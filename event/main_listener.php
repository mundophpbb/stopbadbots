<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2025 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

namespace mundophpbb\stopbadbots\event;

use phpbb\config\config;
use phpbb\request\request;
use phpbb\db\driver\driver_interface as db_driver_interface;
use phpbb\language\language;
use phpbb\cache\driver\driver_interface as cache_driver_interface;
use phpbb\log\log;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

define('DEBUG_STOPBADBOTS', false); // Mude para true apenas em depuração

class main_listener implements EventSubscriberInterface
{
    protected $config;
    protected $request;
    protected $db;
    protected $language;
    protected $cache;
    protected $table_prefix;
    protected $logger;
    protected $root_path;

    /**
     * Construtor
     *
     * @param config $config Configurações do phpBB
     * @param request $request Objeto de requisição
     * @param db_driver_interface $db Driver do banco de dados
     * @param language $language Sistema de idiomas
     * @param cache_driver_interface $cache Driver de cache
     * @param string $table_prefix Prefixo das tabelas
     * @param log $logger Sistema de logs
     * @param string|null $root_path Caminho raiz do phpBB
     */
    public function __construct(
        config $config,
        request $request,
        db_driver_interface $db,
        language $language,
        cache_driver_interface $cache,
        $table_prefix,
        log $logger,
        $root_path = null
    ) {
        // Log detalhado para depuração
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Verificando dependências - config: ' . (is_object($config) ? get_class($config) : 'null'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: request: ' . (is_object($request) ? get_class($request) : 'null'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: db: ' . (is_object($db) ? get_class($db) : 'null'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: language: ' . (is_object($language) ? get_class($language) : 'null'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: cache: ' . (is_object($cache) ? get_class($cache) : 'null'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: table_prefix: ' . ($table_prefix ?: 'vazio'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: logger: ' . (is_object($logger) ? get_class($logger) : 'null'));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: root_path: ' . ($root_path ?: 'vazio'));
        }

        // Verificar dependências
        $missing = [];
        if (!$config) $missing[] = 'config';
        if (!$request) $missing[] = 'request';
        if (!$db) $missing[] = 'db';
        if (!$language) $missing[] = 'language';
        if (!$cache) $missing[] = 'cache';
        if (!$table_prefix) $missing[] = 'table_prefix';
        if (!$logger) $missing[] = 'logger';
        if (!empty($missing)) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Dependências ausentes: ' . implode(', ', $missing));
            }
            throw new \Exception('Dependências ausentes no main_listener: ' . implode(', ', $missing));
        }

        $this->config = $config;
        $this->request = $request;
        $this->db = $db;
        $this->language = $language;
        $this->cache = $cache;
        $this->table_prefix = $table_prefix;
        $this->logger = $logger;
        $this->root_path = $root_path ?: (defined('PHPBB_ROOT_PATH') ? PHPBB_ROOT_PATH : dirname(__FILE__, 4) . '/');
        $this->root_path = str_replace('\\', '/', $this->root_path);

