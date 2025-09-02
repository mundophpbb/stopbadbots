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
use phpbb\event\data;
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
    protected $legit_bots_verification = [
        'googlebot' => [
            'ua_substring' => 'Googlebot',
            'rdns_suffixes' => ['.googlebot.com', '.google.com'], // Verificação oficial recomendada pela Google
            'ip_ranges' => [ // IPv4 and IPv6 from https://developers.google.com/search/apis/ipranges/googlebot.json (updated as of August 2025)
                '192.178.4.0/27', '192.178.4.128/27', '192.178.4.160/27', '192.178.4.192/27', '192.178.4.32/27',
                '192.178.4.64/27', '192.178.4.96/27', '192.178.5.0/27', '192.178.6.0/27', '192.178.6.128/27',
                '192.178.6.160/27', '192.178.6.192/27', '192.178.6.224/27', '192.178.6.32/27', '192.178.6.64/27',
                '192.178.6.96/27', '192.178.7.0/27', '192.178.7.128/27', '192.178.7.160/27', '192.178.7.192/27',
                '192.178.7.224/27', '192.178.7.32/27', '192.178.7.64/27', '192.178.7.96/27', '34.100.182.96/28',
                '34.101.50.144/28', '34.118.254.0/28', '34.118.66.0/28', '34.126.178.96/28', '34.146.150.144/28',
                '34.147.110.144/28', '34.151.74.144/28', '34.152.50.64/28', '34.154.114.144/28', '34.155.98.32/28',
                '34.165.18.176/28', '34.175.160.64/28', '34.176.130.16/28', '34.22.85.0/27', '34.64.82.64/28',
                '34.65.242.112/28', '34.80.50.80/28', '34.88.194.0/28', '34.89.10.80/28', '34.89.198.80/28',
                '34.96.162.48/28', '35.247.243.240/28', '66.249.64.0/27', '66.249.64.128/27', '66.249.64.160/27',
                '66.249.64.192/27', '66.249.64.224/27', '66.249.64.32/27', '66.249.64.64/27', '66.249.64.96/27',
                '66.249.65.0/27', '66.249.65.128/27', '66.249.65.160/27', '66.249.65.192/27', '66.249.65.224/27',
                '66.249.65.32/27', '66.249.65.64/27', '66.249.65.96/27', '66.249.66.0/27', '66.249.66.128/27',
                '66.249.66.160/27', '66.249.66.192/27', '66.249.66.224/27', '66.249.66.32/27', '66.249.66.64/27',
                '66.249.66.96/27', '66.249.67.0/27', '66.249.67.32/27', '66.249.68.0/27', '66.249.68.128/27',
                '66.249.68.160/27', '66.249.68.192/27', '66.249.68.32/27', '66.249.68.64/27', '66.249.68.96/27',
                '66.249.69.0/27', '66.249.69.128/27', '66.249.69.160/27',
                '2001:4860:4801:10::/64', '2001:4860:4801:11::/64', '2001:4860:4801:12::/64', '2001:4860:4801:13::/64',
                '2001:4860:4801:14::/64', '2001:4860:4801:15::/64', '2001:4860:4801:16::/64', '2001:4860:4801:17::/64',
                '2001:4860:4801:18::/64', '2001:4860:4801:19::/64', '2001:4860:4801:1a::/64', '2001:4860:4801:1b::/64',
                '2001:4860:4801:1c::/64', '2001:4860:4801:1d::/64', '2001:4860:4801:1e::/64', '2001:4860:4801:1f::/64',
                '2001:4860:4801:20::/64', '2001:4860:4801:21::/64', '2001:4860:4801:22::/64', '2001:4860:4801:23::/64',
                '2001:4860:4801:24::/64', '2001:4860:4801:25::/64', '2001:4860:4801:26::/64', '2001:4860:4801:27::/64',
                '2001:4860:4801:28::/64', '2001:4860:4801:29::/64', '2001:4860:4801:2::/64', '2001:4860:4801:2a::/64',
                '2001:4860:4801:2b::/64', '2001:4860:4801:2c::/64', '2001:4860:4801:2d::/64', '2001:4860:4801:2e::/64',
                '2001:4860:4801:2f::/64', '2001:4860:4801:30::/64', '2001:4860:4801:31::/64', '2001:4860:4801:32::/64',
                '2001:4860:4801:33::/64', '2001:4860:4801:34::/64', '2001:4860:4801:35::/64', '2001:4860:4801:36::/64',
                '2001:4860:4801:37::/64', '2001:4860:4801:38::/64', '2001:4860:4801:39::/64', '2001:4860:4801:3a::/64',
                '2001:4860:4801:3b::/64', '2001:4860:4801:3c::/64', '2001:4860:4801:3d::/64', '2001:4860:4801:3e::/64',
                '2001:4860:4801:3f::/64', '2001:4860:4801:40::/64', '2001:4860:4801:41::/64', '2001:4860:4801:42::/64',
                '2001:4860:4801:43::/64', '2001:4860:4801:44::/64', '2001:4860:4801:45::/64', '2001:4860:4801:46::/64',
                '2001:4860:4801:47::/64', '2001:4860:4801:48::/64', '2001:4860:4801:49::/64', '2001:4860:4801:4a::/64',
                '2001:4860:4801:4b::/64', '2001:4860:4801:4c::/64', '2001:4860:4801:4d::/64',
            ],
        ],
        'adsbot' => [
            'ua_substring' => 'AdsBot-Google',
            'rdns_suffixes' => ['.googlebot.com', '.google.com'],
            'ip_ranges' => [ // Same as Googlebot
                '192.178.4.0/27', '192.178.4.128/27', '192.178.4.160/27', '192.178.4.192/27', '192.178.4.32/27',
                '192.178.4.64/27', '192.178.4.96/27', '192.178.5.0/27', '192.178.6.0/27', '192.178.6.128/27',
                '192.178.6.160/27', '192.178.6.192/27', '192.178.6.224/27', '192.178.6.32/27', '192.178.6.64/27',
                '192.178.6.96/27', '192.178.7.0/27', '192.178.7.128/27', '192.178.7.160/27', '192.178.7.192/27',
                '192.178.7.224/27', '192.178.7.32/27', '192.178.7.64/27', '192.178.7.96/27', '34.100.182.96/28',
                '34.101.50.144/28', '34.118.254.0/28', '34.118.66.0/28', '34.126.178.96/28', '34.146.150.144/28',
                '34.147.110.144/28', '34.151.74.144/28', '34.152.50.64/28', '34.154.114.144/28', '34.155.98.32/28',
                '34.165.18.176/28', '34.175.160.64/28', '34.176.130.16/28', '34.22.85.0/27', '34.64.82.64/28',
                '34.65.242.112/28', '34.80.50.80/28', '34.88.194.0/28', '34.89.10.80/28', '34.89.198.80/28',
                '34.96.162.48/28', '35.247.243.240/28', '66.249.64.0/27', '66.249.64.128/27', '66.249.64.160/27',
                '66.249.64.192/27', '66.249.64.224/27', '66.249.64.32/27', '66.249.64.64/27', '66.249.64.96/27',
                '66.249.65.0/27', '66.249.65.128/27', '66.249.65.160/27', '66.249.65.192/27', '66.249.65.224/27',
                '66.249.65.32/27', '66.249.65.64/27', '66.249.65.96/27', '66.249.66.0/27', '66.249.66.128/27',
                '66.249.66.160/27', '66.249.66.192/27', '66.249.66.224/27', '66.249.66.32/27', '66.249.66.64/27',
                '66.249.66.96/27', '66.249.67.0/27', '66.249.67.32/27', '66.249.68.0/27', '66.249.68.128/27',
                '66.249.68.160/27', '66.249.68.192/27', '66.249.68.32/27', '66.249.68.64/27', '66.249.68.96/27',
                '66.249.69.0/27', '66.249.69.128/27', '66.249.69.160/27',
                '2001:4860:4801:10::/64', '2001:4860:4801:11::/64', '2001:4860:4801:12::/64', '2001:4860:4801:13::/64',
                '2001:4860:4801:14::/64', '2001:4860:4801:15::/64', '2001:4860:4801:16::/64', '2001:4860:4801:17::/64',
                '2001:4860:4801:18::/64', '2001:4860:4801:19::/64', '2001:4860:4801:1a::/64', '2001:4860:4801:1b::/64',
                '2001:4860:4801:1c::/64', '2001:4860:4801:1d::/64', '2001:4860:4801:1e::/64', '2001:4860:4801:1f::/64',
                '2001:4860:4801:20::/64', '2001:4860:4801:21::/64', '2001:4860:4801:22::/64', '2001:4860:4801:23::/64',
                '2001:4860:4801:24::/64', '2001:4860:4801:25::/64', '2001:4860:4801:26::/64', '2001:4860:4801:27::/64',
                '2001:4860:4801:28::/64', '2001:4860:4801:29::/64', '2001:4860:4801:2::/64', '2001:4860:4801:2a::/64',
                '2001:4860:4801:2b::/64', '2001:4860:4801:2c::/64', '2001:4860:4801:2d::/64', '2001:4860:4801:2e::/64',
                '2001:4860:4801:2f::/64', '2001:4860:4801:30::/64', '2001:4860:4801:31::/64', '2001:4860:4801:32::/64',
                '2001:4860:4801:33::/64', '2001:4860:4801:34::/64', '2001:4860:4801:35::/64', '2001:4860:4801:36::/64',
                '2001:4860:4801:37::/64', '2001:4860:4801:38::/64', '2001:4860:4801:39::/64', '2001:4860:4801:3a::/64',
                '2001:4860:4801:3b::/64', '2001:4860:4801:3c::/64', '2001:4860:4801:3d::/64', '2001:4860:4801:3e::/64',
                '2001:4860:4801:3f::/64', '2001:4860:4801:40::/64', '2001:4860:4801:41::/64', '2001:4860:4801:42::/64',
                '2001:4860:4801:43::/64', '2001:4860:4801:44::/64', '2001:4860:4801:45::/64', '2001:4860:4801:46::/64',
                '2001:4860:4801:47::/64', '2001:4860:4801:48::/64', '2001:4860:4801:49::/64', '2001:4860:4801:4a::/64',
                '2001:4860:4801:4b::/64', '2001:4860:4801:4c::/64', '2001:4860:4801:4d::/64',
            ],
        ],
        'mediapartners' => [
            'ua_substring' => 'Mediapartners-Google',
            'rdns_suffixes' => ['.googlebot.com', '.google.com'],
            'ip_ranges' => [ // Same as Googlebot
                '192.178.4.0/27', '192.178.4.128/27', '192.178.4.160/27', '192.178.4.192/27', '192.178.4.32/27',
                '192.178.4.64/27', '192.178.4.96/27', '192.178.5.0/27', '192.178.6.0/27', '192.178.6.128/27',
                '192.178.6.160/27', '192.178.6.192/27', '192.178.6.224/27', '192.178.6.32/27', '192.178.6.64/27',
                '192.178.6.96/27', '192.178.7.0/27', '192.178.7.128/27', '192.178.7.160/27', '192.178.7.192/27',
                '192.178.7.224/27', '192.178.7.32/27', '192.178.7.64/27', '192.178.7.96/27', '34.100.182.96/28',
                '34.101.50.144/28', '34.118.254.0/28', '34.118.66.0/28', '34.126.178.96/28', '34.146.150.144/28',
                '34.147.110.144/28', '34.151.74.144/28', '34.152.50.64/28', '34.154.114.144/28', '34.155.98.32/28',
                '34.165.18.176/28', '34.175.160.64/28', '34.176.130.16/28', '34.22.85.0/27', '34.64.82.64/28',
                '34.65.242.112/28', '34.80.50.80/28', '34.88.194.0/28', '34.89.10.80/28', '34.89.198.80/28',
                '34.96.162.48/28', '35.247.243.240/28', '66.249.64.0/27', '66.249.64.128/27', '66.249.64.160/27',
                '66.249.64.192/27', '66.249.64.224/27', '66.249.64.32/27', '66.249.64.64/27', '66.249.64.96/27',
                '66.249.65.0/27', '66.249.65.128/27', '66.249.65.160/27', '66.249.65.192/27', '66.249.65.224/27',
                '66.249.65.32/27', '66.249.65.64/27', '66.249.65.96/27', '66.249.66.0/27', '66.249.66.128/27',
                '66.249.66.160/27', '66.249.66.192/27', '66.249.66.224/27', '66.249.66.32/27', '66.249.66.64/27',
                '66.249.66.96/27', '66.249.67.0/27', '66.249.67.32/27', '66.249.68.0/27', '66.249.68.128/27',
                '66.249.68.160/27', '66.249.68.192/27', '66.249.68.32/27', '66.249.68.64/27', '66.249.68.96/27',
                '66.249.69.0/27', '66.249.69.128/27', '66.249.69.160/27',
                '2001:4860:4801:10::/64', '2001:4860:4801:11::/64', '2001:4860:4801:12::/64', '2001:4860:4801:13::/64',
                '2001:4860:4801:14::/64', '2001:4860:4801:15::/64', '2001:4860:4801:16::/64', '2001:4860:4801:17::/64',
                '2001:4860:4801:18::/64', '2001:4860:4801:19::/64', '2001:4860:4801:1a::/64', '2001:4860:4801:1b::/64',
                '2001:4860:4801:1c::/64', '2001:4860:4801:1d::/64', '2001:4860:4801:1e::/64', '2001:4860:4801:1f::/64',
                '2001:4860:4801:20::/64', '2001:4860:4801:21::/64', '2001:4860:4801:22::/64', '2001:4860:4801:23::/64',
                '2001:4860:4801:24::/64', '2001:4860:4801:25::/64', '2001:4860:4801:26::/64', '2001:4860:4801:27::/64',
                '2001:4860:4801:28::/64', '2001:4860:4801:29::/64', '2001:4860:4801:2::/64', '2001:4860:4801:2a::/64',
                '2001:4860:4801:2b::/64', '2001:4860:4801:2c::/64', '2001:4860:4801:2d::/64', '2001:4860:4801:2e::/64',
                '2001:4860:4801:2f::/64', '2001:4860:4801:30::/64', '2001:4860:4801:31::/64', '2001:4860:4801:32::/64',
                '2001:4860:4801:33::/64', '2001:4860:4801:34::/64', '2001:4860:4801:35::/64', '2001:4860:4801:36::/64',
                '2001:4860:4801:37::/64', '2001:4860:4801:38::/64', '2001:4860:4801:39::/64', '2001:4860:4801:3a::/64',
                '2001:4860:4801:3b::/64', '2001:4860:4801:3c::/64', '2001:4860:4801:3d::/64', '2001:4860:4801:3e::/64',
                '2001:4860:4801:3f::/64', '2001:4860:4801:40::/64', '2001:4860:4801:41::/64', '2001:4860:4801:42::/64',
                '2001:4860:4801:43::/64', '2001:4860:4801:44::/64', '2001:4860:4801:45::/64', '2001:4860:4801:46::/64',
                '2001:4860:4801:47::/64', '2001:4860:4801:48::/64', '2001:4860:4801:49::/64', '2001:4860:4801:4a::/64',
                '2001:4860:4801:4b::/64', '2001:4860:4801:4c::/64', '2001:4860:4801:4d::/64',
            ],
        ],
        'feedfetcher' => [
            'ua_substring' => 'FeedFetcher-Google',
            'rdns_suffixes' => ['.googlebot.com', '.google.com'],
            'ip_ranges' => [ // Same as Googlebot
                '192.178.4.0/27', '192.178.4.128/27', '192.178.4.160/27', '192.178.4.192/27', '192.178.4.32/27',
                '192.178.4.64/27', '192.178.4.96/27', '192.178.5.0/27', '192.178.6.0/27', '192.178.6.128/27',
                '192.178.6.160/27', '192.178.6.192/27', '192.178.6.224/27', '192.178.6.32/27', '192.178.6.64/27',
                '192.178.6.96/27', '192.178.7.0/27', '192.178.7.128/27', '192.178.7.160/27', '192.178.7.192/27',
                '192.178.7.224/27', '192.178.7.32/27', '192.178.7.64/27', '192.178.7.96/27', '34.100.182.96/28',
                '34.101.50.144/28', '34.118.254.0/28', '34.118.66.0/28', '34.126.178.96/28', '34.146.150.144/28',
                '34.147.110.144/28', '34.151.74.144/28', '34.152.50.64/28', '34.154.114.144/28', '34.155.98.32/28',
                '34.165.18.176/28', '34.175.160.64/28', '34.176.130.16/28', '34.22.85.0/27', '34.64.82.64/28',
                '34.65.242.112/28', '34.80.50.80/28', '34.88.194.0/28', '34.89.10.80/28', '34.89.198.80/28',
                '34.96.162.48/28', '35.247.243.240/28', '66.249.64.0/27', '66.249.64.128/27', '66.249.64.160/27',
                '66.249.64.192/27', '66.249.64.224/27', '66.249.64.32/27', '66.249.64.64/27', '66.249.64.96/27',
                '66.249.65.0/27', '66.249.65.128/27', '66.249.65.160/27', '66.249.65.192/27', '66.249.65.224/27',
                '66.249.65.32/27', '66.249.65.64/27', '66.249.65.96/27', '66.249.66.0/27', '66.249.66.128/27',
                '66.249.66.160/27', '66.249.66.192/27', '66.249.66.224/27', '66.249.66.32/27', '66.249.66.64/27',
                '66.249.66.96/27', '66.249.67.0/27', '66.249.67.32/27', '66.249.68.0/27', '66.249.68.128/27',
                '66.249.68.160/27', '66.249.68.192/27', '66.249.68.32/27', '66.249.68.64/27', '66.249.68.96/27',
                '66.249.69.0/27', '66.249.69.128/27', '66.249.69.160/27',
                '2001:4860:4801:10::/64', '2001:4860:4801:11::/64', '2001:4860:4801:12::/64', '2001:4860:4801:13::/64',
                '2001:4860:4801:14::/64', '2001:4860:4801:15::/64', '2001:4860:4801:16::/64', '2001:4860:4801:17::/64',
                '2001:4860:4801:18::/64', '2001:4860:4801:19::/64', '2001:4860:4801:1a::/64', '2001:4860:4801:1b::/64',
                '2001:4860:4801:1c::/64', '2001:4860:4801:1d::/64', '2001:4860:4801:1e::/64', '2001:4860:4801:1f::/64',
                '2001:4860:4801:20::/64', '2001:4860:4801:21::/64', '2001:4860:4801:22::/64', '2001:4860:4801:23::/64',
                '2001:4860:4801:24::/64', '2001:4860:4801:25::/64', '2001:4860:4801:26::/64', '2001:4860:4801:27::/64',
                '2001:4860:4801:28::/64', '2001:4860:4801:29::/64', '2001:4860:4801:2::/64', '2001:4860:4801:2a::/64',
                '2001:4860:4801:2b::/64', '2001:4860:4801:2c::/64', '2001:4860:4801:2d::/64', '2001:4860:4801:2e::/64',
                '2001:4860:4801:2f::/64', '2001:4860:4801:30::/64', '2001:4860:4801:31::/64', '2001:4860:4801:32::/64',
                '2001:4860:4801:33::/64', '2001:4860:4801:34::/64', '2001:4860:4801:35::/64', '2001:4860:4801:36::/64',
                '2001:4860:4801:37::/64', '2001:4860:4801:38::/64', '2001:4860:4801:39::/64', '2001:4860:4801:3a::/64',
                '2001:4860:4801:3b::/64', '2001:4860:4801:3c::/64', '2001:4860:4801:3d::/64', '2001:4860:4801:3e::/64',
                '2001:4860:4801:3f::/64', '2001:4860:4801:40::/64', '2001:4860:4801:41::/64', '2001:4860:4801:42::/64',
                '2001:4860:4801:43::/64', '2001:4860:4801:44::/64', '2001:4860:4801:45::/64', '2001:4860:4801:46::/64',
                '2001:4860:4801:47::/64', '2001:4860:4801:48::/64', '2001:4860:4801:49::/64', '2001:4860:4801:4a::/64',
                '2001:4860:4801:4b::/64', '2001:4860:4801:4c::/64', '2001:4860:4801:4d::/64',
            ],
        ],
        'bingbot' => [
            'ua_substring' => 'bingbot',
            'rdns_suffixes' => ['.search.msn.com'],
            'ip_ranges' => [ // From https://www.bing.com/toolbox/bingbot.json (updated as of August 2025)
                '157.55.39.0/24', '207.46.13.0/24', '40.77.167.0/24', '13.66.139.0/24', '13.66.144.0/24', '52.167.144.0/24',
                '13.67.10.16/28', '13.69.66.240/28', '13.71.172.224/28', '139.217.52.0/28', '191.233.204.224/28',
                '20.36.108.32/28', '20.43.120.16/28', '40.79.131.208/28', '40.79.186.176/28', '52.231.148.0/28',
                '20.79.107.240/28', '51.105.67.0/28', '20.125.163.80/28', '40.77.188.0/22', '65.55.210.0/24',
                '199.30.24.0/23', '40.77.202.0/24', '40.77.139.0/25', '20.74.197.0/28', '20.15.133.160/27',
                '40.77.177.0/24', '40.77.178.0/23',
            ],
        ],
        'yandexbot' => [
            'ua_substring' => 'YandexBot',
            'rdns_suffixes' => ['.yandex.ru', '.yandex.net', '.yandex.com'],
            'ip_ranges' => [ // From https://yandex.com/ips (updated as of August 2025)
                '5.45.192.0/18', '5.255.192.0/18', '37.9.64.0/18', '37.140.128.0/18', '77.88.0.0/18', '84.252.160.0/19',
                '87.250.224.0/19', '90.156.176.0/20', '92.255.112.0/20', '93.158.128.0/18', '95.108.128.0/17',
                '100.43.64.0/19', '141.8.128.0/18', '178.154.128.0/18', '185.32.187.0/24', '199.21.96.0/22',
                '199.36.240.0/22', '213.180.192.0/19', '2a02:6b8::/29',
            ],
        ],
        'duckduckbot' => [
            'ua_substring' => 'DuckDuckBot',
            'rdns_suffixes' => ['.crawl.duckduckgo.com'],
            'ip_ranges' => [ // From https://help.duckduckgo.com/duckduckgo-help-pages/results/duckduckbot/ (updated as of September 2025)
                '104.43.54.127/32', '104.43.55.116/32', '104.43.55.117/32', '104.43.55.166/32', '104.43.55.167/32',
                '108.141.83.74/32', '13.89.106.77/32', '172.169.17.165/32', '191.233.3.197/32', '191.233.3.202/32',
                '191.234.216.178/32', '191.234.216.4/32', '191.235.201.214/32', '191.235.202.38/32', '191.235.202.48/32',
                '20.113.14.159/32', '20.113.3.121/32', '20.12.141.99/32', '20.185.79.15/32', '20.185.79.47/32',
                '20.191.44.119/32', '20.191.44.16/32', '20.191.44.22/32', '20.191.44.234/32', '20.191.45.212/32',
                '20.193.12.126/32', '20.193.24.10/32', '20.193.24.251/32', '20.193.25.197/32', '20.193.27.215/32',
                '20.193.45.113/32', '20.195.108.47/32', '20.197.209.11/32', '20.197.209.27/32', '20.201.15.208/32',
                '20.204.240.172/32', '20.204.241.148/32', '20.204.242.101/32', '20.204.242.19/32', '20.204.243.55/32',
                '20.204.246.254/32', '20.204.246.81/32', '20.207.107.181/32', '20.207.72.11/32', '20.207.72.110/32',
                '20.207.72.113/32', '20.207.72.21/32', '20.207.97.190/32', '20.207.99.197/32', '20.219.43.246/32',
                '20.219.45.190/32', '20.219.45.67/32', '20.226.133.105/32', '20.232.184.230/32', '20.3.1.178/32',
                '20.40.133.240/32', '20.43.150.85/32', '20.43.150.93/32', '20.43.172.120/32', '20.44.222.1/32',
                '20.49.136.28/32', '20.50.168.91/32', '20.50.48.159/32', '20.50.48.192/32', '20.50.49.0/32',
                '20.50.49.237/32', '20.50.49.25/32', '20.50.49.40/32', '20.50.49.55/32', '20.50.50.118/32',
                '20.50.50.121/32', '20.50.50.123/32', '20.50.50.130/32', '20.50.50.134/32', '20.50.50.145/32',
                '20.50.50.146/32', '20.50.50.163/32', '20.50.50.46/32', '20.53.134.160/32', '20.53.78.106/32',
                '20.53.78.123/32', '20.53.78.138/32', '20.53.78.144/32', '20.53.78.236/32', '20.53.91.2/32',
                '20.53.92.211/32', '20.56.197.58/32', '20.56.197.63/32', '20.61.34.40/32', '20.62.224.44/32',
                '20.71.12.143/32', '20.72.242.93/32', '20.73.132.240/32', '20.73.202.147/32', '20.75.144.152/32',
                '20.79.226.26/32', '20.79.238.198/32',
            ],
        ],
        'ahrefsbot' => [
            'ua_substring' => 'AhrefsBot',
            'rdns_suffixes' => ['.ahrefs.com', '.ahrefs.net'],
            'ip_ranges' => [ // From https://api.ahrefs.com/v3/public/crawler-ip-ranges (updated as of September 2025)
                '5.39.1.224/27', '5.39.109.160/27', '15.235.27.0/24', '15.235.96.0/24', '15.235.98.0/24',
                '37.59.204.128/27', '51.68.247.192/27', '51.75.236.128/27', '51.89.129.0/24', '51.161.37.0/24',
                '51.161.65.0/24', '51.195.183.0/24', '51.195.215.0/24', '51.195.244.0/24', '51.222.95.0/24',
                '51.222.168.0/24', '51.222.253.0/26', '54.36.148.0/23', '54.37.118.64/27', '54.38.147.0/24',
                '54.39.0.0/24', '54.39.6.0/24', '54.39.89.0/24', '54.39.136.0/24', '54.39.203.0/24',
                '54.39.210.0/24', '92.222.104.192/27', '92.222.108.96/27', '94.23.188.192/27', '142.44.220.0/24',
                '142.44.225.0/24', '142.44.228.0/24', '142.44.233.0/24', '148.113.128.0/24', '148.113.130.0/24',
                '167.114.139.0/24', '168.100.149.0/24', '176.31.139.0/27', '198.244.168.0/24', '198.244.183.0/24',
                '198.244.186.193/32', '198.244.186.194/31', '198.244.186.196/30', '198.244.186.200/31',
                '198.244.186.202/32', '198.244.226.0/24', '198.244.240.0/24', '198.244.242.0/24',
                '202.8.40.0/22', '202.94.84.110/31', '202.94.84.112/31',
            ],
        ],
        'heritrix' => [
            'ua_substring' => 'heritrix',
            'rdns_suffixes' => ['.archive.org'],
            'ip_ranges' => [],
        ],
        'ibmresearch' => [
            'ua_substring' => 'IBM Research',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'iccrawler' => [
            'ua_substring' => 'ICCrawler',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'msnnewsblogs' => [
            'ua_substring' => 'MSN NewsBlogs',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'msnbot' => [
            'ua_substring' => 'msnbot',
            'rdns_suffixes' => ['.search.msn.com'],
            'ip_ranges' => [],
        ],
        'msnbotmedia' => [
            'ua_substring' => 'msnbot-media',
            'rdns_suffixes' => ['.search.msn.com'],
            'ip_ranges' => [],
        ],
        'majestic12' => [
            'ua_substring' => 'MJ12bot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'metager' => [
            'ua_substring' => 'MetagerBot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'ngsearch' => [
            'ua_substring' => 'NG-Search',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'nutch' => [
            'ua_substring' => 'Nutch',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'nutchcvs' => [
            'ua_substring' => 'NutchCVS',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'omniexplorer' => [
            'ua_substring' => 'OmniExplorer_Bot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'onlinelinkvalidator' => [
            'ua_substring' => 'online link validator',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'seocrawler' => [
            'ua_substring' => 'SEO Crawler',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'seosearch' => [
            'ua_substring' => 'SEOSearch',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'seekport' => [
            'ua_substring' => 'SeekportBot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'semrush' => [
            'ua_substring' => 'SemrushBot',
            'rdns_suffixes' => [],
            'ip_ranges' => ['85.208.96.0/22', '85.208.98.0/24', '185.191.171.0/24'],
        ],
        'sensis' => [
            'ua_substring' => 'Sensis-Web-Crawler',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'seoma' => [
            'ua_substring' => 'Seoma',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'snappy' => [
            'ua_substring' => 'Snappy',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'steeler' => [
            'ua_substring' => 'Steeler',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'synoo' => [
            'ua_substring' => 'SynooBot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'telekom' => [
            'ua_substring' => 'TelekomBot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'turnitinbot' => [
            'ua_substring' => 'TurnitinBot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'voyager' => [
            'ua_substring' => 'Voyager',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'w3sitesearch' => [
            'ua_substring' => 'W3SiteBot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'w3clinkcheck' => [
            'ua_substring' => 'W3C-checklink',
            'rdns_suffixes' => [],
            'ip_ranges' => ['128.30.52.0/24'],
        ],
        'w3cvalidator' => [
            'ua_substring' => 'W3C_Validator',
            'rdns_suffixes' => [],
            'ip_ranges' => ['128.30.52.0/24'],
        ],
        'wisenut' => [
            'ua_substring' => 'ZyBorg',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'yacy' => [
            'ua_substring' => 'yacybot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'yahoommcrawler' => [
            'ua_substring' => 'Yahoo-MMCrawler',
            'rdns_suffixes' => ['.crawl.yahoo.net'],
            'ip_ranges' => [],
        ],
        'slurp' => [
            'ua_substring' => 'Slurp',
            'rdns_suffixes' => ['.crawl.yahoo.net'],
            'ip_ranges' => [],
        ],
        'yahoo' => [
            'ua_substring' => 'Yahoo! Slurp',
            'rdns_suffixes' => ['.crawl.yahoo.net'],
            'ip_ranges' => [],
        ],
        'yahooseeker' => [
            'ua_substring' => 'YahooSeeker',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'ichiro' => [
            'ua_substring' => 'ichiro',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'psbot' => [
            'ua_substring' => 'psbot',
            'rdns_suffixes' => [],
            'ip_ranges' => [],
        ],
        'oaibot' => [
            'ua_substring' => 'OAI-SearchBot',
            'rdns_suffixes' => ['.openai.com'],
            'ip_ranges' => [ // From https://openai.com/searchbot.json (updated as of September 2025)
                '20.42.10.176/28', '172.203.190.128/28', '104.210.140.128/28', '51.8.102.0/24', '135.234.64.0/24',
                '172.182.195.48/28', '20.25.151.224/28', '20.171.53.224/28', '20.169.6.224/28', '172.182.193.80/28',
                '172.182.193.224/28', '172.182.194.32/28', '172.182.194.144/28', '172.182.213.192/28', '172.182.209.208/28',
                '172.182.224.0/28', '172.182.211.192/28', '20.169.7.48/28', '20.168.18.32/28', '20.171.123.64/28',
                '20.14.99.96/28',
            ],
        ],
        // Adicione mais bots se necessário, ex: 'baiduspider' com ua_substring, rdns_suffixes, ip_ranges
    ];
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
    }
    /**
     * Registra os eventos que o listener vai escutar
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup' => 'user_setup',
        ];
    }
    /**
     * Manipula o evento core.user_setup: carrega o idioma e bloqueia bots maliciosos
     *
     * @param data $event Dados do evento
     */
    public function user_setup(data $event)
    {
        error_log('lang_listener: Carregando idioma para evento core.user_setup');
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'mundophpbb/stopbadbots',
            'lang_set' => 'stopbadbots',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
        error_log('lang_listener: Idioma adicionado para mundophpbb/stopbadbots');
        $this->block_bad_bots($event);
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
        $ref_whitelist = $lists['ref_whitelist'] ?? [];
        // Verificação especial para bots legítimos
        foreach ($this->legit_bots_verification as $bot_key => $bot_data) {
            if (stripos($user_agent, $bot_data['ua_substring']) !== false) {
                $is_legit = false;
                // Verificar RDNS se disponível
                if (isset($bot_data['rdns_suffixes']) && $this->verify_rdns($ip, $bot_data['rdns_suffixes'])) {
                    $is_legit = true;
                }
                // Fallback para faixas de IP
                if (!$is_legit && isset($bot_data['ip_ranges'])) {
                    foreach ($bot_data['ip_ranges'] as $range) {
                        if ($this->ip_matches($ip, $range)) {
                            $is_legit = true;
                            break;
                        }
                    }
                }
                if (!$is_legit) {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('main_listener: Bloqueado como fake ' . ucfirst($bot_key) . ': UA=' . $user_agent . ', IP=' . $ip);
                    }
                    return 'Bloqueado como fake ' . ucfirst($bot_key) . ': ' . $user_agent;
                } else {
                    if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
                        error_log('main_listener: ' . ucfirst($bot_key) . ' legítimo detectado e permitido: IP=' . $ip);
                    }
                    return false; // Permitir acesso
                }
            }
        }
        // Prosseguir com verificações normais de blacklist (código original)
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
        if (!empty($referer)) {
            $parsed_referer = parse_url($referer);
            $referer_host = strtolower($parsed_referer['host'] ?? '');
            if (!in_array($referer_host, array_column($ref_whitelist, 'value'))) {
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
        }
        if (defined('DEBUG_STOPBADBOTS') && DEBUG_STOPBADBOTS) {
            error_log('main_listener: Nenhum bloqueio aplicado');
        }
        return false;
    }
    /**
     * Verifica se o IP do cliente corresponde a um padrão (IP único ou CIDR), suportando IPv4 e IPv6
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
        if (strpos($pattern, '/') === false) {
            return false;
        }
        list($base_ip, $mask_str) = explode('/', $pattern);
        $mask = (int) $mask_str;
        $is_ipv6_ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        $is_ipv6_base = filter_var($base_ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6);
        // Verificar mismatch de família de IP
        if ($is_ipv6_ip !== $is_ipv6_base) {
            return false;
        }
        if ($is_ipv6_ip) {
            // IPv6
            $ip_bin = @inet_pton($ip);
            $base_bin = @inet_pton($base_ip);
            if ($ip_bin === false || $base_bin === false) {
                return false;
            }
            $bytes = 16; // IPv6 tem 128 bits / 16 bytes
            $full_bytes = (int) ($mask / 8);
            $bitmask = str_repeat("\xFF", $full_bytes) . str_repeat("\x00", $bytes - $full_bytes);
            if ($mask % 8 !== 0) {
                $bitmask[$full_bytes] = chr(0xFF << (8 - ($mask % 8)));
            }
            return ($ip_bin & $bitmask) === ($base_bin & $bitmask);
        } else {
            // IPv4
            $ip_long = ip2long($ip);
            $base_long = ip2long($base_ip);
            if ($ip_long === false || $base_long === false) {
                return false;
            }
            $netmask = ~((1 << (32 - $mask)) - 1);
            return ($ip_long & $netmask) === ($base_long & $netmask);
        }
    }
    /**
     * Verifica reverse DNS (RDNS) para confirmar bots legítimos
     *
     * @param string $ip IP do cliente
     * @param array $suffixes Sufixos de hostname oficiais
     * @return bool
     */
    protected function verify_rdns($ip, $suffixes)
    {
        $hostname = @gethostbyaddr($ip);
        if (!$hostname) {
            return false;
        }
        $forward_ip = @gethostbyname($hostname);
        if ($forward_ip !== $ip) {
            return false; // Anti-spoofing
        }
        foreach ($suffixes as $suffix) {
            if (substr($hostname, -strlen($suffix)) === $suffix) {
                return true;
            }
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
            'ref_whitelist' => [],
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