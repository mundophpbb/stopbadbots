<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2024 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

namespace mundophpbb\stopbadbots\acp;

class acp_stopbadbots_info
{
    public function module()
    {
        return [
            'filename' => '\mundophpbb\stopbadbots\acp\main_module',
            'title' => 'ACP_STOPBADBOTS',
            'modes' => [
                'settings' => [
                    'title' => 'ACP_STOPBADBOTS_SETTINGS',
                    'auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                    'cat' => ['ACP_STOPBADBOTS']
                ],
                'lists' => [
                    'title' => 'ACP_STOPBADBOTS_LISTS',
                    'auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                    'cat' => ['ACP_STOPBADBOTS']
                ],
                'logs' => [
                    'title' => 'ACP_STOPBADBOTS_LOGS',
                    'auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                    'cat' => ['ACP_STOPBADBOTS']
                ],
                'overview' => [
                    'title' => 'ACP_STOPBADBOTS_OVERVIEW',
                    'auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                    'cat' => ['ACP_STOPBADBOTS']
                ],
                'restore' => [
                    'title' => 'ACP_STOPBADBOTS_RESTORE',
                    'auth' => 'ext_mundophpbb/stopbadbots && acl_a_board',
                    'cat' => ['ACP_STOPBADBOTS']
                ],
            ],
        ];
    }
}