        // Verificar root_path
        if (empty($this->root_path) || !is_dir($this->root_path)) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: root_path inválido: ' . ($this->root_path ?: 'vazio'));
            }
            throw new \Exception('Caminho raiz inválido ou não especificado.');
        }

        // Carregar idioma dinamicamente
        $lang_code = $this->request->variable('lang', $this->config['default_lang'] ?: 'en');
        $language_file = $this->root_path . 'ext/mundophpbb/stopbadbots/language/' . $lang_code . '/stopbadbots.php';

        if (file_exists($language_file) && is_readable($language_file)) {
            $this->language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', $lang_code);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Idioma carregado com sucesso: ' . $lang_code);
            }
        } else {
            $this->language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', 'en');
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Idioma ' . $lang_code . ' não encontrado, usando fallback para en');
            }
        }

        // Limpar cache de idioma
        $this->cache->destroy('_lang_' . $lang_code);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Cache de idioma limpo para: ' . $lang_code);
        }
    }

    /**
     * Registra os eventos que o listener vai escutar
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup' => 'block_bad_bots',
        ];
    }

    /**
     * Bloqueia bots maliciosos com base em User-Agent, IP e Referer
     *
     * @param \phpbb\event\data $event Dados do evento
     */
    public function block_bad_bots($event)
    {
        // Ignorar em páginas admin
        if (strpos($this->request->server('SCRIPT_FILENAME'), 'adm') !== false || !$this->config['stopbadbots_enabled']) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Ignorando block_bad_bots - Em admin: true, stopbadbots_enabled: ' . ($this->config['stopbadbots_enabled'] ?? 'false'));
            }
            return;
        }

        $user_agent = $this->get_user_agent();
        $ip = $this->get_client_ip();
        $referer = $this->get_referer();

        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Verificando bot - User-Agent: ' . ($user_agent ?: 'Desconhecido') . ', IP: ' . ($ip ?: 'Desconhecido') . ', Referer: ' . ($referer ?: 'Nenhum'));
        }

        try {
            $block_reason = $this->is_bot_blocked($user_agent, $ip, $referer);
            if ($block_reason) {
                $this->log_blocked_bot($ip, $user_agent, $referer, $block_reason);
                header('HTTP/1.1 403 Forbidden');
                exit($this->language->lang('ACP_STOPBADBOTS_BLOCKED', htmlspecialchars($block_reason)));
            }
        } catch (\Exception $e) {
            $this->logger->add('admin', 0, $ip, 'LOG_ERROR', time(), ['Erro em block_bad_bots: ' . $e->getMessage()]);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Erro em block_bad_bots: ' . $e->getMessage());
            }
        }
    }

    /**
     * Verifica se o acesso deve ser bloqueado com base em User-Agent, IP ou Referer
     *
     * @param string $user_agent User-Agent do cliente
     * @param string $ip Endereço IP do cliente
     * @param string $referer Referer do cliente
     * @return string|bool Motivo do bloqueio ou false se não bloqueado
     */
    protected function is_bot_blocked($user_agent, $ip, $referer)
    {
        $lists = $this->load_lists();
        $ua_list = $lists['ua_blacklist'] ?? [];
        $ip_list = $lists['ip_blacklist'] ?? [];
        $ref_list = $lists['ref_blacklist'] ?? [];
        $ua_whitelist = $lists['ua_whitelist'] ?? [];
        $ip_whitelist = $lists['ip_whitelist'] ?? [];

        // Verificar User-Agent
        if (!empty($user_agent) && !in_array($user_agent, array_column($ua_whitelist, 'value'))) {
            foreach ($ua_list as $ua) {
                $ua = trim($ua['value']);
                if (!empty($ua) && stripos($user_agent, $ua) !== false) {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('main_listener: Bloqueado por User-Agent: ' . $user_agent);
                    }
                    return 'Bloqueado por User-Agent: ' . $user_agent;
                }
            }
        }

        // Verificar IP
        if (!empty($ip) && !in_array($ip, array_column($ip_whitelist, 'value'))) {
            foreach ($ip_list as $blocked_ip) {
                $blocked_ip = trim($blocked_ip['value']);
                if ($this->ip_matches($ip, $blocked_ip)) {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('main_listener: Bloqueado por IP: ' . $ip);
                    }
                    return 'Bloqueado por IP: ' . $ip;
                }
            }
        }

        // Verificar Referer
        if (!empty($referer)) {
            $parsed_referer = parse_url($referer);
            $referer_host = strtolower($parsed_referer['host'] ?? '');
            foreach ($ref_list as $blocked_referer) {
                $blocked_referer = trim(strtolower($blocked_referer['value']));
                if (!empty($blocked_referer) && stripos($referer_host, $blocked_referer) !== false) {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('main_listener: Bloqueado por Referer: ' . $referer);
                    }
                    return 'Bloqueado por Referer: ' . $referer;
                }
            }
        }

        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Nenhum bloqueio aplicado');
        }
        return false;
    }

    /**
     * Verifica se o IP do cliente corresponde a um padrão (IP único ou CIDR)
     *
     * @param string $ip IP do cliente
     * @param string $pattern IP ou intervalo CIDR
     * @return bool
     */
    protected function ip_matches($ip, $pattern)
    {
        if (filter_var($pattern, FILTER_VALIDATE_IP)) {
            return $ip === $pattern;
        }
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/', $pattern)) {
            list($base_ip, $mask) = explode('/', $pattern);
            $ip_long = ip2long($ip);
            $base_ip_long = ip2long($base_ip);
            if ($ip_long === false || $base_ip_long === false) {
                return false;
            }
            $mask = ~((1 << (32 - $mask)) - 1);
            return ($ip_long & $mask) === ($base_ip_long & $mask);
        }
        return false;
    }

    /**
     * Carrega as listas de bloqueio e whitelist do banco de dados
     *
     * @return array Listas organizadas por tipo
     */
    protected function load_lists()
    {
        $lists = [
            'ua_blacklist' => [],
            'ip_blacklist' => [],
            'ref_blacklist' => [],
            'ua_whitelist' => [],
            'ip_whitelist' => [],
        ];

        $sql = 'SELECT list_type, value FROM ' . $this->table_prefix . 'stopbadbots_lists';
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Carregando listas: ' . $sql);
        }
        try {
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                $lists[$row['list_type']][] = ['value' => $row['value']];
            }
            $this->db->sql_freeresult($result);
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Erro ao carregar listas: ' . $e->getMessage());
            }
            $this->logger->add('admin', 0, '', 'LOG_ERROR', time(), ['Erro ao carregar listas: ' . $e->getMessage()]);
        }

        return $lists;
    }

    /**
     * Obtém o User-Agent do cliente
     *
     * @return string
     */
    protected function get_user_agent()
    {
        $user_agent = $this->request->server('HTTP_USER_AGENT', 'Desconhecido');
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: User-Agent obtido: ' . ($user_agent ?: 'Desconhecido'));
        }
        return $user_agent;
    }

    /**
     * Obtém o IP do cliente, priorizando REMOTE_ADDR e usando X-Forwarded-For apenas se configurado
     *
     * @return string
     */
    protected function get_client_ip()
    {
        // Priorizar REMOTE_ADDR por padrão
        $ip = $this->request->server('REMOTE_ADDR', '');

        // Verificar se a configuração para usar X-Forwarded-For está habilitada
        if (!empty($this->config['stopbadbots_use_x_forwarded_for'])) {
            $forwarded_for = $this->request->server('HTTP_X_FORWARDED_FOR', '');
            if ($forwarded_for) {
                // Pegar o primeiro IP da lista (o mais próximo do cliente)
                $forwarded_ips = array_map('trim', explode(',', $forwarded_for));
                $ip = $forwarded_ips[0];
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('main_listener: Usando X-Forwarded-For: ' . $ip);
                }
            } else {
                $client_ip = $this->request->server('HTTP_CLIENT_IP', '');
                if ($client_ip) {
                    $ip = $client_ip;
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('main_listener: Usando HTTP_CLIENT_IP: ' . $ip);
                    }
                }
            }
        } else {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: X-Forwarded-For desativado, usando REMOTE_ADDR: ' . $ip);
            }
        }

        // Validar o IP
        $ip = filter_var($ip, FILTER_VALIDATE_IP) ? $ip : 'Desconhecido';
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: IP final obtido: ' . ($ip ?: 'Desconhecido'));
        }
        return $ip;
    }

    /**
     * Obtém o Referer do cliente
     *
     * @return string
     */
    protected function get_referer()
    {
        $referer = $this->request->server('HTTP_REFERER', 'Nenhum');
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Referer obtido: ' . ($referer ?: 'Nenhum'));
        }
        return $referer;
    }

    /**
     * Registra um bot bloqueado no banco de dados
     *
     * @param string $ip IP do cliente
     * @param string $user_agent User-Agent do cliente
     * @param string $referer Referer do cliente
     * @param string $reason Motivo do bloqueio
     */
    protected function log_blocked_bot($ip, $user_agent, $referer, $reason)
    {
        $sql_array = [
            'log_time' => time(),
            'user_agent' => substr($user_agent ?: 'Desconhecido', 0, 255),
            'ip' => substr($ip ?: 'Desconhecido', 0, 45),
            'referer' => $referer ?: 'Nenhum',
            'reason' => $reason,
        ];

        $sql = 'INSERT INTO ' . $this->table_prefix . 'stopbadbots_log ' . $this->db->sql_build_array('INSERT', $sql_array);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Registrando bot bloqueado: ' . $sql);
        }
        try {
            $this->db->sql_query($sql);
            $this->logger->add('admin', 0, $ip, 'LOG_BOT_BLOCKED', time(), [$reason]);
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_listener: Erro ao registrar bot bloqueado: ' . $e->getMessage());
            }
            $this->logger->add('admin', 0, '', 'LOG_ERROR', time(), ['Erro ao registrar bot bloqueado: ' . $e->getMessage()]);
        }
    }
}