<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2025 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

/**
 * Language file for stopbadbots extension (German - Formal Honorifics)
 * Dieses Dokument enthält die Übersetzungen für die StopBadBots-Erweiterung in deutscher Sprache (formell).
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    // General
    'ACP_STOPBADBOTS'                       => 'Böse Bots stoppen',
    'ACP_STOPBADBOTS_TITLE'                 => 'Böse Bots stoppen',
    'ACP_STOPBADBOTS_SETTINGS'              => 'Einstellungen',
    'ACP_STOPBADBOTS_SETTINGS_EXPLAIN'      => 'Konfigurieren Sie die Optionen, um böse Bots basierend auf User-Agent, IP-Adresse und Referer zu blockieren.',
    'ACP_STOPBADBOTS_LISTS'                 => 'Sperrlisten',
    'ACP_STOPBADBOTS_LISTS_EXPLAIN'         => 'Verwalten Sie Sperr- und Zulassungslisten für User-Agents, IPs und Referer.',
    'ACP_STOPBADBOTS_LOGS'                  => 'Sperr-Protokolle',
    'ACP_STOPBADBOTS_RESTORE'               => 'Standardeinstellungen wiederherstellen',
    'ACP_STOPBADBOTS_RESTORE_EXPLAIN'       => 'Stellt die Standardeinstellungen der Erweiterung "Böse Bots stoppen" wieder her, leert die Listen-Tabelle und fügt die anfänglichen Daten erneut ein.',
    'ACP_STOPBADBOTS_OVERVIEW'              => 'Übersicht',
    'ACP_STOPBADBOTS_OVERVIEW_EXPLAIN'      => 'Verwalten Sie alle Bot-Sperr- und Zulassungslisten in einer vereinfachten Oberfläche.',
    'ACP_STOPBADBOTS_VERSION'               => 'Erweiterungsversion',

    // Settings
    'ACP_STOPBADBOTS_ENABLED'                   => 'Böse Bots stoppen aktivieren',
    'ACP_STOPBADBOTS_ENABLED_EXPLAIN'           => 'Aktiviert oder deaktiviert den Schutz vor bösen Bots.',
    'ACP_STOPBADBOTS_CRON_ENABLED'              => 'Cron-Aufgabe aktivieren',
    'ACP_STOPBADBOTS_CRON_ENABLED_EXPLAIN'      => 'Aktiviert oder deaktiviert die Cron-Aufgabe zur Protokollbereinigung und Statistikaktualisierung.',
    'ACP_STOPBADBOTS_CRON_INTERVAL'             => 'Cron-Ausführungsintervall',
    'ACP_STOPBADBOTS_CRON_INTERVAL_EXPLAIN'     => 'Intervall in Sekunden zwischen den Ausführungen der Cron-Aufgabe (mindestens 3600 Sekunden, entspricht 1 Stunde).',
    'ACP_STOPBADBOTS_CRON_INTERVAL_ERROR'       => 'Das Cron-Intervall muss mindestens 3600 Sekunden (1 Stunde) betragen.',
    'ACP_STOPBADBOTS_LOG_RETENTION_DAYS'        => 'Tage zur Protokollaufbewahrung',
    'ACP_STOPBADBOTS_LOG_RETENTION_DAYS_EXPLAIN'=> 'Anzahl der Tage, an denen Sperr-Protokolle aufbewahrt werden, bevor sie automatisch gelöscht werden (mindestens 1 Tag).',
    'ACP_STOPBADBOTS_USE_X_FORWARDED_FOR'       => 'X-Forwarded-For zur IP-Identifizierung verwenden',
    'ACP_STOPBADBOTS_USE_X_FORWARDED_FOR_EXPLAIN'=> 'Aktivieren Sie diese Option nur, wenn sich Ihr Server hinter einem vertrauenswürdigen Proxy befindet, der den X-Forwarded-For-Header korrekt festlegt. Andernfalls lassen Sie die Option deaktiviert, um REMOTE_ADDR zu verwenden und ungerechtfertigte Sperren zu vermeiden.',
    'ACP_STOPBADBOTS_STATISTICS'                => 'Sperrstatistiken',
    'ACP_STOPBADBOTS_STATISTICS_EXPLAIN'        => 'Diagramm, das die Anzahl der täglichen Sperren nach Typ anzeigt (User-Agent, IP, Referer).',
    'ACP_STOPBADBOTS_DAILY_BLOCKS'              => 'Tägliche Sperren',
    'ACP_STOPBADBOTS_BLOCKS'                    => 'Anzahl der Sperren',
    'ACP_STOPBADBOTS_RESET_DEFAULT'             => 'Standardeinstellungen wiederherstellen',
    'ACP_STOPBADBOTS_RESET_DEFAULT_SUCCESS'     => 'Einstellungen erfolgreich auf die Standardwerte zurückgesetzt!',
    'ACP_STOPBADBOTS_SAVED'                     => 'Einstellungen erfolgreich gespeichert!',
    'ACP_STOPBADBOTS_CRON_RUN_SUCCESS'          => 'Cron-Aufgabe erfolgreich ausgeführt!',
    'ACP_STOPBADBOTS_LISTS_SAVED'               => 'Listen erfolgreich gespeichert!',
    
    // Block Lists
    'ACP_STOPBADBOTS_UA_LIST'               => 'User-Agent-Sperrliste',
    'ACP_STOPBADBOTS_UA_LIST_EXPLAIN'       => 'Geben Sie einen User-Agent pro Zeile ein, um ihn zu blockieren (z. B. "_zbot" oder "BadBot"). Bei durch Kommas getrennten Werten wird nur das erste Feld verwendet.',
    'ACP_STOPBADBOTS_IP_LIST'               => 'IP-Sperrliste',
    'ACP_STOPBADBOTS_IP_LIST_EXPLAIN'       => 'Geben Sie eine IP-Adresse oder einen CIDR-Bereich pro Zeile ein, um ihn zu blockieren (z. B. "1.180.70.178" oder "192.168.1.0/24").',
    'ACP_STOPBADBOTS_REF_LIST'              => 'Referer-Sperrliste',
    'ACP_STOPBADBOTS_REF_LIST_EXPLAIN'      => 'Geben Sie einen Referer pro Zeile ein, um ihn zu blockieren (z. B. "000Free.us" oder "example.com"). Das Protokoll wird ignoriert.',
    'ACP_STOPBADBOTS_UA_WHITELIST'          => 'User-Agent-Zulassungsliste',
    'ACP_STOPBADBOTS_UA_WHITELIST_EXPLAIN'  => 'Geben Sie einen User-Agent pro Zeile ein, um ihn zuzulassen (z. B. "Googlebot").',
    'ACP_STOPBADBOTS_IP_WHITELIST'          => 'IP-Zulassungsliste',
    'ACP_STOPBADBOTS_IP_WHITELIST_EXPLAIN'  => 'Geben Sie eine IP-Adresse oder einen CIDR-Bereich pro Zeile ein, um ihn zuzulassen (z. B. "192.168.1.1" oder "192.168.1.0/24").',
    'ACP_STOPBADBOTS_REF_WHITELIST'         => 'Referer-Zulassungsliste',
    'ACP_STOPBADBOTS_REF_WHITELIST_EXPLAIN' => 'Geben Sie einen Referer pro Zeile ein, um ihn zuzulassen (z. B. "example.com").',
    'ACP_STOPBADBOTS_UA_ADDED'              => 'User-Agent erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_IP_ADDED'              => 'IP-Adresse erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_REF_ADDED'             => 'Referer erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_UA_WHITELIST_ADDED'    => 'User-Agent (Zulassungsliste) erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_IP_WHITELIST_ADDED'    => 'IP-Adresse (Zulassungsliste) erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_REF_WHITELIST_ADDED'   => 'Referer (Zulassungsliste) erfolgreich hinzugefügt!',
    'ACP_STOPBADBOTS_LIST_UPDATED'          => 'Eintrag erfolgreich aktualisiert!',
    'ACP_STOPBADBOTS_LIST_DELETED'          => 'Eintrag erfolgreich entfernt!',
    'ACP_STOPBADBOTS_LISTS_DELETED'         => 'Ausgewählte Einträge erfolgreich entfernt!',
    'ACP_STOPBADBOTS_DUPLICATE_ENTRY'       => 'Doppelter Eintrag: %s existiert bereits in der Liste %s.',
    'ACP_STOPBADBOTS_LIST_UPLOADED'         => '%d Einträge wurden erfolgreich zur Liste hinzugefügt.',

    // Accessibility Descriptions for Forms
    'NEW_USER_AGENT_EXPLAIN'        => 'Geben Sie einen User-Agent ein, um ihn zur Sperrliste hinzuzufügen.',
    'NEW_IP_ADDRESS_EXPLAIN'        => 'Geben Sie eine IP-Adresse oder einen CIDR-Bereich ein, um ihn zur Sperrliste hinzuzufügen.',
    'NEW_REFERER_EXPLAIN'           => 'Geben Sie einen Referer ein, um ihn zur Sperrliste hinzuzufügen.',
    'NEW_UA_WHITELIST_EXPLAIN'      => 'Geben Sie einen User-Agent ein, um ihn zur Zulassungsliste hinzuzufügen.',
    'NEW_IP_WHITELIST_EXPLAIN'      => 'Geben Sie eine IP-Adresse oder einen CIDR-Bereich ein, um ihn zur Zulassungsliste hinzuzufügen.',
    'NEW_REF_WHITELIST_EXPLAIN'     => 'Geben Sie einen Referer ein, um ihn zur Zulassungsliste hinzuzufügen.',
    'SEARCH_LIST_EXPLAIN'           => 'Geben Sie einen Begriff ein, um in den Listen für User-Agents, IPs oder Referer zu suchen.',

    // Messages for Empty Lists
    'NO_ENTRIES'                    => 'Keine Einträge gefunden.',
    'NO_UA_WHITELIST'               => 'Keine User-Agents auf der Zulassungsliste.',
    'NO_IP_WHITELIST'               => 'Keine IPs auf der Zulassungsliste.',
    'NO_REF_WHITELIST'              => 'Keine Referer auf der Zulassungsliste.',

    // Generic Actions
    'ADD'                           => 'Hinzufügen',
    'S_SUBMIT'                        => 'Absenden',
    'RESET'                         => 'Zurücksetzen',

    // Upload and Import
    'UPLOAD_LIST_TYPE'                  => 'Listentyp',
    'UPLOAD_LIST_TYPE_EXPLAIN'          => 'Wählen Sie den Listentyp aus, in den die Datei importiert werden soll.',
    'UPLOAD_LIST_FILE'                  => 'Listendatei (.txt oder .csv)',
    'UPLOAD_LIST_FILE_EXPLAIN'          => 'Wählen Sie eine Textdatei (.txt oder .csv) aus, die einen Eintrag pro Zeile enthält (z. B. User-Agents, IPs oder Referer). Bei CSV-Dateien wird nur das erste Feld verwendet.',
    'UPLOAD'                            => 'Hochladen',
    'UPLOADING'                         => 'Lädt hoch...',
    'IMPORT_FROM_TXT_FILES'             => 'Aus Standardlisten importieren (bots.txt, botsip.txt, botsref.txt)',
    'IMPORTING'                         => 'Importiert...',
    'ACP_STOPBADBOTS_DEFAULT_LISTS_IMPORTED' => 'Standardlisten erfolgreich importiert: %d Einträge hinzugefügt.',
    'STOPBADBOTS_DEFAULT_LIST_IMPORTED' => 'Standardliste importiert: %d Einträge zu %s hinzugefügt.',
    'STOPBADBOTS_IMPORT_FAILED'         => 'Import der Standardliste fehlgeschlagen: %s.',
    'ACP_STOPBADBOTS_IMPORT_FAILED'     => 'Fehler beim Import der Standardliste: %s.',
    'FILE_READ_ERROR'                   => 'Fehler beim Lesen der Datei %s. Überprüfen Sie, ob die Datei existiert und Leseberechtigungen hat.',

    // Logs
    'ACP_STOPBADBOTS_BLOCKED'               => 'Zugriff verweigert: %s',
    'LOG_BOT_BLOCKED'                       => 'Bot blockiert: %s',
    'LOG_CLEARED'                           => 'Sperr-Protokolle erfolgreich gelöscht.',
    'SEARCH_LOGS'                           => 'Protokolle durchsuchen',
    'SEARCH_LOGS_EXPLAIN'                   => 'Durchsuchen Sie Protokolle nach User-Agent, IP, Referer oder Grund.',
    'SEARCH_LOGS_PLACEHOLDER'               => 'User-Agent, IP, Referer oder Grund eingeben',
    'DATE_FROM'                             => 'Startdatum',
    'DATE_TO'                               => 'Enddatum',
    'EXPORT_LOGS'                           => 'Protokolle als CSV exportieren',
    'NO_LOG_ENTRIES'                        => 'Keine Sperr-Protokolleinträge gefunden.',
    'LOG_TIME'                              => 'Datum/Uhrzeit',
    'USER_AGENT'                            => 'User-Agent',
    'IP'                                    => 'IP-Adresse',
    'REFERER'                               => 'Referer',
    'REASON'                                => 'Grund',
    'ALL'                                   => 'Alle',

    // Statistics and Cron
    'STATISTICS_AND_CRON'               => 'Statistiken und Cron',
    'CRON_LAST_RUN'                     => 'Letzte Cron-Ausführung',
    'DAILY_BLOCKS'                      => 'Gesamtzahl der täglichen Sperren',
    'RUN_CRON'                          => 'Cron jetzt ausführen',
    'CONFIRM_RUN_CRON'                  => 'Sind Sie sicher, dass Sie die Cron-Aufgabe jetzt ausführen möchten?',
    'NEVER'                             => 'Nie',
    'STOPBADBOTS_CRON_DISABLED'         => 'Cron-Aufgabe ist deaktiviert.',
    'STOPBADBOTS_LOG_CLEANED'           => 'Alte Protokolle erfolgreich bereinigt: %d Einträge entfernt.',
    'STOPBADBOTS_LOG_ERROR'             => 'Fehler bei der Cron-Aufgabe: %s',

    // Search and Filters
    'SEARCH_LIST'                       => 'In Listen suchen',
    'SEARCH_LIST_PLACEHOLDER'           => 'User-Agent, IP oder Referer eingeben',
    'CLEAR_SEARCH'                      => 'Suche löschen',
    'SEARCH_RESULTS'                    => 'Suchergebnisse',
    'NO_SEARCH_PERFORMED'               => 'Keine Suche durchgeführt.',
    'NO_SEARCH_RESULTS'                 => 'Keine Ergebnisse für den Suchbegriff gefunden.',
    'CHOOSE_FILE'                       => 'Datei auswählen',
    'FILTER_LIST'                       => 'Liste filtern',
    'FILTER_UA_LIST'                    => 'User-Agent-Sperrliste filtern',
    'FILTER_IP_LIST'                    => 'IP-Sperrliste filtern',
    'FILTER_REF_LIST'                   => 'Referer-Sperrliste filtern',
    'FILTER_UA_WHITELIST'               => 'User-Agent-Zulassungsliste filtern',
    'FILTER_IP_WHITELIST'               => 'IP-Zulassungsliste filtern',
    'FILTER_REF_WHITELIST'              => 'Referer-Zulassungsliste filtern',
    'FILTER_BY_LIST_TYPE'               => 'Nach Listentyp filtern',

    // Actions on Lists
    'ADD_USER_AGENT'                    => 'User-Agent hinzufügen',
    'ADD_IP_ADDRESS'                    => 'IP-Adresse hinzufügen',
    'ADD_REFERER'                       => 'Referer hinzufügen',
    'NEW_USER_AGENT'                    => 'Neuer User-Agent',
    'NEW_IP_ADDRESS'                    => 'Neue IP-Adresse',
    'NEW_REFERER'                       => 'Neuer Referer',
    'NEW_UA_WHITELIST'                  => 'Neuer User-Agent (Zulassungsliste)',
    'NEW_IP_WHITELIST'                  => 'Neue IP-Adresse (Zulassungsliste)',
    'NEW_REF_WHITELIST'                 => 'Neuer Referer (Zulassungsliste)',
    'DELETE_SELECTED'                   => 'Ausgewählte löschen',
    'SELECT'                            => 'Auswählen',
    'SELECT_ALL'                        => 'Alle auswählen',
    'NO_ENTRIES_SELECTED'               => 'Kein Eintrag wurde zum Löschen ausgewählt.',
    'NO_USER_AGENTS'                    => 'Keine User-Agents auf der Liste.',
    'NO_IP_ADDRESSES'                   => 'Keine IPs auf der Liste.',
    'NO_REFERERS'                       => 'Keine Referer auf der Liste.',
    'USER_AGENT_LIST'                   => 'Liste der User-Agents',
    'IP_ADDRESS_LIST'                   => 'Liste der IPs',
    'REFERER_LIST'                      => 'Liste der Referer',
    'BLOCK_LOG'                         => 'Sperr-Protokoll',
    'OPTIONS'                           => 'Optionen',
    'ADDED_TIME'                        => 'Hinzugefügt am',
    'LIST_TYPE'                         => 'Listentyp',

    // Confirmations
    'CONFIRM_RESET_DEFAULT'             => 'Sind Sie sicher, dass Sie die Standardeinstellungen wiederherstellen möchten? Dies löscht alle aktuellen Listen und fügt die anfänglichen Daten erneut ein.',
    'CONFIRM_CLEAR_LOG'                 => 'Protokolle leeren',
    'CONFIRM_CLEAR_LOG_MESSAGE'         => 'Sind Sie sicher, dass Sie alle Sperr-Protokolle leeren möchten?',
    'CONFIRM_DELETE_LIST'               => 'Sind Sie sicher, dass Sie diesen Eintrag löschen möchten?',
    'CONFIRM_DELETE_SELECTED_LISTS'     => 'Sind Sie sicher, dass Sie die ausgewählten Einträge löschen möchten?',

    // Errors
    'FORM_INVALID'                      => 'Ungültiges Formular. Bitte versuchen Sie es erneut.',
    'INVALID_IP'                        => 'Ungültiges IP- oder CIDR-Format.',
    'INVALID_REFERER'                   => 'Ungültiges Referer-Format. Es muss eine gültige Domain oder URL sein.',
    'DB_ERROR'                          => 'Datenbankfehler: %s',
    'LOG_ERROR'                         => 'Fehler bei der Erweiterung "Böse Bots stoppen": %s',
    'TABLE_NOT_FOUND'                   => 'Tabelle nicht gefunden: %s',
    'INVALID_MODE'                      => 'Ungültiger Modus angegeben.',
    'FILE_NOT_FOUND'                    => 'Die Datei %s wurde nicht gefunden.',
    'FILE_NOT_READABLE'                 => 'Die Datei %s ist nicht lesbar.',
    'FILE_READ_ERROR'                   => 'Fehler beim Lesen der Datei %s. Überprüfen Sie, ob die Datei existiert und Leseberechtigungen hat.',
    'INVALID_LIST_TYPE'                 => 'Ungültiger Listentyp ausgewählt.',
    'NO_FILE_UPLOADED'                  => 'Keine Datei hochgeladen.',
    'INVALID_LIST_TYPE_MISMATCH'        => 'Der Inhalt der Datei stimmt nicht mit dem ausgewählten Listentyp überein. Überprüfen Sie das Dateiformat.',
    'INVALID_FILE_TYPE'                 => 'Ungültiger Dateityp. Nur Textdateien (.txt oder .csv) sind erlaubt.',
    'UPLOAD_ERROR'                      => 'Unerwarteter Fehler beim Verarbeiten des Dateiuploads. Bitte versuchen Sie es erneut.',
    'FILEINFO_EXTENSION_NOT_ENABLED'    => 'Die PHP-Erweiterung "fileinfo" ist nicht aktiviert. Überprüfen Sie die Konfiguration in php.ini.',
    'INVALID_FILE_PATH'                 => 'Ungültiger oder unzugänglicher Dateipfad. Überprüfen Sie die Upload-Einstellungen in php.ini.',
    'LOAD_MORE'                         => 'Mehr laden',
    'LOAD_MORE_ERROR'                   => 'Fehler beim Laden weiterer Einträge. Bitte versuchen Sie es später erneut.',
    'NO_ENTRY_FOUND'                    => 'Kein Eintrag in der angegebenen Liste gefunden.',
    'INVALID_SESSION'                   => 'Ungültige Sitzung. Bitte melden Sie sich erneut an.',
    'IMPORT_FROM_TXT_FILES_FAILED'      => 'Fehler beim Importieren von Einträgen aus Textdateien.',
    'TOTAL_LOGS'                        => 'Protokolle gesamt',
    'PAGE_NUMBER'                       => 'Seite',
    'ACP_STOPBADBOTS_LIST_UPLOADED_WITH_TYPE' => 'StopBadBots-Liste erfolgreich hochgeladen',
    'EXPORT_LOGS'                       => 'Protokolle als CSV exportieren',
    'CONFIRM_EXPORT_LOGS_MESSAGE'       => 'Möchten Sie die Protokolle in eine CSV-Datei exportieren?',
    'ACP_STOPBADBOTS_DEBUG_ENABLED'     => 'Debug-Modus aktivieren',
    'ACP_STOPBADBOTS_DEBUG_ENABLED_EXPLAIN' => 'Aktiviert die detaillierte Protokollierung zur Fehlersuche der Erweiterung "Böse Bots stoppen". Verwenden Sie diese Option nur zu Test- oder Diagnosezwecken, da sie das Protokollvolumen erheblich erhöhen kann.',
]);