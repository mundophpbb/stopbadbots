<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2024 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

namespace mundophpbb\stopbadbots\acp;

class restore
{
    protected $db;
    protected $config;
    protected $cache;
    protected $log;
    protected $language;
    protected $request;
    protected $user;
    protected $table_prefix;
    protected $u_action;
    protected $phpbb_root_path;

    public function __construct(
        \phpbb\db\driver\driver_interface $db,
        \phpbb\config\config $config,
        \phpbb\cache\driver\driver_interface $cache,
        \phpbb\log\log $log,
        \phpbb\language\language $language,
        \phpbb\request\request $request,
        \phpbb\user $user,
        $table_prefix
    ) {
        $this->db = $db;
        $this->config = $config;
        $this->cache = $cache;
        $this->log = $log;
        $this->language = $language;
        $this->request = $request;
        $this->user = $user;
        $this->table_prefix = $table_prefix;
        $this->phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './../../';
        error_log('restore: Inicializado com prefixo da tabela ' . $table_prefix);
    }

    public function set_u_action($u_action)
    {
        $this->u_action = $u_action;
        error_log('restore: u_action definido como: ' . $u_action);
    }

    public function handle()
    {
        global $config;

        // Determinar o idioma do usuário ou do fórum, com fallback para 'en'
        $lang_code = !empty($this->user->lang_name) ? $this->user->lang_name : (!empty($config['default_lang']) ? $config['default_lang'] : 'en');
        $this->language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', $lang_code);

        // Fallback para 'en' se o idioma não estiver disponível
        if (!file_exists($this->phpbb_root_path . 'ext/mundophpbb/stopbadbots/language/' . $lang_code . '/stopbadbots.php')) {
            $this->language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', 'en');
            error_log('restore: Idioma ' . $lang_code . ' não encontrado, usando fallback en');
        } else {
            error_log('restore: Idioma carregado com sucesso para ' . $lang_code);
        }

        add_form_key('sbb_restore');

        if ($this->request->is_set_post('submit')) {
            if (!check_form_key('sbb_restore')) {
                $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para restauração']);
                trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
            }

            try {
                // Verificar se a tabela existe
                $sql = "SHOW TABLES LIKE '" . $this->db->sql_escape($this->table_prefix . 'stopbadbots_lists') . "'";
                $result = $this->db->sql_query($sql);
                $table_exists = $this->db->sql_fetchrow($result) !== false;
                $this->db->sql_freeresult($result);
                error_log('restore: Tabela ' . $this->table_prefix . 'stopbadbots_lists existe: ' . ($table_exists ? 'true' : 'false'));

                if (!$table_exists) {
                    $sql = "CREATE TABLE " . $this->table_prefix . "stopbadbots_lists (
                        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        list_type VARCHAR(50) NOT NULL,
                        value VARCHAR(255) NOT NULL,
                        added_at INT UNSIGNED NOT NULL,
                        PRIMARY KEY (id),
                        INDEX idx_list_type (list_type)
                    )";
                    $this->db->sql_query($sql);
                    error_log('restore: Tabela criada: ' . $this->table_prefix . 'stopbadbots_lists');
                }

                // Limpar a tabela de listas
                $sql = 'TRUNCATE TABLE ' . $this->table_prefix . 'stopbadbots_lists';
                error_log('restore: Executando SQL para limpar listas: ' . $sql);
                $this->db->sql_query($sql);
                $affected_rows = $this->db->sql_affectedrows();
                error_log('restore: Linhas afetadas pela limpeza: ' . $affected_rows);

                // Resetar configurações para valores padrão
                $this->config->set('stopbadbots_enabled', 1);
                $this->config->set('stopbadbots_cron_enabled', 1);
                $this->config->set('stopbadbots_cron_interval', 86400);
                $this->config->set('stopbadbots_log_retention_days', 30);
                $this->config->set('stopbadbots_cron_last_run', 0);
                error_log('restore: Configurações padrão restauradas');

                // Inserir listas iniciais
                $initial_lists = [
                    ['list_type' => 'ua_blacklist', 'value' => '_zbot', 'added_at' => time()],
                    ['list_type' => 'ua_blacklist', 'value' => 'BadBot', 'added_at' => time()],
                    ['list_type' => 'ip_blacklist', 'value' => '1.180.70.178', 'added_at' => time()],
                    ['list_type' => 'ip_blacklist', 'value' => '192.168.1.0/24', 'added_at' => time()],
                    ['list_type' => 'ref_blacklist', 'value' => '000Free.us', 'added_at' => time()],
                    ['list_type' => 'ua_whitelist', 'value' => 'Googlebot', 'added_at' => time()],
                    ['list_type' => 'ip_whitelist', 'value' => '192.168.1.1', 'added_at' => time()],
                    ['list_type' => 'ref_whitelist', 'value' => 'example.com', 'added_at' => time()],
                ];

                $inserted_count = 0;
                foreach ($initial_lists as $list) {
                    $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists 
                            WHERE list_type = "' . $this->db->sql_escape($list['list_type']) . '" 
                            AND value = "' . $this->db->sql_escape($list['value']) . '"';
                    $result = $this->db->sql_query($sql);
                    $count = (int) $this->db->sql_fetchfield('count');
                    $this->db->sql_freeresult($result);

                    if ($count == 0) {
                        $sql = 'INSERT INTO ' . $this->table_prefix . 'stopbadbots_lists ' . $this->db->sql_build_array('INSERT', $list);
                        error_log('restore: Adicionando entrada padrão: ' . $sql);
                        $this->db->sql_query($sql);
                        $inserted_count++;
                    } else {
                        error_log('restore: Duplicata ignorada: ' . $list['list_type'] . ' - ' . $list['value']);
                    }
                }
                error_log('restore: Total de entradas padrão inseridas: ' . $inserted_count);

                // Limpar cache
                $this->cache->purge();
                error_log('restore: Cache limpo após restauração padrão');

                // Registrar no log
                $this->log->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_RESET_DEFAULT', time());

                // Exibir mensagem de sucesso
                trigger_error($this->language->lang('ACP_STOPBADBOTS_RESET_DEFAULT_SUCCESS') . adm_back_link($this->u_action), E_USER_WARNING);
            } catch (\Exception $e) {
                error_log('restore: Erro ao restaurar configurações padrão: ' . $e->getMessage());
                trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage())) . adm_back_link($this->u_action), E_USER_WARNING);
            }
        }
    }
}