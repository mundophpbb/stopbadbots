<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2025 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */
namespace mundophpbb\stopbadbots\controller;
use phpbb\config\config;
use phpbb\db\driver\driver_interface;
use phpbb\template\template;
use phpbb\language\language;
use phpbb\request\request;
use phpbb\log\log;
use phpbb\user;
use phpbb\pagination;
use phpbb\files\upload;
use phpbb\cache\driver\driver_interface as cache_driver;
class acp_controller
{
    const COMPOSER_FILE = 'ext/mundophpbb/stopbadbots/composer.json';
    protected $db;
    protected $config;
    protected $template;
    protected $language;
    protected $request;
    protected $logger;
    protected $table_prefix;
    protected $php_ext;
    protected $u_action;
    protected $user;
    protected $pagination;
    protected $upload;
    protected $cron;
    protected $cache;
    protected $root_path;
    public function __construct(
        driver_interface $db,
        config $config,
        template $template,
        language $language,
        request $request,
        user $user,
        $table_prefix,
        $php_ext,
        log $logger,
        pagination $pagination,
        upload $upload,
        \mundophpbb\stopbadbots\cron\cron_task $cron,
        cache_driver $cache,
        $root_path = null
    ) {
        // Definir DEBUG_STOPBADBOTS com base na configuração
        if (!defined('DEBUG_STOPBADBOTS')) {
            define('DEBUG_STOPBADBOTS', (bool) ($config['stopbadbots_debug_enabled'] ?? false));
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Verificando dependências - db: ' . (is_object($db) ? get_class($db) : 'null'));
            error_log('acp_controller: config: ' . (is_object($config) ? get_class($config) : 'null'));
            error_log('acp_controller: template: ' . (is_object($template) ? get_class($template) : 'null'));
            error_log('acp_controller: language: ' . (is_object($language) ? get_class($language) : 'null'));
            error_log('acp_controller: request: ' . (is_object($request) ? get_class($request) : 'null'));
            error_log('acp_controller: user: ' . (is_object($user) ? get_class($user) : 'null'));
            error_log('acp_controller: table_prefix: ' . ($table_prefix ?: 'vazio'));
            error_log('acp_controller: php_ext: ' . ($php_ext ?: 'vazio'));
            error_log('acp_controller: logger: ' . (is_object($logger) ? get_class($logger) : 'null'));
            error_log('acp_controller: pagination: ' . (is_object($pagination) ? get_class($pagination) : 'null'));
            error_log('acp_controller: upload: ' . (is_object($upload) ? get_class($upload) : 'null'));
            error_log('acp_controller: cron: ' . (is_object($cron) ? get_class($cron) : 'null'));
            error_log('acp_controller: cache: ' . (is_object($cache) ? get_class($cache) : 'null'));
            error_log('acp_controller: root_path: ' . ($root_path ?: 'vazio'));
        }
        $missing = [];
        if (!$db) $missing[] = 'Driver do banco de dados não injetado';
        if (!$config) $missing[] = 'Configurações do phpBB não injetadas';
        if (!$template) $missing[] = 'Sistema de templates não injetado';
        if (!$language) $missing[] = 'Sistema de idiomas não injetado';
        if (!$request) $missing[] = 'Objeto de requisição não injetado';
        if (!$user) $missing[] = 'Objeto do usuário não injetado';
        if (!$logger) $missing[] = 'Sistema de logs não injetado';
        if (!$pagination) $missing[] = 'Sistema de paginação não injetado';
        if (!$upload) $missing[] = 'Sistema de upload não injetado';
        if (!$cron) $missing[] = 'Tarefa cron não injetada';
        if (!$cache) $missing[] = 'Driver de cache não injetado';
        if (!$table_prefix) $missing[] = 'Prefixo das tabelas não especificado';
        if (!empty($missing)) {
            $error_message = 'Dependências do controlador ausentes: ' . implode(', ', $missing);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: ' . $error_message);
            }
            throw new \Exception($error_message);
        }
        $root_path = $root_path ?: (defined('PHPBB_ROOT_PATH') ? PHPBB_ROOT_PATH : realpath(__DIR__ . '/../../../') . '/');
        $root_path = str_replace('\\', '/', $root_path);
        if (empty($root_path) || !is_dir($root_path)) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: root_path inválido: ' . ($root_path ?: 'vazio'));
            }
            throw new \Exception('Caminho raiz inválido ou não especificado.');
        }
        $this->db = $db;
        $this->config = $config;
        $this->template = $template;
        $this->language = $language;
        $this->request = $request;
        $this->user = $user;
        $this->table_prefix = $table_prefix;
        $this->php_ext = $php_ext;
        $this->logger = $logger;
        $this->pagination = $pagination;
        $this->upload = $upload;
        $this->cron = $cron;
        $this->cache = $cache;
        $this->root_path = rtrim($root_path, '/') . '/';
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Inicializado com prefixo da tabela ' . $table_prefix . ', Session ID: ' . ($user->session_id ?: 'vazio') . ', root_path: ' . $this->root_path);
        }
    }
    public function set_u_action($u_action)
    {
        $this->u_action = $u_action;
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: u_action definido como: ' . $u_action);
        }
    }
    public function handle($mode)
    {
        $lang_code = preg_replace('/[^a-zA-Z0-9_-]/', '', $this->request->variable('lang', $this->config['default_lang'] ?: 'en'));
        $language_file = $this->root_path . 'ext/mundophpbb/stopbadbots/language/' . $lang_code . '/stopbadbots.php';
        if (file_exists($language_file) && is_readable($language_file)) {
            $this->language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', $lang_code);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Idioma carregado com sucesso: ' . $lang_code);
            }
        } else {
            $this->language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', 'en');
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Idioma ' . $lang_code . ' não encontrado, usando fallback para en');
            }
        }
        $this->cache->destroy('_lang_' . $lang_code);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Cache de idioma limpo para: ' . $lang_code);
        }
        $extension_version = 'N/A';
        $jsonFilePath = $this->root_path . self::COMPOSER_FILE;
        if (file_exists($jsonFilePath) && is_readable($jsonFilePath)) {
            $jsonContent = file_get_contents($jsonFilePath);
            $data = json_decode($jsonContent);
            if (json_last_error() !== JSON_ERROR_NONE) {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Erro ao parsear composer.json: ' . json_last_error_msg());
                }
            } elseif ($data && isset($data->version)) {
                $extension_version = htmlspecialchars($data->version, ENT_QUOTES, 'UTF-8');
            }
        } else {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Arquivo composer.json não encontrado ou não legível em: ' . $jsonFilePath);
            }
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Versão da extensão carregada: ' . $extension_version);
        }
        $this->template->assign_vars([
            'U_ACTION' => $this->u_action,
            'U_CLEAR_LOG' => $this->u_action . '&action=clear_log',
            'U_EXPORT_CSV' => $this->u_action . '&action=export_csv',
            'STOPBADBOTS_VERSION' => $extension_version,
        ]);
        switch ($mode) {
            case 'settings':
                $action = $this->request->variable('action', '');
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Modo configurações, ação: ' . $action);
                }
                if ($action === 'run_cron') {
                    $this->handle_run_cron();
                } elseif ($action === 'reset_default') {
                    $this->handle_reset_default();
                } elseif ($action === 'load_more_list') {
                    $this->handle_load_more_list();
                } else {
                    $this->handle_settings();
                }
                break;
            case 'lists':
                $this->handle_lists();
                break;
            case 'logs':
                $this->handle_logs();
                break;
            case 'restore':
                try {
                    $restore_controller = new \mundophpbb\stopbadbots\acp\restore(
                        $this->db,
                        $this->config,
                        $this->cache,
                        $this->logger,
                        $this->language,
                        $this->request,
                        $this->user,
                        $this->table_prefix
                    );
                    $restore_controller->set_u_action($this->u_action);
                    $restore_controller->handle();
                } catch (\Exception $e) {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Erro ao carregar controlador de restauração: ' . $e->getMessage());
                    }
                    trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
                }
                break;
            case 'overview':
                $action = $this->request->variable('action', '');
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Modo overview, ação: ' . $action);
                }
                if ($action === 'save_lists') {
                    $this->handle_save_lists();
                } else {
                    $this->handle_overview();
                }
                break;
            default:
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Modo inválido: ' . $mode);
                }
                trigger_error($this->language->lang('INVALID_MODE'), E_USER_ERROR);
        }
    }
    protected function handle_run_cron()
    {
        $form_key = 'sbb_config';
        add_form_key($form_key);
        if ($this->request->is_set_post('submit')) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Verificando form_key para run_cron: ' . $this->request->variable('form_token', ''));
            }
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para run_cron']);
                trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
            }
            try {
                $this->cron->run(true);
                $this->config->set('stopbadbots_cron_last_run', time());
                $this->cache->purge();
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_CRON_RUN', time());
                redirect($this->u_action . '&mode=logs');
            } catch (\Exception $e) {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Erro ao executar cron: ' . $e->getMessage());
                }
                trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_WARNING);
            }
        }
        $this->handle_settings();
    }
    protected function handle_reset_default()
    {
        $form_key = 'sbb_config';
        add_form_key($form_key);
        if ($this->request->is_set_post('reset_default')) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Ação de restauração padrão acionada, POST: ' . json_encode($_POST));
            }
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para reset_default']);
                trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
            }
            try {
                $sql = "SHOW TABLES LIKE '" . $this->db->sql_escape($this->table_prefix . 'stopbadbots_lists') . "'";
                $result = $this->db->sql_query($sql);
                $table_exists = $this->db->sql_fetchrow($result) !== false;
                $this->db->sql_freeresult($result);
                if (!$table_exists) {
                    $sql = "CREATE TABLE " . $this->table_prefix . "stopbadbots_lists (
                        id INT UNSIGNED NOT NULL AUTO_INCREMENT,
                        list_type VARCHAR(50) NOT NULL,
                        value VARCHAR(255) NOT NULL,
                        added_at INT UNSIGNED NOT NULL,
                        PRIMARY KEY (id),
                        INDEX idx_list_type (list_type),
                        INDEX idx_value (value(255))
                    ) CHARACTER SET utf8 COLLATE utf8_bin";
                    $this->db->sql_query($sql);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Tabela criada: ' . $this->table_prefix . 'stopbadbots_lists');
                    }
                }
                $sql = 'TRUNCATE TABLE ' . $this->table_prefix . 'stopbadbots_lists';
                $this->db->sql_query($sql);
                $this->config->set('stopbadbots_enabled', 1);
                $this->config->set('stopbadbots_cron_enabled', 1);
                $this->config->set('stopbadbots_cron_interval', 86400);
                $this->config->set('stopbadbots_log_retention_days', 30);
                $this->config->set('stopbadbots_cron_last_run', 0);
                $this->config->set('stopbadbots_daily_blocks', 0);
                $this->config->set('stopbadbots_daily_ua_blocks', 0);
                $this->config->set('stopbadbots_daily_ip_blocks', 0);
                $this->config->set('stopbadbots_daily_ref_blocks', 0);
                $this->config->set('stopbadbots_use_x_forwarded_for', 0);
                $this->config->set('stopbadbots_debug_enabled', 0);
                $initial_lists = [
                    ['list_type' => 'ua_blacklist', 'value' => '_zbot', 'added_at' => time()],
                    ['list_type' => 'ua_blacklist', 'value' => 'BadBot', 'added_at' => time()],
                    ['list_type' => 'ip_blacklist', 'value' => '1.180.70.178', 'added_at' => time()],
                    ['list_type' => 'ip_blacklist', 'value' => '192.168.1.0/24', 'added_at' => time()],
                    ['list_type' => 'ref_blacklist', 'value' => '000Free.us', 'added_at' => time()],
                    ['list_type' => 'ua_whitelist', 'value' => 'Googlebot', 'added_at' => time()],
                    ['list_type' => 'ua_whitelist', 'value' => 'bingbot', 'added_at' => time()],
                    ['list_type' => 'ua_whitelist', 'value' => 'YandexBot', 'added_at' => time()],
                    ['list_type' => 'ua_whitelist', 'value' => 'DuckDuckBot', 'added_at' => time()],
                    ['list_type' => 'ip_whitelist', 'value' => '192.168.1.1', 'added_at' => time()],
                    ['list_type' => 'ref_whitelist', 'value' => 'example.com', 'added_at' => time()],
                ];
                $inserted_count = 0;
                foreach ($initial_lists as $list) {
                    if (in_array($list['list_type'], ['ip_blacklist', 'ip_whitelist']) && !$this->validate_ip_or_cidr($list['value'])) {
                        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                            error_log('acp_controller: IP inválido ignorado: ' . $list['value'] . ' para ' . $list['list_type']);
                        }
                        continue;
                    }
                    if ($this->add_list_entry($list['list_type'], $list['value'])) {
                        $inserted_count++;
                    }
                }
                $this->cache->purge();
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_RESET_DEFAULT', time());
                trigger_error($this->language->lang('ACP_STOPBADBOTS_RESET_DEFAULT_SUCCESS') . adm_back_link($this->u_action), E_USER_NOTICE);
            } catch (\Exception $e) {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Erro ao restaurar configurações padrão: ' . $e->getMessage());
                }
                trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_WARNING);
            }
        }
        $this->handle_settings();
    }
    protected function handle_load_more_list()
{
    $list_type = $this->request->variable('list_type', '');
    $offset = $this->request->variable('offset', 0, false, \phpbb\request\request::GET);
    $per_page = $this->request->variable('per_page', 50, false, \phpbb\request\request::GET);
    if (!in_array($list_type, ['ua_blacklist', 'ip_blacklist', 'ref_blacklist', 'ua_whitelist', 'ip_whitelist', 'ref_whitelist'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $this->language->lang('INVALID_LIST_TYPE')]);
        exit;
    }
    try {
        $sql = 'SELECT id, value, added_at, is_active FROM ' . $this->table_prefix . 'stopbadbots_lists
                WHERE list_type = "' . $this->db->sql_escape($list_type) . '"
                ORDER BY value ASC';
        $result = $this->db->sql_query_limit($sql, (int) $per_page, (int) $offset);
        $entries = [];
        while ($row = $this->db->sql_fetchrow($result)) {
            $entries[] = [
                'id' => $row['id'],
                'value' => htmlspecialchars($row['value']),
                'added_at' => $this->user->format_date($row['added_at']),
                'is_active' => (bool) $row['is_active'],
                'u_toggle' => $this->u_action . '&action=toggle&id=' . $row['id'] . '&is_active=' . ($row['is_active'] ? 0 : 1),
            ];
        }
        $this->db->sql_freeresult($result);
        $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists WHERE list_type = "' . $this->db->sql_escape($list_type) . '"';
        $result = $this->db->sql_query($sql);
        $total = (int) $this->db->sql_fetchfield('count');
        $this->db->sql_freeresult($result);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'entries' => $entries,
            'has_more' => ($offset + $per_page) < $total
        ]);
    } catch (\Exception $e) {
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Erro ao carregar mais entradas da lista: ' . $e->getMessage());
        }
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => $this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage()))]);
    }
    exit;
}
    protected function handle_lists()
    {
        $form_key = 'sbb_lists';
        add_form_key($form_key);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Form key gerado para sbb_lists');
        }
        $action = $this->request->variable('action', '');
        $id = $this->request->variable('id', 0);
        $search = $this->request->variable('list_search', '', true);
        $error = [];
        $success = false;
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: handle_lists - Ação: ' . $action . ', Form token: ' . $this->request->variable('form_token', '') . ', POST: ' . json_encode($_POST));
        }
        if ($this->request->is_set_post('clear_search')) {
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para clear_search']);
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Falha na validação do form_key para clear_search');
                }
                $error[] = $this->language->lang('FORM_INVALID');
            } else {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Limpar pesquisa acionado, redirecionando para: ' . $this->u_action);
                }
                redirect($this->u_action);
            }
        }
        if ($this->request->is_set_post('submit') || $this->request->is_set_post('submit_search')) {
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para listas']);
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Falha na validação do form_key para submit/submit_search');
                }
                $error[] = $this->language->lang('FORM_INVALID');
            } else {
                if ($action === 'add_user_agent') {
                    $value = $this->request->variable('new_user_agent', '', true);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Adicionando user agent (blacklist): ' . $value);
                    }
                    if ($value) {
                        if ($this->add_list_entry('ua_blacklist', $value)) {
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_UA_ADDED', time(), [$value]);
                            $success = true;
                        } else {
                            $error[] = $this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), 'ua_blacklist');
                        }
                    } else {
                        $error[] = $this->language->lang('INVALID_VALUE');
                    }
                } elseif ($action === 'add_ip') {
                    $value = $this->request->variable('new_ip_address', '', true);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Adicionando IP (blacklist): ' . $value);
                    }
                    if ($value && $this->validate_ip_or_cidr($value)) {
                        if ($this->add_list_entry('ip_blacklist', $value)) {
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_IP_ADDED', time(), [$value]);
                            $success = true;
                        } else {
                            $error[] = $this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), 'ip_blacklist');
                        }
                    } else {
                        $error[] = $this->language->lang('INVALID_IP');
                    }
                } elseif ($action === 'add_referer') {
                    $value = $this->request->variable('new_referer', '', true);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Adicionando referer (blacklist): ' . $value);
                    }
                    if ($value) {
                        if ($this->add_list_entry('ref_blacklist', $value)) {
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_REF_ADDED', time(), [$value]);
                            $success = true;
                        } else {
                            $error[] = $this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), 'ref_blacklist');
                        }
                    } else {
                        $error[] = $this->language->lang('INVALID_VALUE');
                    }
                } elseif ($action === 'add_ua_whitelist') {
                    $value = $this->request->variable('new_ua_whitelist', '', true);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Adicionando user agent (whitelist): ' . $value);
                    }
                    if ($value) {
                        if ($this->add_list_entry('ua_whitelist', $value)) {
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_UA_WHITELIST_ADDED', time(), [$value]);
                            $success = true;
                        } else {
                            $error[] = $this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), 'ua_whitelist');
                        }
                    } else {
                        $error[] = $this->language->lang('INVALID_VALUE');
                    }
                } elseif ($action === 'add_ip_whitelist') {
                    $value = $this->request->variable('new_ip_whitelist', '', true);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Adicionando IP (whitelist): ' . $value);
                    }
                    if ($value && $this->validate_ip_or_cidr($value)) {
                        if ($this->add_list_entry('ip_whitelist', $value)) {
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_IP_WHITELIST_ADDED', time(), [$value]);
                            $success = true;
                        } else {
                            $error[] = $this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), 'ip_whitelist');
                        }
                    } else {
                        $error[] = $this->language->lang('INVALID_IP');
                    }
                } elseif ($action === 'add_ref_whitelist') {
                    $value = $this->request->variable('new_ref_whitelist', '', true);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Adicionando referer (whitelist): ' . $value);
                    }
                    if ($value) {
                        if ($this->add_list_entry('ref_whitelist', $value)) {
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_REF_WHITELIST_ADDED', time(), [$value]);
                            $success = true;
                        } else {
                            $error[] = $this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY', htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), 'ref_whitelist');
                        }
                    } else {
                        $error[] = $this->language->lang('INVALID_VALUE');
                    }
                } elseif ($action === 'upload_list') {
                    $list_type = $this->request->variable('list_type', '');
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Upload de lista, tipo: ' . $list_type);
                    }
                    if (!in_array($list_type, ['ua_blacklist', 'ip_blacklist', 'ref_blacklist', 'ua_whitelist', 'ip_whitelist', 'ref_whitelist'])) {
                        $error[] = $this->language->lang('INVALID_LIST_TYPE');
                    } else {
                        $this->upload->set_allowed_extensions(['txt', 'csv']);
                        $this->upload->set_max_filesize(1024 * 1024); // Limite de 1MB
                        $filedata = $this->upload->handle_upload('files.types.form', 'list_file');
                        if (!empty($filedata->error)) {
                            $error = array_merge($error, $filedata->error);
                        } else {
                            $file_path = $filedata->get('filename');
                            $upload_name = $filedata->get('uploadname');
                            if (empty($file_path) || !file_exists($file_path) || !is_readable($file_path)) {
                                $filedata->remove();
                                $error[] = $this->language->lang('INVALID_FILE_PATH');
                            } else {
                                $finfo = finfo_open(FILEINFO_MIME_TYPE);
                                if ($finfo === false) {
                                    $filedata->remove();
                                    $error[] = $this->language->lang('FILEINFO_EXTENSION_NOT_ENABLED');
                                } else {
                                    $mime_type = finfo_file($finfo, $file_path);
                                    finfo_close($finfo);
                                    if (!in_array($mime_type, ['text/plain', 'text/csv'])) {
                                        $filedata->remove();
                                        $error[] = $this->language->lang('INVALID_FILE_TYPE');
                                    } else {
                                        $lines = file($file_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                                        if ($lines === false) {
                                            $filedata->remove();
                                            $error[] = $this->language->lang('FILE_READ_ERROR', htmlspecialchars($upload_name, ENT_QUOTES, 'UTF-8'));
                                        } else {
                                            $count_added = 0;
                                            foreach ($lines as $line) {
                                                $line = trim($line);
                                                if (!empty($line)) {
                                                    if ($mime_type === 'text/csv' || in_array($list_type, ['ip_blacklist', 'ip_whitelist'])) {
                                                        $parts = explode(',', $line);
                                                        $line = trim($parts[0]);
                                                    }
                                                    if (!empty($line) && (!in_array($list_type, ['ip_blacklist', 'ip_whitelist']) || $this->validate_ip_or_cidr($line))) {
                                                        if ($this->add_list_entry($list_type, $line)) {
                                                            $count_added++;
                                                        }
                                                    }
                                                }
                                            }
                                            $filedata->remove();
                                            $this->cache->purge();
                                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LIST_UPLOADED', time(), [$count_added, $list_type]);
                                            $list_type_lang = $this->get_list_type_lang($list_type);
                                            trigger_error(sprintf($this->language->lang('ACP_STOPBADBOTS_LIST_UPLOADED_WITH_TYPE'), $count_added, $this->language->lang($list_type_lang)) . adm_back_link($this->u_action), E_USER_NOTICE);
                                        }
                                    }
                                }
                            }
                        }
                    }
                } elseif ($action === 'import_default_lists') {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Iniciando importação de listas padrão');
                    }
                    $count_added = $this->import_default_lists();
                    $this->cache->purge();
                    $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LIST_UPLOADED', time(), [$count_added, 'default_lists']);
                    if ($this->request->is_ajax()) {
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'message' => sprintf($this->language->lang('ACP_STOPBADBOTS_LIST_UPLOADED'), $count_added)
                        ]);
                        exit;
                    } else {
                        trigger_error(sprintf($this->language->lang('ACP_STOPBADBOTS_LIST_UPLOADED_WITH_TYPE'), $count_added, $this->language->lang('DEFAULT_LISTS')) . adm_back_link($this->u_action), E_USER_NOTICE);
                    }
                }
            }
        }
        if ($action === 'delete' && $id) {
            if (confirm_box(true)) {
                try {
                    $this->delete_list_entry($id);
                    $this->cache->purge();
                    $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LIST_DELETED', time());
                    $success = true;
                } catch (\Exception $e) {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Erro ao excluir entrada da lista: ' . $e->getMessage());
                    }
                    $error[] = $this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                }
            } else {
                confirm_box(false, $this->language->lang('CONFIRM_DELETE_LIST'), build_hidden_fields([
                    'action' => 'delete',
                    'id' => $id,
                ]));
            }
        }
        if ($action === 'mass_delete' && $this->request->is_set_post('submit')) {
            if ($this->request->is_set_post('confirm')) {
                if (confirm_box(true)) {
                    $delete_ids = $this->request->variable('delete_ids', [0]);
                    if (!empty($delete_ids) && !(count($delete_ids) === 1 && $delete_ids[0] == 0)) {
                        try {
                            foreach ($delete_ids as $del_id) {
                                $this->delete_list_entry((int) $del_id);
                            }
                            $this->cache->purge();
                            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LISTS_DELETED', time());
                            $success = true;
                        } catch (\Exception $e) {
                            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                                error_log('acp_controller: Erro ao excluir entradas em massa: ' . $e->getMessage());
                            }
                            $error[] = $this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                        }
                    } else {
                        $error[] = $this->language->lang('NO_ENTRIES_SELECTED');
                    }
                } else {
                    $error[] = $this->language->lang('FORM_INVALID');
                }
            } else {
                if (!check_form_key($form_key)) {
                    $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para mass_delete']);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Falha na validação do form_key para mass_delete');
                    }
                    $error[] = $this->language->lang('FORM_INVALID');
                } else {
                    $delete_ids = $this->request->variable('delete_ids', [0]);
                    if (empty($delete_ids) || (count($delete_ids) === 1 && $delete_ids[0] == 0)) {
                        $error[] = $this->language->lang('NO_ENTRIES_SELECTED');
                    } else {
                        confirm_box(false, $this->language->lang('CONFIRM_DELETE_SELECTED_LISTS'), build_hidden_fields([
                            'action' => 'mass_delete',
                            'delete_ids' => $delete_ids,
                            'submit' => true,
                            'form_key' => $form_key,
                            'form_token' => $this->request->variable('form_token', ''),
                            'creation_time' => $this->request->variable('creation_time', 0),
                        ]));
                    }
                }
            }
        }
        $list_type_map = [
            'ua_blacklist' => $this->language->lang('USER_AGENT_LIST'),
            'ip_blacklist' => $this->language->lang('IP_ADDRESS_LIST'),
            'ref_blacklist' => $this->language->lang('REFERER_LIST'),
            'ua_whitelist' => $this->language->lang('ACP_STOPBADBOTS_UA_WHITELIST'),
            'ip_whitelist' => $this->language->lang('ACP_STOPBADBOTS_IP_WHITELIST'),
            'ref_whitelist' => $this->language->lang('ACP_STOPBADBOTS_REF_WHITELIST'),
        ];
        $list_counts = [
            'ua_blacklist' => 0,
            'ip_blacklist' => 0,
            'ref_blacklist' => 0,
            'ua_whitelist' => 0,
            'ip_whitelist' => 0,
            'ref_whitelist' => 0,
        ];
        foreach (array_keys($list_counts) as $list_type) {
            $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists WHERE list_type = "' . $this->db->sql_escape($list_type) . '"';
            $result = $this->db->sql_query($sql);
            $list_counts[$list_type] = (int) $this->db->sql_fetchfield('count');
            $this->db->sql_freeresult($result);
        }
        $search_results = [];
        if ($search) {
            $sql_where = ' WHERE value LIKE \'%' . $this->db->sql_escape($search) . '%\'';
            $sql = 'SELECT id, list_type, value, added_at FROM ' . $this->table_prefix . 'stopbadbots_lists' . $sql_where . ' ORDER BY list_type ASC, value ASC';
            try {
                $result = $this->db->sql_query($sql);
                while ($row = $this->db->sql_fetchrow($result)) {
                    $search_results[] = [
                        'id' => $row['id'],
                        'list_type' => $list_type_map[$row['list_type']] ?? $row['list_type'],
                        'value' => htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8'),
                        'added_at' => $this->user->format_date($row['added_at']),
                        'u_delete' => $this->u_action . '&action=delete&id=' . $row['id'] . '&list_search=' . urlencode($search),
                    ];
                }
                $this->db->sql_freeresult($result);
            } catch (\Exception $e) {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Erro ao carregar resultados da pesquisa: ' . $e->getMessage());
                }
                $error[] = $this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
            }
        }
        foreach ($search_results as $entry) {
            $this->template->assign_block_vars('search_results', [
                'ID' => $entry['id'],
                'LIST_TYPE' => $entry['list_type'],
                'VALUE' => $entry['value'],
                'ADDED_TIME' => $entry['added_at'],
                'U_DELETE' => $entry['u_delete'],
            ]);
        }
        $this->template->assign_vars([
            'S_ERROR' => !empty($error),
            'ERROR_MESSAGE' => implode('<br />', $error),
            'S_SUCCESS' => $success,
            'SUCCESS_MESSAGE' => $success ? $this->language->lang('ACP_STOPBADBOTS_LIST_ADDED') : '',
            'UA_BLACKLIST_COUNT' => $list_counts['ua_blacklist'],
            'IP_BLACKLIST_COUNT' => $list_counts['ip_blacklist'],
            'REF_BLACKLIST_COUNT' => $list_counts['ref_blacklist'],
            'UA_WHITELIST_COUNT' => $list_counts['ua_whitelist'],
            'IP_WHITELIST_COUNT' => $list_counts['ip_whitelist'],
            'REF_WHITELIST_COUNT' => $list_counts['ref_whitelist'],
            'SEARCH_RESULT_COUNT' => count($search_results),
            'LIST_SEARCH' => htmlspecialchars($search, ENT_QUOTES, 'UTF-8'),
            'L_NO_SEARCH_PERFORMED' => $this->language->lang('NO_SEARCH_PERFORMED'),
            'L_NO_SEARCH_RESULTS' => $this->language->lang('NO_SEARCH_RESULTS'),
        ]);
    }
    protected function get_list_type_lang($list_type)
    {
        $list_type_map = [
            'ua_blacklist' => 'USER_AGENT_LIST',
            'ip_blacklist' => 'IP_ADDRESS_LIST',
            'ref_blacklist' => 'REFERER_LIST',
            'ua_whitelist' => 'ACP_STOPBADBOTS_UA_WHITELIST',
            'ip_whitelist' => 'ACP_STOPBADBOTS_IP_WHITELIST',
            'ref_whitelist' => 'ACP_STOPBADBOTS_REF_WHITELIST',
            'default_lists' => 'DEFAULT_LISTS',
        ];
        return $list_type_map[$list_type] ?? 'UNKNOWN_LIST_TYPE';
    }
    protected function detect_list_type($lines)
    {
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            if ($this->validate_ip_or_cidr($line)) {
                return 'ip_blacklist';
            }
            if (preg_match('/^(https?:\/\/)?([a-zA-Z0-9-]+\.)+[a-zA-Z]{2,}(\/.*)?$/', $line) || strpos($line, '.') !== false) {
                return 'ref_blacklist';
            }
            return 'ua_blacklist';
        }
        return false;
    }
    protected function import_default_lists()
    {
        $files = [
            'ua_blacklist' => 'bots.txt',
            'ip_blacklist' => 'botsip.txt',
            'ref_blacklist' => 'botsref.txt',
        ];
        $base_path = $this->config['stopbadbots_lists_path'] ?? $this->root_path . 'ext/mundophpbb/stopbadbots/assets/';
        $base_path = str_replace('\\', '/', $base_path);
        $count_added = 0;
        if (!is_dir($base_path)) {
            $error_message = sprintf($this->language->lang('FILE_NOT_FOUND'), htmlspecialchars($base_path, ENT_QUOTES, 'UTF-8'));
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Diretório não encontrado: ' . $base_path);
            }
            trigger_error($error_message . adm_back_link($this->u_action), E_USER_WARNING);
        }
        foreach ($files as $list_type => $filename) {
            $file_path = $base_path . $filename;
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Tentando importar arquivo: ' . $file_path . ', existe: ' . (file_exists($file_path) ? 'sim' : 'não') . ', legível: ' . (is_readable($file_path) ? 'sim' : 'não'));
            }
            if (file_exists($file_path) && is_readable($file_path)) {
                $content = file_get_contents($file_path);
                $content = str_replace("\xEF\xBB\xBF", '', $content); // Remove BOM
                file_put_contents($file_path . '.tmp', $content);
                $lines = file($file_path . '.tmp', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                unlink($file_path . '.tmp');
                if ($lines === false) {
                    $error_message = sprintf($this->language->lang('FILE_READ_ERROR'), htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'));
                    $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_IMPORT_FAILED', time(), ['Erro de leitura do arquivo: ' . $file_path]);
                    trigger_error($error_message . adm_back_link($this->u_action), E_USER_WARNING);
                    continue;
                }
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Linhas lidas do arquivo ' . $file_path . ': ' . count($lines));
                    error_log('acp_controller: Primeiras 5 linhas: ' . implode(', ', array_slice($lines, 0, 5)));
                }
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (!empty($line)) {
                        if ($list_type === 'ua_blacklist' || $list_type === 'ref_blacklist') {
                            $parts = explode(',', $line);
                            $line = trim($parts[0]);
                        } elseif ($list_type === 'ip_blacklist') {
                            $parts = explode(',', $line);
                            $line = trim($parts[0]);
                        }
                        if (!empty($line) && ($list_type !== 'ip_blacklist' || $this->validate_ip_or_cidr($line))) {
                            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                                error_log('acp_controller: Importando entrada: ' . $line . ' para ' . $list_type);
                            }
                            if ($this->add_list_entry($list_type, $line)) {
                                $count_added++;
                            }
                        } else {
                            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                                error_log('acp_controller: Entrada inválida ignorada: ' . $line . ' para ' . $list_type);
                            }
                        }
                    }
                }
            } else {
                $error_message = sprintf($this->language->lang('FILE_NOT_FOUND'), htmlspecialchars($filename, ENT_QUOTES, 'UTF-8'));
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_IMPORT_FAILED', time(), ['Arquivo não encontrado ou não legível: ' . $file_path]);
                trigger_error($error_message . adm_back_link($this->u_action), E_USER_WARNING);
            }
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Total de entradas adicionadas: ' . $count_added);
        }
        return $count_added;
    }
    protected function handle_logs()
    {
        $form_key = 'sbb_logs';
        add_form_key($form_key);
        $action = $this->request->variable('action', '');
        if ($this->request->is_set_post('submit_search') || $this->request->is_set_post('clear_search')) {
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para submit_search/clear_search']);
                trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
            }
            $log_search = $this->request->variable('log_search', '', true);
            $log_date_from = $this->request->variable('log_date_from', '');
            $log_date_to = $this->request->variable('log_date_to', '');
            $log_reason = $this->request->variable('log_reason', '');
            if ($this->request->is_set_post('clear_search')) {
                $log_search = '';
                $log_date_from = '';
                $log_date_to = '';
                $log_reason = '';
            }
            $query_string = '';
            if ($log_search) {
                $query_string .= '&log_search=' . urlencode($log_search);
            }
            if ($log_date_from) {
                $query_string .= '&log_date_from=' . urlencode($log_date_from);
            }
            if ($log_date_to) {
                $query_string .= '&log_date_to=' . urlencode($log_date_to);
            }
            if ($log_reason) {
                $query_string .= '&log_reason=' . urlencode($log_reason);
            }
            redirect($this->u_action . $query_string);
        }
        $log_search = $this->request->variable('log_search', '', true);
        $log_date_from = $this->request->variable('log_date_from', '');
        $log_date_to = $this->request->variable('log_date_to', '');
        $log_reason = $this->request->variable('log_reason', '');
        $start = $this->request->variable('start', 0);
        $per_page = 50;
        if ($action === 'clear_log') {
            $this->clear_log();
            return;
        }
        if ($action === 'export_csv') {
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para export_csv']);
                trigger_error($this->language->lang('FORM_INVALID') . adm_back_link($this->u_action), E_USER_WARNING);
            }
            $where_clauses = [];
            if ($log_search) {
                $escaped_search = $this->db->sql_escape(utf8_clean_string($log_search));
                $where_clauses[] = "(user_agent LIKE '%$escaped_search%' OR ip LIKE '%$escaped_search%' OR referer LIKE '%$escaped_search%' OR reason LIKE '%$escaped_search%')";
            }
            if ($log_date_from) {
                $from_timestamp = strtotime($log_date_from . ' 00:00:00');
                if ($from_timestamp !== false) {
                    $where_clauses[] = 'log_time >= ' . (int) $from_timestamp;
                }
            }
            if ($log_date_to) {
                $to_timestamp = strtotime($log_date_to . ' 23:59:59');
                if ($to_timestamp !== false) {
                    $where_clauses[] = 'log_time <= ' . (int) $to_timestamp;
                }
            }
            if ($log_reason) {
                $where_clauses[] = 'reason = \'' . $this->db->sql_escape($log_reason) . '\'';
            }
            $sql_where_clause = !empty($where_clauses) ? ' WHERE ' . implode(' AND ', $where_clauses) : '';
            $sql = 'SELECT * FROM ' . $this->table_prefix . 'stopbadbots_log' . $sql_where_clause . ' ORDER BY log_time DESC';
            $result = $this->db->sql_query($sql);
            $output = fopen('php://output', 'w');
            if ($output === false) {
                trigger_error('Erro ao criar stream de saída.', E_USER_WARNING);
            }
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="stopbadbots_logs_' . date('Y-m-d') . '.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($output, ['Data e Hora', 'User Agent', 'IP', 'Referer', 'Motivo']);
            while ($row = $this->db->sql_fetchrow($result)) {
                $formatted_time = date('Y-m-d H:i:s', $row['log_time']);
                fputcsv($output, [
                    $formatted_time,
                    $row['user_agent'],
                    $row['ip'],
                    $row['referer'],
                    $row['reason']
                ]);
            }
            $this->db->sql_freeresult($result);
            fclose($output);
            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LOGS_EXPORTED', time());
            exit;
        }
        $where_clauses = [];
        if ($log_search) {
            $escaped_search = $this->db->sql_escape(utf8_clean_string($log_search));
            $where_clauses[] = "(user_agent LIKE '%$escaped_search%' OR ip LIKE '%$escaped_search%' OR referer LIKE '%$escaped_search%' OR reason LIKE '%$escaped_search%')";
        }
        if ($log_date_from) {
            $from_timestamp = strtotime($log_date_from . ' 00:00:00');
            if ($from_timestamp !== false) {
                $where_clauses[] = 'log_time >= ' . (int) $from_timestamp;
            }
        }
        if ($log_date_to) {
            $to_timestamp = strtotime($log_date_to . ' 23:59:59');
            if ($to_timestamp !== false) {
                $where_clauses[] = 'log_time <= ' . (int) $to_timestamp;
            }
        }
        if ($log_reason) {
            $where_clauses[] = 'reason = \'' . $this->db->sql_escape($log_reason) . '\'';
        }
        $sql_where_clause = !empty($where_clauses) ? ' WHERE ' . implode(' AND ', $where_clauses) : '';
        $sql = 'SELECT COUNT(*) AS total FROM ' . $this->table_prefix . 'stopbadbots_log' . $sql_where_clause;
        $result = $this->db->sql_query($sql);
        $total_logs = (int) $this->db->sql_fetchfield('total');
        $this->db->sql_freeresult($result);
        try {
            $sql = 'SELECT * FROM ' . $this->table_prefix . 'stopbadbots_log' . $sql_where_clause . ' ORDER BY log_time DESC LIMIT ' . (int) $per_page . ' OFFSET ' . (int) $start;
            $result = $this->db->sql_query($sql);
            $logs = [];
            while ($row = $this->db->sql_fetchrow($result)) {
                $logs[] = [
                    'LOG_TIME' => $this->user->format_date($row['log_time']),
                    'USER_AGENT' => htmlspecialchars($row['user_agent'], ENT_QUOTES, 'UTF-8'),
                    'IP' => htmlspecialchars($row['ip'], ENT_QUOTES, 'UTF-8'),
                    'REFERER' => htmlspecialchars($row['referer'], ENT_QUOTES, 'UTF-8'),
                    'REASON' => htmlspecialchars($row['reason'], ENT_QUOTES, 'UTF-8'),
                ];
            }
            $this->db->sql_freeresult($result);
            foreach ($logs as $log) {
                $this->template->assign_block_vars('log_entries', $log);
            }
            $base_url = $this->u_action . '&log_search=' . urlencode($log_search) . '&log_date_from=' . urlencode($log_date_from) . '&log_date_to=' . urlencode($log_date_to) . '&log_reason=' . urlencode($log_reason);
            $this->pagination->generate_template_pagination(
                $base_url,
                'pagination',
                'start',
                $total_logs,
                $per_page,
                $start
            );
            $this->template->assign_vars([
                'LOG_SEARCH' => htmlspecialchars($log_search, ENT_QUOTES, 'UTF-8'),
                'LOG_DATE_FROM' => htmlspecialchars($log_date_from, ENT_QUOTES, 'UTF-8'),
                'LOG_DATE_TO' => htmlspecialchars($log_date_to, ENT_QUOTES, 'UTF-8'),
                'LOG_REASON' => htmlspecialchars($log_reason, ENT_QUOTES, 'UTF-8'),
                'TOTAL_LOGS' => $total_logs,
                'PER_PAGE' => $per_page,
                'PAGE_NUMBER' => floor($start / $per_page) + 1,
            ]);
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao carregar logs: ' . $e->getMessage());
            }
            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LOG_ERROR', time(), ['Erro ao carregar logs: ' . $e->getMessage()]);
            trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
        }
    }
    protected function clear_log()
    {
        if (confirm_box(true)) {
            $sql = 'TRUNCATE TABLE ' . $this->table_prefix . 'stopbadbots_log';
            try {
                $this->db->sql_query($sql);
                $this->cache->purge();
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'LOG_CLEARED', time());
                trigger_error($this->language->lang('LOG_CLEARED') . adm_back_link($this->u_action), E_USER_NOTICE);
            } catch (\Exception $e) {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Erro ao limpar logs: ' . $e->getMessage());
                }
                trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
            }
        } else {
            confirm_box(false, $this->language->lang('CONFIRM_CLEAR_LOG'), build_hidden_fields([
                'action' => 'clear_log',
            ]));
        }
    }
    protected function add_list_entry($list_type, $value)
    {
        $value = substr($value, 0, 255);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: Tentando adicionar entrada: list_type=' . $list_type . ', value=' . $value);
        }
        $normalized_value = $value;
        if ($list_type === 'ua_blacklist' || $list_type === 'ref_blacklist') {
            $normalized_value = preg_replace('/,.+$/', '', $value);
            $value = $normalized_value;
        }
        $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists
                WHERE list_type = "' . $this->db->sql_escape($list_type) . '"
                AND value = "' . $this->db->sql_escape($normalized_value) . '"';
        $result = $this->db->sql_query($sql);
        $count = (int) $this->db->sql_fetchfield('count');
        $this->db->sql_freeresult($result);
        if ($count > 0) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Entrada duplicada detectada para ' . $list_type . ': ' . $value);
            }
            return false;
        }
        $sql_array = [
            'list_type' => $list_type,
            'value' => $value,
            'added_at' => time(),
        ];
        $sql = 'INSERT INTO ' . $this->table_prefix . 'stopbadbots_lists ' . $this->db->sql_build_array('INSERT', $sql_array);
        try {
            $this->db->sql_query($sql);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Entrada adicionada com sucesso: ' . $value);
            }
            return true;
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao adicionar entrada na lista: ' . $e->getMessage());
            }
            return false;
        }
    }
    protected function update_list_entry($id, $value)
    {
        $value = substr($value, 0, 255);
        $sql = 'SELECT list_type FROM ' . $this->table_prefix . 'stopbadbots_lists WHERE id = ' . (int) $id;
        $result = $this->db->sql_query($sql);
        $row = $this->db->sql_fetchrow($result);
        $this->db->sql_freeresult($result);
        if (!$row) {
            trigger_error($this->language->lang('NO_ENTRY_FOUND') . adm_back_link($this->u_action), E_USER_WARNING);
        }
        $list_type = $row['list_type'];
        $sql = 'SELECT id FROM ' . $this->table_prefix . 'stopbadbots_lists
                WHERE list_type = "' . $this->db->sql_escape($list_type) . '"
                AND value = "' . $this->db->sql_escape($value) . '"
                AND id != ' . (int) $id;
        $result = $this->db->sql_query($sql);
        if ($this->db->sql_fetchrow($result)) {
            $this->db->sql_freeresult($result);
            trigger_error($this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY') . adm_back_link($this->u_action), E_USER_WARNING);
        }
        $this->db->sql_freeresult($result);
        $sql = 'UPDATE ' . $this->table_prefix . 'stopbadbots_lists SET value = "' . $this->db->sql_escape($value) . '" WHERE id = ' . (int) $id;
        try {
            $this->db->sql_query($sql);
            $this->cache->purge();
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao atualizar entrada na lista: ' . $e->getMessage());
            }
            trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
        }
    }
    protected function delete_list_entry($id)
    {
        $sql = 'DELETE FROM ' . $this->table_prefix . 'stopbadbots_lists WHERE id = ' . (int) $id;
        try {
            $this->db->sql_query($sql);
            $this->cache->purge();
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao excluir entrada da lista: ' . $e->getMessage());
            }
            trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
        }
    }
    protected function save_lists($data)
    {
        $list_types = ['ua_blacklist', 'ip_blacklist', 'ref_blacklist', 'ua_whitelist', 'ip_whitelist', 'ref_whitelist'];
        $sql = 'DELETE FROM ' . $this->table_prefix . 'stopbadbots_lists';
        try {
            $this->db->sql_query($sql);
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao excluir listas: ' . $e->getMessage());
            }
            trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
        }
        foreach ($list_types as $list_type) {
            if (!empty($data[$list_type])) {
                foreach ($data[$list_type] as $value) {
                    $value = trim($value);
                    if (!empty($value) && ($list_type !== 'ip_blacklist' && $list_type !== 'ip_whitelist' || $this->validate_ip_or_cidr($value))) {
                        $this->add_list_entry($list_type, $value);
                    }
                }
            }
        }
    }
    protected function load_lists($search = '')
    {
        $lists = [
            'ua_blacklist' => [],
            'ip_blacklist' => [],
            'ref_blacklist' => [],
            'ua_whitelist' => [],
            'ip_whitelist' => [],
            'ref_whitelist' => [],
        ];
        $sql_where = $search ? " WHERE value LIKE '%" . $this->db->sql_escape($search) . "%'" : '';
        $sql = 'SELECT id, list_type, value, added_at FROM ' . $this->table_prefix . 'stopbadbots_lists' . $sql_where . ' ORDER BY list_type ASC, value ASC';
        try {
            $result = $this->db->sql_query($sql);
            while ($row = $this->db->sql_fetchrow($result)) {
                $lists[$row['list_type']][] = [
                    'id' => $row['id'],
                    'value' => $row['value'],
                    'added_at' => $row['added_at'],
                ];
            }
            $this->db->sql_freeresult($result);
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao carregar listas: ' . $e->getMessage());
            }
            trigger_error($this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8')) . adm_back_link($this->u_action), E_USER_ERROR);
        }
        return $lists;
    }
    protected function sanitize_list($input, $is_user_agent = false)
    {
        $lines = explode("\n", trim($input));
        $sanitized = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                if ($is_user_agent) {
                    $parts = explode(',', $line);
                    $line = trim($parts[0]);
                    if (!preg_match('/^[a-zA-Z0-9\s\/\(\)\.\-_+;]+$/', $line)) {
                        continue;
                    }
                }
                if (!empty($line)) {
                    $sanitized[] = substr($line, 0, 255);
                }
            }
        }
        $sanitized = array_unique($sanitized);
        sort($sanitized);
        return $sanitized;
    }
    protected function sanitize_ip_list($input)
    {
        $lines = explode("\n", trim($input));
        $sanitized = [];
        foreach ($lines as $line) {
            $line = trim($line);
            if (!empty($line)) {
                $parts = explode(',', $line);
                $ip = trim($parts[0]);
                if ($this->validate_ip_or_cidr($ip)) {
                    $sanitized[] = $ip;
                }
            }
        }
        $sanitized = array_unique($sanitized);
        sort($sanitized);
        return $sanitized;
    }
    protected function validate_ip_or_cidr($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            return true;
        }
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/', $ip)) {
            list($base_ip, $mask) = explode('/', $ip);
            if (filter_var($base_ip, FILTER_VALIDATE_IP) && $mask >= 0 && $mask <= 32) {
                $octets = explode('.', $base_ip);
                foreach ($octets as $octet) {
                    if ($octet < 0 || $octet > 255) {
                        return false;
                    }
                }
                return true;
            }
        }
        return false;
    }
    protected function handle_settings()
    {
        $form_key = 'sbb_config';
        add_form_key($form_key);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: handle_settings - Form token: ' . $this->request->variable('form_token', '') . ', creation_time: ' . $this->request->variable('creation_time', 0));
        }
        $error = [];
        $success = false;
        if ($this->request->is_set_post('submit')) {
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para configurações']);
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Falha na validação do form_key para configurações');
                }
                $error[] = $this->language->lang('FORM_INVALID');
            } else {
                $data = [
                    'stopbadbots_enabled' => (int) $this->request->variable('stopbadbots_enabled', 0, true, \phpbb\request\request::POST) === 1 ? 1 : 0,
                    'stopbadbots_cron_enabled' => (int) $this->request->variable('stopbadbots_cron_enabled', 0, true, \phpbb\request\request::POST) === 1 ? 1 : 0,
                    'stopbadbots_cron_interval' => max(3600, min(604800, (int) $this->request->variable('stopbadbots_cron_interval', 86400))),
                    'stopbadbots_log_retention_days' => max(1, min(365, (int) $this->request->variable('stopbadbots_log_retention_days', 30))),
                    'stopbadbots_use_x_forwarded_for' => (int) $this->request->variable('stopbadbots_use_x_forwarded_for', 0, true, \phpbb\request\request::POST) === 1 ? 1 : 0,
                    'stopbadbots_debug_enabled' => (int) $this->request->variable('stopbadbots_debug_enabled', 0, true, \phpbb\request\request::POST) === 1 ? 1 : 0,
                ];
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Configurações recebidas - ' . json_encode($data));
                }
                                $this->config->set('stopbadbots_enabled', $data['stopbadbots_enabled']);
                $this->config->set('stopbadbots_cron_enabled', $data['stopbadbots_cron_enabled']);
                $this->config->set('stopbadbots_cron_interval', $data['stopbadbots_cron_interval']);
                $this->config->set('stopbadbots_log_retention_days', $data['stopbadbots_log_retention_days']);
                $this->config->set('stopbadbots_use_x_forwarded_for', $data['stopbadbots_use_x_forwarded_for']);
                $this->config->set('stopbadbots_debug_enabled', $data['stopbadbots_debug_enabled']);
                $this->cache->purge();
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_SETTINGS_SAVED', time());
                $success = true;
            }
        }
        // Calcular estatísticas diárias
        $today_start = strtotime('today midnight');
        $sql = 'SELECT reason, COUNT(*) AS count
                FROM ' . $this->table_prefix . 'stopbadbots_log
                WHERE log_time >= ' . (int) $today_start . '
                GROUP BY reason';
        try {
            $result = $this->db->sql_query($sql);
            $daily_blocks = [
                'user_agent' => 0,
                'ip' => 0,
                'referer' => 0,
            ];
            while ($row = $this->db->sql_fetchrow($result)) {
                if (stripos($row['reason'], 'User-Agent') !== false) {
                    $daily_blocks['user_agent'] += (int) $row['count'];
                } elseif (stripos($row['reason'], 'IP') !== false) {
                    $daily_blocks['ip'] += (int) $row['count'];
                } elseif (stripos($row['reason'], 'Referer') !== false) {
                    $daily_blocks['referer'] += (int) $row['count'];
                }
            }
            $this->db->sql_freeresult($result);
            $total_daily_blocks = array_sum($daily_blocks);
            $this->config->set('stopbadbots_daily_blocks', $total_daily_blocks);
            $this->config->set('stopbadbots_daily_ua_blocks', $daily_blocks['user_agent']);
            $this->config->set('stopbadbots_daily_ip_blocks', $daily_blocks['ip']);
            $this->config->set('stopbadbots_daily_ref_blocks', $daily_blocks['referer']);
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('acp_controller: Erro ao calcular estatísticas diárias: ' . $e->getMessage());
            }
            $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LOG_ERROR', time(), ['Erro ao calcular estatísticas: ' . $e->getMessage()]);
            $error[] = $this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
        // Atribuir variáveis ao template
        $this->template->assign_vars([
            'S_ERROR' => !empty($error),
            'ERROR_MESSAGE' => implode('<br />', $error),
            'S_SUCCESS' => $success,
            'SUCCESS_MESSAGE' => $success ? $this->language->lang('ACP_STOPBADBOTS_SAVED') : '',
            'STOPBADBOTS_ENABLED' => (int) ($this->config['stopbadbots_enabled'] ?? 0),
            'STOPBADBOTS_CRON_ENABLED' => (int) ($this->config['stopbadbots_cron_enabled'] ?? 0),
            'STOPBADBOTS_CRON_INTERVAL' => (int) ($this->config['stopbadbots_cron_interval'] ?? 86400),
            'STOPBADBOTS_LOG_RETENTION_DAYS' => (int) ($this->config['stopbadbots_log_retention_days'] ?? 30),
            'STOPBADBOTS_USE_X_FORWARDED_FOR' => (int) ($this->config['stopbadbots_use_x_forwarded_for'] ?? 0),
            'STOPBADBOTS_DEBUG_ENABLED' => (int) ($this->config['stopbadbots_debug_enabled'] ?? 0),
            'STOPBADBOTS_DAILY_UA_BLOCKS' => (int) ($this->config['stopbadbots_daily_ua_blocks'] ?? $daily_blocks['user_agent'] ?? 0),
            'STOPBADBOTS_DAILY_IP_BLOCKS' => (int) ($this->config['stopbadbots_daily_ip_blocks'] ?? $daily_blocks['ip'] ?? 0),
            'STOPBADBOTS_DAILY_REF_BLOCKS' => (int) ($this->config['stopbadbots_daily_ref_blocks'] ?? $daily_blocks['referer'] ?? 0),
            'DAILY_BLOCKS' => (int) ($this->config['stopbadbots_daily_blocks'] ?? $total_daily_blocks ?? 0),
            'CRON_LAST_RUN' => $this->config['stopbadbots_cron_last_run'] ? $this->user->format_date($this->config['stopbadbots_cron_last_run']) : $this->language->lang('NEVER'),
        ]);
    }
    protected function handle_overview()
    {
        $form_key = 'sbb_lists';
        add_form_key($form_key);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: handle_overview - Form token: ' . $this->request->variable('form_token', '') . ', creation_time: ' . $this->request->variable('creation_time', 0));
        }
        $list_counts = [
            'ua_blacklist' => 0,
            'ip_blacklist' => 0,
            'ref_blacklist' => 0,
            'ua_whitelist' => 0,
            'ip_whitelist' => 0,
            'ref_whitelist' => 0,
        ];
        $list_values = [
            'ua_blacklist' => [],
            'ip_blacklist' => [],
            'ref_blacklist' => [],
            'ua_whitelist' => [],
            'ip_whitelist' => [],
            'ref_whitelist' => [],
        ];
        $sql = "SHOW TABLES LIKE '" . $this->db->sql_escape($this->table_prefix . 'stopbadbots_lists') . "'";
        $result = $this->db->sql_query($sql);
        $table_exists = $this->db->sql_fetchrow($result) !== false;
        $this->db->sql_freeresult($result);
        if ($table_exists) {
            foreach (array_keys($list_counts) as $list_type) {
                $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists WHERE list_type = "' . $this->db->sql_escape($list_type) . '"';
                $result = $this->db->sql_query($sql);
                $list_counts[$list_type] = (int) $this->db->sql_fetchfield('count');
                $this->db->sql_freeresult($result);
                $sql = 'SELECT value FROM ' . $this->table_prefix . 'stopbadbots_lists
                        WHERE list_type = "' . $this->db->sql_escape($list_type) . '"
                        ORDER BY value ASC';
                $result = $this->db->sql_query($sql);
                while ($row = $this->db->sql_fetchrow($result)) {
                    $list_values[$list_type][] = ['VALUE' => htmlspecialchars($row['value'], ENT_QUOTES, 'UTF-8')];
                }
                $this->db->sql_freeresult($result);
            }
        }
        $this->template->assign_vars([
            'UA_BLACKLIST_COUNT' => $list_counts['ua_blacklist'],
            'IP_BLACKLIST_COUNT' => $list_counts['ip_blacklist'],
            'REF_BLACKLIST_COUNT' => $list_counts['ref_blacklist'],
            'UA_WHITELIST_COUNT' => $list_counts['ua_whitelist'],
            'IP_WHITELIST_COUNT' => $list_counts['ip_whitelist'],
            'REF_WHITELIST_COUNT' => $list_counts['ref_whitelist'],
        ]);
        foreach ($list_values as $list_type => $values) {
            foreach ($values as $value) {
                $this->template->assign_block_vars($list_type, $value);
            }
        }
    }
    protected function handle_save_lists()
    {
        $form_key = 'sbb_lists';
        add_form_key($form_key);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('acp_controller: handle_save_lists - Form token: ' . $this->request->variable('form_token', '') . ', creation_time: ' . $this->request->variable('creation_time', 0));
        }
        $error = [];
        $success = false;
        if ($this->request->is_set_post('submit')) {
            if (!check_form_key($form_key)) {
                $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'FORM_INVALID', time(), ['Form key inválido para save_lists']);
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('acp_controller: Falha na validação do form_key para save_lists');
                }
                $error[] = $this->language->lang('FORM_INVALID');
            } else {
                $data = [
                    'ua_blacklist' => $this->sanitize_list($this->request->variable('stopbadbots_ua_list', '', true), true),
                    'ip_blacklist' => $this->sanitize_ip_list($this->request->variable('stopbadbots_ip_list', '', true)),
                    'ref_blacklist' => $this->sanitize_list($this->request->variable('stopbadbots_ref_list', '', true), false),
                    'ua_whitelist' => $this->sanitize_list($this->request->variable('stopbadbots_ua_whitelist', '', true), true),
                    'ip_whitelist' => $this->sanitize_ip_list($this->request->variable('stopbadbots_ip_whitelist', '', true)),
                    'ref_whitelist' => $this->sanitize_list($this->request->variable('stopbadbots_ref_whitelist', '', true), false),
                ];
                $duplicate_entries = [];
                foreach ($data as $list_type => $values) {
                    foreach ($values as $value) {
                        $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists
                                WHERE list_type = "' . $this->db->sql_escape($list_type) . '"
                                AND value = "' . $this->db->sql_escape($value) . '"';
                        $result = $this->db->sql_query($sql);
                        $count = (int) $this->db->sql_fetchfield('count');
                        $this->db->sql_freeresult($result);
                        if ($count > 0) {
                            $duplicate_entries[] = sprintf($this->language->lang('ACP_STOPBADBOTS_DUPLICATE_ENTRY'), htmlspecialchars($value, ENT_QUOTES, 'UTF-8'), $list_type);
                        }
                    }
                }
                if (!empty($duplicate_entries)) {
                    $error = array_merge($error, $duplicate_entries);
                    $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_DUPLICATE_ENTRIES', time(), $duplicate_entries);
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('acp_controller: Entradas duplicadas detectadas: ' . implode(', ', $duplicate_entries));
                    }
                } else {
                    try {
                        $this->save_lists($data);
                        $this->cache->purge();
                        $this->logger->add('admin', $this->user->data['user_id'], $this->user->ip, 'STOPBADBOTS_LISTS_SAVED', time());
                        $success = true;
                    } catch (\Exception $e) {
                        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                            error_log('acp_controller: Erro ao salvar listas: ' . $e->getMessage());
                        }
                        $error[] = $this->language->lang('DB_ERROR', htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
                    }
                }
            }
        }
        $this->template->assign_vars([
            'S_ERROR' => !empty($error),
            'ERROR_MESSAGE' => implode('<br />', $error),
            'S_SUCCESS' => $success,
            'SUCCESS_MESSAGE' => $success ? $this->language->lang('ACP_STOPBADBOTS_LISTS_SAVED') : '',
        ]);
        $this->handle_overview();
    }
}