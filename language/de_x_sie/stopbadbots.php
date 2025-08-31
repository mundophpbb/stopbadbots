<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2025 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

/**
 * Language file for stopbadbots extension (German)
 * This file contains the translations for the StopBadBots extension in German Language.
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    // General
    'ACP_STOPBADBOTS'                   		=> 'Stop Bad Bots',
    'ACP_STOPBADBOTS_TITLE'             		=> 'Stop Bad Bots',
    'ACP_STOPBADBOTS_SETTINGS'          		=> 'Einstellungen',
    'ACP_STOPBADBOTS_SETTINGS_EXPLAIN'  		=> 'Einstellungsoptionen zum Blockieren bösartiger Bots basierend auf User-Agent, IP-Adresse und Referrer.',
    'ACP_STOPBADBOTS_LISTS'             		=> 'Listen der blockierten Bots',
    'ACP_STOPBADBOTS_LISTS_EXPLAIN'     		=> 'Verwaltung von Black- und Whitelists für User-Agents, IPs und Referrer.',
    'ACP_STOPBADBOTS_LOGS'              		=> 'Protokolle der blockierten Bots',
    'ACP_STOPBADBOTS_RESTORE'           		=> 'Standardeinstellungen wiederherstellen',
    'ACP_STOPBADBOTS_RESTORE_EXPLAIN'   		=> 'Stellt die Standardeinstellungen für die Erweiterung "Stop Bad Bots" wieder her, löscht die Listentabelle und fügt die ursprünglichen Daten wieder ein.',
    'ACP_STOPBADBOTS_OVERVIEW'          		=> 'Übersicht',
    'ACP_STOPBADBOTS_OVERVIEW_EXPLAIN'  		=> 'Verwaltung aller Black- und Whitelists der Bots in einer vereinfachten Benutzeroberfläche.',
    'ACP_STOPBADBOTS_VERSION'           		=> 'Erweiterungsversion',

    // Settings
	'ACP_STOPBADBOTS_ENABLED'                   => 'Stop Bad Bots aktivieren',
    'ACP_STOPBADBOTS_ENABLED_EXPLAIN'           => 'Aktiviert oder deaktiviert den Schutz vor bösartigen Bots.',
    'ACP_STOPBADBOTS_CRON_ENABLED'              => 'Cron-Job aktivieren',
    'ACP_STOPBADBOTS_CRON_ENABLED_EXPLAIN'      => 'Aktiviert oder deaktiviert den Cron-Job für die Bereinigung der Protokolle und die Aktualisierungen der Statistik.',
    'ACP_STOPBADBOTS_CRON_INTERVAL'             => 'Ausführungsintervall des Cron-Jobs',
    'ACP_STOPBADBOTS_CRON_INTERVAL_EXPLAIN'     => 'Intervall in Sekunden zwischen den Cron-Jobs (mindestens 3600 Sekunden, entspricht 1 Stunde).',
    'ACP_STOPBADBOTS_CRON_INTERVAL_ERROR'       => 'Das Intervall des Cron-Jobs muss mindestens 3600 Sekunden (1 Stunde) betragen.',
    'ACP_STOPBADBOTS_LOG_RETENTION_DAYS'        => 'Tage der Protokollaufbewahrung',
    'ACP_STOPBADBOTS_LOG_RETENTION_DAYS_EXPLAIN'=> 'Anzahl der Tage, an denen Blockprotokolle aufbewahrt werden, bevor sie automatisch gelöscht werden (mindestens 1 Tag).',
    'ACP_STOPBADBOTS_USE_X_FORWARDED_FOR'       => 'X-Forwarded-For für IP-Identifizierung verwenden',
    'ACP_STOPBADBOTS_USE_X_FORWARDED_FOR_EXPLAIN'=> 'Diese Option nur aktivieren, wenn sich der Server hinter einem vertrauenswürdigen Proxy befindet, der den X-Forwarded-For-Header korrekt setzt. Andernfalls deaktiviert lassen, um REMOTE_ADDR zu verwenden und falsche Sperren zu vermeiden.',
    'ACP_STOPBADBOTS_STATISTICS'                => 'Stop Bad Bots Statistiken',
    'ACP_STOPBADBOTS_STATISTICS_EXPLAIN'        => 'Grafik mit der Anzahl der täglichen blockierten Bots nach Typ (User-Agent, IP, Referrer).',
    'ACP_STOPBADBOTS_DAILY_BLOCKS'              => 'Blockierte Bots pro Tag',
    'ACP_STOPBADBOTS_BLOCKS'                    => 'Anzahl der blockierten Bots',
    'ACP_STOPBADBOTS_RESET_DEFAULT'             => 'Standardeinstellungen wiederherstellen',
    'ACP_STOPBADBOTS_RESET_DEFAULT_SUCCESS'     => 'Einstellungen erfolgreich auf Standardwerte zurückgesetzt!',
    'ACP_STOPBADBOTS_SAVED'                     => 'Einstellungen erfolgreich gespeichert!',
    'ACP_STOPBADBOTS_CRON_RUN_SUCCESS'          => 'Cron-Job erfolgreich ausgeführt!',
    'ACP_STOPBADBOTS_LISTS_SAVED'               => 'Listen erfolgreich gespeichert!',
    
    // Block Lists
    'ACP_STOPBADBOTS_UA_LIST'           		=> 'User-Agent-Blacklist',
    'ACP_STOPBADBOTS_UA_LIST_EXPLAIN'   		=> 'Pro Zeile einen User-Agent eintragen, der blockiert werden soll (z.B. "_zbot" oder "BadBot"). Bei kommaseparierten Werten wird nur das erste Feld berücksichtigt.',
    'ACP_STOPBADBOTS_IP_LIST'           		=> 'IP-Blacklist',
    'ACP_STOPBADBOTS_IP_LIST_EXPLAIN'   		=> 'Pro Zeile eine IP-Adresse oder einen CIDR-Bereich eintragen, die bzw. der blockiert werden soll (z.B. "1.180.70.178" oder "192.168.1.0/24").',
    'ACP_STOPBADBOTS_REF_LIST'          		=> 'Referrer-Blacklist',
    'ACP_STOPBADBOTS_REF_LIST_EXPLAIN'  		=> 'Pro Zeile einen Referrer eintragen, der blockiert werden soll (z.B. "000Free.us" oder "example.com"). Das Protokoll wird ignoriert.',
    'ACP_STOPBADBOTS_UA_WHITELIST'      		=> 'User-Agent-Whitelist',
    'ACP_STOPBADBOTS_UA_WHITELIST_EXPLAIN' 		=> 'Pro Zeile einen User-Agent eintragen, der zugelassen werden soll (z.B. "Googlebot").',
    'ACP_STOPBADBOTS_IP_WHITELIST'      		=> 'IP-Whitelist',
    'ACP_STOPBADBOTS_IP_WHITELIST_EXPLAIN' 		=> 'Pro Zeile eine IP-Adresse oder einen CIDR-Bereich eintragen, die bzw. der zugelassen werden soll (z.B. "192.168.1.1" oder "192.168.1.0/24").',
    'ACP_STOPBADBOTS_REF_WHITELIST'     		=> 'Referrer-Whitelist',
    'ACP_STOPBADBOTS_REF_WHITELIST_EXPLAIN' 	=> 'Pro Zeile einen Referrer eintragen, der zugelassen werden soll (z.B. "example.com").',
    'ACP_STOPBADBOTS_UA_ADDED'          		=> 'User-Agent erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_IP_ADDED'          		=> 'IP-Adresse erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_REF_ADDED'         		=> 'Referrer erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_UA_WHITELIST_ADDED' 		=> 'User-Agent (Whitelist) erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_IP_WHITELIST_ADDED' 		=> 'IP-Adresse (Whitelist) erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_REF_WHITELIST_ADDED' 		=> 'Referrer (Whitelist) erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_LIST_UPDATED'      		=> 'Eintrag erfolgreich aktualisiert!',
    'ACP_STOPBADBOTS_LIST_DELETED'      		=> 'Eintrag erfolgreich entfernt!',
    'ACP_STOPBADBOTS_LISTS_DELETED'     		=> 'Ausgewählte Einträge erfolgreich entfernt!',
    'ACP_STOPBADBOTS_DUPLICATE_ENTRY'   		=> 'Doppelter Eintrag: %s existiert bereits in der Liste %s.',
    'ACP_STOPBADBOTS_LIST_UPLOADED'     		=> '%d Einträge wurden erfolgreich zur Liste hinzugefügt.',

    // Accessibility Descriptions for Forms
    'NEW_USER_AGENT_EXPLAIN'    => 'User-Agent eingeben, der zur Blacklist hinzugefügt werden soll.',
    'NEW_IP_ADDRESS_EXPLAIN'    => 'IP-Adresse oder CIDR-Bereich eingeben, die bzw. der zur Blacklist hinzugefügt werden soll.',
    'NEW_REFERER_EXPLAIN'       => 'Referrer eingeben, der zur Blacklist hinzugefügt werden soll.',
    'NEW_UA_WHITELIST_EXPLAIN'  => 'User-Agent eingeben, der zur Whitelist hinzugefügt werden soll.',
    'NEW_IP_WHITELIST_EXPLAIN'  => 'IP-Adresse oder CIDR-Bereich eingeben, die bzw. der zur Whitelist hinzugefügt werden soll.',
    'NEW_REF_WHITELIST_EXPLAIN' => 'Referrer eingeben, der zur Whitelist hinzugefügt werden soll.',
    'SEARCH_LIST_EXPLAIN'       => 'Begriff eingeben, nach dem in den Listen "User-Agent", "IP" oder "Referrer" gesucht werden soll.',

    // Messages for Empty Lists
    'NO_ENTRIES'        => 'Keine Einträge gefunden.',
    'NO_UA_WHITELIST'   => 'Kein User-Agent in der Whitelist.',
    'NO_IP_WHITELIST'   => 'Keine IP-Adresse in der Whitelist.',
    'NO_REF_WHITELIST'  => 'Kein Referrer in der Whitelist.',

    // Generic Actions
    'ADD'       => 'Hinzufügen',
    'S_SUBMIT'    => 'Absenden',
    'RESET'     => 'Zurücksetzen',

    // Upload and Import
    'UPLOAD_LIST_TYPE'                  => 'Listentyp',
    'UPLOAD_LIST_TYPE_EXPLAIN'          => 'Auswahl des Listentyps, für den die Datei importiert werden soll.',
    'UPLOAD_LIST_FILE'                  => 'Listendatei (.txt oder .csv)',
    'UPLOAD_LIST_FILE_EXPLAIN'          => 'Eine Textdatei (.txt oder .csv) auswählen, die pro Zeile einen Eintrag enthält (z.B. User-Agents, IPs oder Referrer). Bei CSV-Dateien wird nur das erste Feld verwendet.',
    'UPLOAD'                            => 'Hochladen',
    'UPLOADING'                         => 'Hochladen...',
    'IMPORT_FROM_TXT_FILES'             => 'Aus Standardlisten importieren (bots.txt, botsip.txt, botsref.txt)',
    'IMPORTING'                         => 'Importieren...',
    'ACP_STOPBADBOTS_DEFAULT_LISTS_IMPORTED' => 'Standardlisten erfolgreich importiert: %d Einträge hinzugefügt.',
    'STOPBADBOTS_DEFAULT_LIST_IMPORTED' => 'Standardliste importiert: %d Einträge zu %s hinzugefügt.',
    'STOPBADBOTS_IMPORT_FAILED'         => 'Import der Standardliste fehlgeschlagen: %s.',
    'ACP_STOPBADBOTS_IMPORT_FAILED'     => 'Fehler beim Importieren der Standardliste: %s.',
    'FILE_READ_ERROR'                   => 'Fehler beim Lesen der Datei %s. Bitte überprüfen, ob die Datei vorhanden ist und über Leserechte verfügt.',

    // Logs
    'ACP_STOPBADBOTS_BLOCKED'               => 'Zugriff verweigert: %s',
    'LOG_BOT_BLOCKED'                       => 'Bot blockiert: %s',
    'LOG_CLEARED'                           => 'Protokolle erfolgreich gelöscht.',
    'SEARCH_LOGS'                           => 'Protokolle durchsuchen',
    'SEARCH_LOGS_EXPLAIN'                   => 'Protokolle nach User-Agent, IP, Referrer oder Grund durchsuchen.',
    'SEARCH_LOGS_PLACEHOLDER'               => 'User-Agent, IP-Adresse, Referrer oder Grund (Erläuterung) eingeben',
    'DATE_FROM'                             => 'Startdatum',
    'DATE_TO'                               => 'Enddatum',
    'EXPORT_LOGS'                           => 'Protokolle als CSV exportieren',
    'NO_LOG_ENTRIES'                        => 'Keine Protokolleinträge gefunden.',
    'LOG_TIME'                              => 'Datum/Uhrzeit',
    'USER_AGENT'                            => 'User-Agent',
    'IP'                                    => 'IP-Adresse',
    'REFERER'                           	=> 'Referrer',
    'REASON'                                => 'Erläuterung',
    'ALL'                               	=> 'Alle Daten',

    // Statistics and Cron
    'STATISTICS_AND_CRON'       => 'Statistiken und Cron-Job',
    'CRON_LAST_RUN'             => 'Letzte Ausführung des Cron-Jobs',
    'DAILY_BLOCKS'              => 'Gesamtzahl der blockierten Bots pro Tag',
    'RUN_CRON'                  => 'Cron-Job jetzt ausführen',
    'CONFIRM_RUN_CRON'          => 'Den Cron-Job jetzt wirklich starten?',
    'NEVER'                     => 'Nie',
    'STOPBADBOTS_CRON_DISABLED' => 'Cron-Job ist deaktiviert.',
    'STOPBADBOTS_LOG_CLEANED'   => 'Alte Protokolle erfolgreich bereinigt: %d Einträge entfernt.',
    'STOPBADBOTS_LOG_ERROR'     => 'Cron-Job-Fehler: %s',

    // Search and Filters
    'SEARCH_LIST'               => 'In Listen suchen',
    'SEARCH_LIST_PLACEHOLDER'   => 'User-Agent, IP oder Referrer eingeben',
    'CLEAR_SEARCH'              => 'Suche löschen',
    'SEARCH_RESULTS'            => 'Suchergebnisse',
    'NO_SEARCH_PERFORMED'       => 'Es wurde keine Suche durchgeführt.' ,
    'NO_SEARCH_RESULTS'         => 'Keine Ergebnisse für den Suchbegriff gefunden.',
    'CHOOSE_FILE'               => 'Datei auswählen',
    'FILTER_LIST'               => 'Liste filtern',
    'FILTER_UA_LIST'            => 'Liste der blockierten User-Agents filtern',
    'FILTER_IP_LIST'            => 'Liste der blockierten IP-Adressen filtern',
    'FILTER_REF_LIST'           => 'Liste der blockierten Referrer filtern',
    'FILTER_UA_WHITELIST'       => 'Liste der zugelassenen User-Agents filtern',
    'FILTER_IP_WHITELIST'       => 'Liste der zugelassenen IP-Adressen filtern',
    'FILTER_REF_WHITELIST'      => 'Liste der zugelassenen Referrer filtern',
    'FILTER_BY_LIST_TYPE'       => 'Nach Listentyp filtern',

    // List Actions
    'ADD_USER_AGENT' 			=> 'User-Agent hinzufügen',
    'ADD_IP_ADDRESS' 			=> 'IP-Adresse hinzufügen',
    'ADD_REFERER' 				=> 'Referrer hinzufügen',
    'NEW_USER_AGENT' 			=> 'Neuer User-Agent',
    'NEW_IP_ADDRESS' 			=> 'Neue IP-Adresse',
    'NEW_REFERER'               => 'Neuer Referrer',
    'NEW_UA_WHITELIST'          => 'Neuer User-Agent (Whitelist)',
    'NEW_IP_WHITELIST'          => 'Neue IP-Adresse (Whitelist)',
    'NEW_REF_WHITELIST'         => 'Neuer Referrer (Whitelist)',
    'DELETE_SELECTED'           => 'Ausgewählte löschen',
    'SELECT'                    => 'Auswählen',
    'SELECT_ALL'                => 'Alle auswählen',
    'NO_ENTRIES_SELECTED'       => 'Es wurden keine Einträge zum Löschen ausgewählt.',
    'NO_USER_AGENTS'            => 'Kein User-Agent in der Liste.',
    'NO_IP_ADDRESSES'           => 'Keine IP-Adresse in der Liste.',
    'NO_REFERERS'               => 'Kein Referrer in der Liste.',
    'USER_AGENT_LIST'           => 'Liste der User-Agents',
    'IP_ADDRESS_LIST'           => 'Liste der IP-Adressen',
    'REFERER_LIST'              => 'Liste der Referrer',
    'BLOCK_LOG'                 => 'Protokoll der blockierten Bots',
    'OPTIONS'                   => 'Optionen',
    'ADDED_TIME'                => 'Hinzugefügt am',
    'LIST_TYPE'                 => 'Listentyp',

    // Confirmations
    'CONFIRM_RESET_DEFAULT'         => 'Sollen die Standardeinstellungen wirklich wiederherstellt werden? Dadurch werden alle aktuellen Listen gelöscht und die ursprünglichen Daten wieder eingefügt.',
    'CONFIRM_CLEAR_LOG'             => 'Protokolle löschen',
    'CONFIRM_CLEAR_LOG_MESSAGE'     => 'Sollen wirklich alle Protokolle der blockierten Bots gelöscht werden?',
    'CONFIRM_DELETE_LIST'           => 'Soll dieser Eintrag wirklich gelöscht werden?',
    'CONFIRM_DELETE_SELECTED_LISTS' => 'Sollen die ausgewählten Einträge wirklich gelöscht werden?',

    // Errors
    'FORM_INVALID'                      => 'Ungültiges Formular. Bitte erneut versuchen.',
    'INVALID_IP'                        => 'Ungültiges IP- oder CIDR-Format.',
    'INVALID_REFERER'                   => 'Ungültiges Referrer-Format. Es muss eine gültige Domain oder URL sein.',
    'DB_ERROR'                          => 'Datenbankfehler: %s',
    'LOG_ERROR'                         => 'Fehler der StopBadBots-Erweiterung: %s',
    'TABLE_NOT_FOUND'                   => 'Tabelle nicht gefunden: %s',
    'INVALID_MODE'                      => 'Ungültiger Modus angegeben.',
    'FILE_NOT_FOUND'                    => 'Die Datei %s wurde nicht gefunden.',
    'FILE_NOT_READABLE'                 => 'Die Datei %s ist nicht lesbar.',
    'FILE_READ_ERROR'                   => 'Fehler beim Lesen der Datei %s. Bitte überprüfen, ob die Datei vorhanden ist und über Leserechte verfügt.',
    'INVALID_LIST_TYPE'                 => 'Ungültiger Listentyp ausgewählt.',
    'NO_FILE_UPLOADED'                  => 'Es wurde keine Datei hochgeladen.',
    'INVALID_LIST_TYPE_MISMATCH'        => 'Der Dateiinhalt stimmt nicht mit dem ausgewählten Listentyp überein. Bitte das Dateiformat überprüfen.',
    'INVALID_FILE_TYPE'                 => 'Ungültiger Dateityp. Es sind nur Textdateien (.txt oder .csv) zulässig.',
    'UPLOAD_ERROR'                      => 'Unerwarteter Fehler beim Verarbeiten des Datei-Uploads. Bitte erneut versuchen.',
    'FILEINFO_EXTENSION_NOT_ENABLED'    => 'Die PHP-Erweiterung fileinfo ist nicht aktiviert. Bitte die Konfiguration in php.ini überprüfen.',
    'INVALID_FILE_PATH'                 => 'Ungültiger oder nicht zugänglicher Dateipfad. Bitte die Upload-Einstellungen in php.ini überprüfen.',
    'LOAD_MORE'                         => 'Mehr laden',
    'LOAD_MORE_ERROR'                   => 'Fehler beim Laden weiterer Einträge. Bitte später erneut versuchen.',
    'NO_ENTRY_FOUND'                    => 'Kein Eintrag in der angegebenen Liste gefunden.',
    'INVALID_SESSION'                   => 'Ungültige Sitzung. Bitte erneut anmelden.',
    'IMPORT_FROM_TXT_FILES_FAILED'      => 'Import von Einträgen aus Textdateien fehlgeschlagen.',
    'TOTAL_LOGS'                        => 'Gesamtzahl der Protokolle',
    'PAGE_NUMBER'                       => 'Seite',
    'ACP_STOPBADBOTS_LIST_UPLOADED_WITH_TYPE' => 'StopBadBots-Liste erfolgreich hochgeladen',
    'EXPORT_LOGS'                       => 'Protokolle als CSV exportieren',
    'CONFIRM_EXPORT_LOGS_MESSAGE'       => 'Sollen die Protokolle in eine CSV-Datei exportiert werden?',
    'ACP_STOPBADBOTS_DEBUG_ENABLED'     => 'Debug-Modus aktivieren',
    'ACP_STOPBADBOTS_DEBUG_ENABLED_EXPLAIN' => 'Aktiviert detaillierte Protokollierung für die Fehlerbehebung der StopBadBots-Erweiterung. Diese Option ist nur zu Test- oder Diagnosezwecken zu verwenden, da sie das Protokollvolumen erheblich erhöhen kann.',
]);