<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2025 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */
namespace mundophpbb\stopbadbots\migrations;

class v1_0_0 extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        $required_tables = [
            $this->table_prefix . 'stopbadbots_log',
            $this->table_prefix . 'stopbadbots_lists',
        ];

        foreach ($required_tables as $table) {
            if (!$this->db_tools->sql_table_exists($table)) {
                error_log("Migration v1_0_0: Tabela {$table} não existe.");
                return false;
            }
        }

        error_log("Migration v1_0_0: Todas as tabelas necessárias existem.");
        return $this->config->offsetExists('stopbadbots_enabled');
    }

    public static function depends_on()
    {
        return [];
    }

    public function update_schema()
    {
        error_log("Migration v1_0_0: Executando update_schema para criar tabelas da extensão.");
        return [
            'add_tables' => [
                $this->table_prefix . 'stopbadbots_log' => [
                    'COLUMNS' => [
                        'log_id' => ['UINT:10', null, 'auto_increment'],
                        'log_time' => ['TIMESTAMP', 0],
                        'user_agent' => ['VCHAR:255', ''],
                        'ip' => ['VCHAR:45', ''],
                        'referer' => ['TEXT_UNI', ''],
                        'reason' => ['TEXT_UNI', ''],
                    ],
                    'PRIMARY_KEY' => 'log_id',
                    'KEYS' => [
                        'log_time_idx' => ['INDEX', 'log_time'],
                        'ip_idx' => ['INDEX', 'ip'],
                    ],
                ],
                $this->table_prefix . 'stopbadbots_lists' => [
                    'COLUMNS' => [
                        'id' => ['UINT:10', null, 'auto_increment'],
                        'list_type' => ['VCHAR:50', ''],
                        'value' => ['VCHAR:255', ''],
                        'added_at' => ['TIMESTAMP', 0],
                    ],
                    'PRIMARY_KEY' => 'id',
                    'KEYS' => [
                        'list_type_idx' => ['INDEX', 'list_type'],
                        'value_idx' => ['INDEX', 'value(255)'],
                    ],
                ],
            ],
        ];
    }

    public function revert_schema()
    {
        error_log("Migration v1_0_0: Executando revert_schema para remover tabelas da extensão.");
        return [
            'drop_tables' => [
                $this->table_prefix . 'stopbadbots_log',
                $this->table_prefix . 'stopbadbots_lists',
            ],
        ];
    }

    public function update_data()
    {
        error_log("Migration v1_0_0: Executando update_data para adicionar configurações e módulos.");
        return [
            // Adicionar configurações iniciais
            ['config.add', ['stopbadbots_enabled', 1]],
            ['config.add', ['stopbadbots_cron_enabled', 1]],
            ['config.add', ['stopbadbots_cron_interval', 86400]],
            ['config.add', ['stopbadbots_log_retention_days', 30]],
            ['config.add', ['stopbadbots_cron_last_run', 0]],
            ['config.add', ['stopbadbots_lists_path', dirname(__FILE__, 2) . '/assets/']],
            ['config.add', ['stopbadbots_daily_blocks', 0]],
            ['config.add', ['stopbadbots_daily_ua_blocks', 0]],
            ['config.add', ['stopbadbots_daily_ip_blocks', 0]],
            ['config.add', ['stopbadbots_daily_ref_blocks', 0]],
            ['config.add', ['stopbadbots_use_x_forwarded_for', 0]], // Nova configuração
            // Adicionar listas iniciais
            ['custom', [[$this, 'add_initial_lists']]],
            // Adicionar categoria da extensão
            ['module.add', [
                'acp',
                'ACP_CAT_DOT_MODS',
                'ACP_STOPBADBOTS_TITLE',
            ]],
            // Adicionar módulo Configurações
            ['module.add', [
                'acp',
                'ACP_STOPBADBOTS_TITLE',
                [
                    'module_basename' => '\mundophpbb\stopbadbots\acp\main_module',
                    'module_langname' => 'ACP_STOPBADBOTS_SETTINGS',
                    'module_mode' => 'settings',
                    'module_auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                ],
            ]],
            // Adicionar módulo Listas
            ['module.add', [
                'acp',
                'ACP_STOPBADBOTS_TITLE',
                [
                    'module_basename' => '\mundophpbb\stopbadbots\acp\main_module',
                    'module_langname' => 'ACP_STOPBADBOTS_LISTS',
                    'module_mode' => 'lists',
                    'module_auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                ],
            ]],
            // Adicionar módulo Logs
            ['module.add', [
                'acp',
                'ACP_STOPBADBOTS_TITLE',
                [
                    'module_basename' => '\mundophpbb\stopbadbots\acp\main_module',
                    'module_langname' => 'ACP_STOPBADBOTS_LOGS',
                    'module_mode' => 'logs',
                    'module_auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                ],
            ]],
            // Adicionar módulo Overview
            ['module.add', [
                'acp',
                'ACP_STOPBADBOTS_TITLE',
                [
                    'module_basename' => '\mundophpbb\stopbadbots\acp\main_module',
                    'module_langname' => 'ACP_STOPBADBOTS_OVERVIEW',
                    'module_mode' => 'overview',
                    'module_auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                ],
            ]],
            // Adicionar módulo Restaurar
            ['module.add', [
                'acp',
                'ACP_STOPBADBOTS_TITLE',
                [
                    'module_basename' => '\mundophpbb\stopbadbots\acp\main_module',
                    'module_langname' => 'ACP_STOPBADBOTS_RESTORE',
                    'module_mode' => 'restore',
                    'module_auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                ],
            ]],
        ];
    }

    public function add_initial_lists()
    {
        error_log("Migration v1_0_0: Adicionando listas iniciais.");
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

        foreach ($initial_lists as $list) {
            if (in_array($list['list_type'], ['ip_blacklist', 'ip_whitelist']) && !$this->validate_ip_or_cidr($list['value'])) {
                error_log("Migration v1_0_0: IP inválido ignorado: {$list['value']} para {$list['list_type']}");
                continue;
            }

            $sql = 'SELECT COUNT(*) AS count FROM ' . $this->table_prefix . 'stopbadbots_lists 
                    WHERE list_type = "' . $this->db->sql_escape($list['list_type']) . '" 
                    AND value = "' . $this->db->sql_escape($list['value']) . '"';
            try {
                $result = $this->db->sql_query($sql);
                $count = (int) $this->db->sql_fetchfield('count');
                $this->db->sql_freeresult($result);

                if ($count == 0) {
                    $sql = 'INSERT INTO ' . $this->table_prefix . 'stopbadbots_lists ' . $this->db->sql_build_array('INSERT', $list);
                    error_log("Migration v1_0_0: Adicionando entrada inicial: " . $sql);
                    $this->db->sql_query($sql);
                } else {
                    error_log("Migration v1_0_0: Entrada duplicada ignorada: {$list['list_type']} - {$list['value']}");
                }
            } catch (\Exception $e) {
                error_log("Migration v1_0_0: Erro ao adicionar entrada inicial: " . $e->getMessage());
            }
        }
    }

    protected function validate_ip_or_cidr($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            error_log("Migration v1_0_0: IP válido: {$ip}");
            return true;
        }
        if (preg_match('/^(\d{1,3}\.){3}\d{1,3}\/\d{1,2}$/', $ip)) {
            list($base_ip, $mask) = explode('/', $ip);
            if (filter_var($base_ip, FILTER_VALIDATE_IP) && $mask >= 0 && $mask <= 32) {
                error_log("Migration v1_0_0: CIDR válido: {$ip}");
                return true;
            }
        }
        error_log("Migration v1_0_0: IP ou CIDR inválido: {$ip}");
        return false;
    }
}