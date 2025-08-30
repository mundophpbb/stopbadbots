<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2024 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

namespace mundophpbb\stopbadbots\acp;

class main_module
{
    public $u_action;
    public $tpl_name;
    public $page_title;

    public function main($id, $mode)
    {
        global $phpbb_container, $phpbb_root_path, $phpEx, $user, $auth, $config;

        // Iniciar sessão e verificar autenticação
        $user->session_begin();
        $auth->acl($user->data);

        // Verificar permissões de administrador
        if (!$auth->acl_get('a_board')) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_module: Acesso negado - usuário sem permissão a_board, user_id: ' . $user->data['user_id']);
            }
            trigger_error('NO_AUTH_ADMIN', E_USER_ERROR);
        }

        // Determinar o idioma do usuário ou do fórum, com fallback para 'en'
        $lang_code = !empty($user->lang_name) ? $user->lang_name : (!empty($config['default_lang']) ? $config['default_lang'] : 'en');
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_module: Idioma selecionado: ' . $lang_code);
        }

        // Carregar idioma com verificação robusta
        try {
            $language = $phpbb_container->get('language');
            $language_file = str_replace('\\', '/', $phpbb_root_path) . 'ext/mundophpbb/stopbadbots/language/' . $lang_code . '/stopbadbots.php';
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_module: Tentando carregar idioma em: ' . $language_file . ', existe: ' . (file_exists($language_file) ? 'sim' : 'não') . ', legível: ' . (is_readable($language_file) ? 'sim' : 'não'));
            }
            if (file_exists($language_file) && is_readable($language_file)) {
                $language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', $lang_code);
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('main_module: Idioma carregado com sucesso: ' . $lang_code);
                }
            } else {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('main_module: Arquivo de idioma não encontrado ou não legível: ' . $language_file);
                }
                $language->add_lang('stopbadbots', 'mundophpbb/stopbadbots', 'en');
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('main_module: Idioma ' . $lang_code . ' não encontrado, usando fallback para en');
                }
            }
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_module: Erro ao carregar idioma: ' . $e->getMessage());
            }
            $template = $phpbb_container->get('template');
            $template->assign_vars([
                'S_ERROR' => true,
                'ERROR_MESSAGE' => $language->lang('LOG_ERROR', htmlspecialchars($e->getMessage())),
            ]);
            $this->tpl_name = 'stopbadbots_' . $mode;
            $this->page_title = 'ACP_STOPBADBOTS_' . strtoupper($mode);
            return;
        }

        // Define o template e título com base no modo
        switch ($mode) {
            case 'settings':
                $this->tpl_name = 'stopbadbots_admin';
                $this->page_title = 'ACP_STOPBADBOTS_SETTINGS';
                break;
            case 'lists':
                $this->tpl_name = 'stopbadbots_lists';
                $this->page_title = 'ACP_STOPBADBOTS_LISTS';
                break;
            case 'logs':
                $this->tpl_name = 'stopbadbots_logs';
                $this->page_title = 'ACP_STOPBADBOTS_LOGS';
                break;
            case 'restore':
                $this->tpl_name = 'stopbadbots_restore';
                $this->page_title = 'ACP_STOPBADBOTS_RESTORE';
                break;
            case 'overview':
                $this->tpl_name = 'stopbadbots_overview';
                $this->page_title = 'ACP_STOPBADBOTS_OVERVIEW';
                break;
            default:
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('main_module: Modo inválido: ' . $mode);
                }
                trigger_error($language->lang('INVALID_MODE'), E_USER_ERROR);
        }

        // Gera a URL da ação
        $this->u_action = append_sid("{$phpbb_root_path}adm/index.$phpEx", "i={$id}&mode={$mode}", true);
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_module: u_action definido como: ' . $this->u_action);
        }

        // Carrega o controlador
        try {
            $acp_controller = $phpbb_container->get('mundophpbb.stopbadbots.acp_controller');
            if (!method_exists($acp_controller, 'set_u_action')) {
                if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                    error_log('main_module: Método set_u_action não encontrado em acp_controller');
                }
                throw new \Exception('Método set_u_action não definido no controlador.');
            }
            $acp_controller->set_u_action($this->u_action);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_module: Controlador carregado com sucesso');
            }
            $acp_controller->handle($mode);
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_module: Controlador executado com sucesso para o modo: ' . $mode);
            }
        } catch (\Exception $e) {
            if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                error_log('main_module: Erro ao carregar/executar controlador: ' . $e->getMessage());
            }
            $template = $phpbb_container->get('template');
            $template->assign_vars([
                'S_ERROR' => true,
                'ERROR_MESSAGE' => $language->lang('DB_ERROR', htmlspecialchars($e->getMessage())),
            ]);
            $this->tpl_name = 'stopbadbots_' . $mode;
            $this->page_title = 'ACP_STOPBADBOTS_' . strtoupper($mode);
        }
    }
}