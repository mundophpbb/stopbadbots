<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2024 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

namespace mundophpbb\stopbadbots\cron;

use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\cache\driver\driver_interface as cache_driver;
use phpbb\log\log;

class cron_task extends \phpbb\cron\task\base
{
    protected $db;
    protected $config;
    protected $cache;
    protected $table_prefix;
    protected $logger;
    protected $name = 'mundophpbb_stopbadbots_cron';

    public function __construct(
        driver_interface $db,
        config $config,
        cache_driver $cache,
        $table_prefix,
        log $logger
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->cache = $cache;
        $this->table_prefix = $table_prefix;
        $this->logger = $logger;
        error_log('cron_task: Inicializado com prefixo da tabela ' . $table_prefix . ' em ' . date('Y-m-d H:i:s'));
    }

    public function get_name()
    {
        return $this->name;
    }

    public function run($clear_all_logs = false)
    {
        error_log('cron_task: Iniciando método run em ' . date('Y-m-d H:i:s') . ', clear_all_logs: ' . ($clear_all_logs ? 'true' : 'false'));

        if (!$this->config['stopbadbots_cron_enabled']) {
            error_log('cron_task: Não executado, cron está desativado');
            $this->logger->add('admin', ANONYMOUS, '', 'STOPBADBOTS_CRON_DISABLED', time());
            return;
        }

        // Verificar se a tabela existe
        $sql = "SHOW TABLES LIKE '" . $this->db->sql_escape($this->table_prefix . 'stopbadbots_log') . "'";
        $result = $this->db->sql_query($sql);
        $table_exists = $this->db->sql_fetchrow($result) !== false;
        $this->db->sql_freeresult($result);
        if (!$table_exists) {
            error_log('cron_task: Tabela ' . $this->table_prefix . 'stopbadbots_log não existe');
            $this->logger->add('admin', ANONYMOUS, '', 'STOPBADBOTS_LOG_ERROR', time(), ['Tabela não existe']);
            return;
        }

        // Limpar logs
        if ($clear_all_logs) {
            $sql = 'TRUNCATE TABLE ' . $this->table_prefix . 'stopbadbots_log';
            error_log('cron_task: Limpando todos os logs: ' . $sql);
        } else {
            $retention_days = max(1, (int) ($this->config['stopbadbots_log_retention_days'] ?? 30));
            $retention_time = time() - ($retention_days * 24 * 3600);
            $sql = 'DELETE FROM ' . $this->table_prefix . 'stopbadbots_log WHERE log_time < ' . (int) $retention_time;
            error_log('cron_task: Deletando logs antigos: ' . $sql);
        }
        try {
            $this->db->sql_query($sql);
            $affected_rows = $this->db->sql_affectedrows();
            error_log('cron_task: Limpou ' . $affected_rows . ' logs');
            $this->logger->add('admin', ANONYMOUS, '', 'STOPBADBOTS_LOG_CLEANED', time(), [$affected_rows]);
        } catch (\Exception $e) {
            error_log('cron_task: Erro ao limpar logs: ' . $e->getMessage());
            $this->logger->add('admin', ANONYMOUS, '', 'STOPBADBOTS_LOG_ERROR', time(), ['Erro ao limpar logs: ' . $e->getMessage()]);
        }

        // Atualizar estatísticas diárias
        $today_start = strtotime('today midnight');
        $sql = 'SELECT reason, COUNT(*) AS count 
                FROM ' . $this->table_prefix . 'stopbadbots_log 
                WHERE log_time >= ' . (int) $today_start . ' 
                GROUP BY reason';
        error_log('cron_task: Calculando estatísticas: ' . $sql);
        try {
            $result = $this->db->sql_query($sql);
            $daily_blocks = [
                'total' => 0,
                'ua_blocks' => 0,
                'ip_blocks' => 0,
                'ref_blocks' => 0,
            ];
            while ($row = $this->db->sql_fetchrow($result)) {
                $daily_blocks['total'] += (int) $row['count'];
                if (stripos($row['reason'], 'User-Agent') !== false) {
                    $daily_blocks['ua_blocks'] += (int) $row['count'];
                } elseif (stripos($row['reason'], 'IP') !== false) {
                    $daily_blocks['ip_blocks'] += (int) $row['count'];
                } elseif (stripos($row['reason'], 'Referer') !== false) {
                    $daily_blocks['ref_blocks'] += (int) $row['count'];
                }
            }
            $this->db->sql_freeresult($result);

            $this->config->set('stopbadbots_daily_blocks', $daily_blocks['total']);
            $this->config->set('stopbadbots_daily_ua_blocks', $daily_blocks['ua_blocks']);
            $this->config->set('stopbadbots_daily_ip_blocks', $daily_blocks['ip_blocks']);
            $this->config->set('stopbadbots_daily_ref_blocks', $daily_blocks['ref_blocks']);
            error_log('cron_task: Estatísticas atualizadas - Total: ' . $daily_blocks['total'] . ', UA: ' . $daily_blocks['ua_blocks'] . ', IP: ' . $daily_blocks['ip_blocks'] . ', Referer: ' . $daily_blocks['ref_blocks']);
        } catch (\Exception $e) {
            error_log('cron_task: Erro ao atualizar estatísticas: ' . $e->getMessage());
            $this->logger->add('admin', ANONYMOUS, '', 'STOPBADBOTS_LOG_ERROR', time(), ['Erro ao atualizar estatísticas: ' . $e->getMessage()]);
        }

        // Atualizar a última execução do cron
        $this->config->set('stopbadbots_cron_last_run', time());
        error_log('cron_task: Atualizado cron_last_run para ' . date('Y-m-d H:i:s'));

        // Limpar cache
        $this->cache->purge();
        error_log('cron_task: Cache limpo após execução do cron');
    }

    public function is_runnable()
    {
        $enabled = (bool) ($this->config['stopbadbots_cron_enabled'] ?? 0);
        error_log('cron_task: is_runnable verificado - enabled: ' . ($enabled ? 'true' : 'false'));
        return $enabled;
    }

    public function should_run()
    {
        $interval = max(3600, (int) ($this->config['stopbadbots_cron_interval'] ?? 86400));
        $last_run = (int) ($this->config['stopbadbots_cron_last_run'] ?? 0);
        $should_run = $this->config['stopbadbots_cron_enabled'] && (time() - $last_run >= $interval);
        error_log('cron_task: should_run verificado - enabled: ' . ($this->config['stopbadbots_cron_enabled'] ? 'true' : 'false') . ', intervalo: ' . $interval . ', última execução: ' . ($last_run ? date('Y-m-d H:i:s', $last_run) : 'nunca') . ', deve executar: ' . ($should_run ? 'true' : 'false'));
        return $should_run;
    }
}