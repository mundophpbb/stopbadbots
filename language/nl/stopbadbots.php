<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2025 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

/**
 * Language file for stopbadbots extension (English)
 * This file contains the translations for the StopBadBots extension in English.
 */

if (!defined('IN_PHPBB')) {
    exit;
}

if (empty($lang) || !is_array($lang)) {
    $lang = [];
}

$lang = array_merge($lang, [
    // Algemeen
    'ACP_STOPBADBOTS'                   => 'Stop Slechte Bots',
    'ACP_STOPBADBOTS_TITLE'             => 'Stop Slechte Bots',
    'ACP_STOPBADBOTS_SETTINGS'          => 'Instellingen',
    'ACP_STOPBADBOTS_SETTINGS_EXPLAIN'  => 'Configureer opties om kwaadaardige bots te blokkeren op basis van User-Agent, IP-adres en referer.',
    'ACP_STOPBADBOTS_LISTS'             => 'Blokkeerlijsten',
    'ACP_STOPBADBOTS_LISTS_EXPLAIN'     => 'Beheer blokkeer- en toestaan-lijsten voor User-Agents, IP’s en Referers.',
    'ACP_STOPBADBOTS_LOGS'              => 'Blokkeerlogboek',
    'ACP_STOPBADBOTS_RESTORE'           => 'Standaardinstellingen herstellen',
    'ACP_STOPBADBOTS_RESTORE_EXPLAIN'   => 'Herstelt de standaardinstellingen voor de Stop Slechte Bots-extensie, wist de tabellen en zet de initiële gegevens terug.',
    'ACP_STOPBADBOTS_OVERVIEW'          => 'Overzicht',
    'ACP_STOPBADBOTS_OVERVIEW_EXPLAIN'  => 'Beheer alle bot-blokkeer- en toestaan-lijsten via een vereenvoudigde interface.',
    'ACP_STOPBADBOTS_VERSION'           => 'Extensieversie',

    // Instellingen
    'ACP_STOPBADBOTS_ENABLED'                   => 'Stop Slechte Bots inschakelen',
    'ACP_STOPBADBOTS_ENABLED_EXPLAIN'           => 'Schakel bescherming tegen kwaadaardige bots in of uit.',
    'ACP_STOPBADBOTS_CRON_ENABLED'              => 'Cron-taak inschakelen',
    'ACP_STOPBADBOTS_CRON_ENABLED_EXPLAIN'      => 'Schakelt de cron-taak voor logopschoning en statistiekupdates in of uit.',
    'ACP_STOPBADBOTS_CRON_INTERVAL'             => 'Cron-interval',
    'ACP_STOPBADBOTS_CRON_INTERVAL_EXPLAIN'     => 'Interval in seconden tussen cron-uitvoeringen (minimum 3600 seconden = 1 uur).',
    'ACP_STOPBADBOTS_CRON_INTERVAL_ERROR'       => 'Het cron-interval moet minimaal 3600 seconden (1 uur) zijn.',
    'ACP_STOPBADBOTS_LOG_RETENTION_DAYS'        => 'Logbewaring (dagen)',
    'ACP_STOPBADBOTS_LOG_RETENTION_DAYS_EXPLAIN'=> 'Aantal dagen dat blokkeerlogs bewaard blijven voordat ze automatisch verwijderd worden (minimaal 1 dag).',
    'ACP_STOPBADBOTS_USE_X_FORWARDED_FOR'       => 'Gebruik X-Forwarded-For voor IP-identificatie',
    'ACP_STOPBADBOTS_USE_X_FORWARDED_FOR_EXPLAIN'=> 'Schakel dit alleen in als je server achter een vertrouwde proxy staat die de X-Forwarded-For-header correct instelt. Anders uitschakelen om REMOTE_ADDR te gebruiken en verkeerde blokkades te vermijden.',
    'ACP_STOPBADBOTS_STATISTICS'                => 'Blokkeerstatistieken',
    'ACP_STOPBADBOTS_STATISTICS_EXPLAIN'        => 'Grafiek met het aantal dagelijkse blokkades per type (User-Agent, IP, Referer).',
    'ACP_STOPBADBOTS_DAILY_BLOCKS'              => 'Dagelijkse blokkades',
    'ACP_STOPBADBOTS_BLOCKS'                    => 'Aantal blokkades',
    'ACP_STOPBADBOTS_RESET_DEFAULT'             => 'Standaardinstellingen herstellen',
    'ACP_STOPBADBOTS_RESET_DEFAULT_SUCCESS'     => 'Instellingen succesvol teruggezet naar standaardwaarden!',
    'ACP_STOPBADBOTS_SAVED'                     => 'Instellingen succesvol opgeslagen!',
    'ACP_STOPBADBOTS_CRON_RUN_SUCCESS'          => 'Cron-taak succesvol uitgevoerd!',
    'ACP_STOPBADBOTS_LISTS_SAVED'               => 'Lijsten succesvol opgeslagen!',

    // Blokkeerlijsten
    'ACP_STOPBADBOTS_UA_LIST'           => 'User-Agent Zwarte lijst',
    'ACP_STOPBADBOTS_UA_LIST_EXPLAIN'   => 'Voer één User-Agent per regel in om te blokkeren (bijv. "_zbot" of "BadBot"). Bij CSV-bestanden wordt alleen het eerste veld gebruikt.',
    'ACP_STOPBADBOTS_IP_LIST'           => 'IP Zwarte lijst',
    'ACP_STOPBADBOTS_IP_LIST_EXPLAIN'   => 'Voer één IP-adres of CIDR-bereik per regel in om te blokkeren (bijv. "1.180.70.178" of "192.168.1.0/24").',
    'ACP_STOPBADBOTS_REF_LIST'          => 'Referer Zwarte lijst',
    'ACP_STOPBADBOTS_REF_LIST_EXPLAIN'  => 'Voer één referer per regel in om te blokkeren (bijv. "000Free.us" of "example.com"). Het protocol wordt genegeerd.',
    'ACP_STOPBADBOTS_UA_WHITELIST'      => 'User-Agent Witte lijst',
    'ACP_STOPBADBOTS_UA_WHITELIST_EXPLAIN' => 'Voer één User-Agent per regel in om toe te staan (bijv. "Googlebot").',
    'ACP_STOPBADBOTS_IP_WHITELIST'      => 'IP Witte lijst',
    'ACP_STOPBADBOTS_IP_WHITELIST_EXPLAIN' => 'Voer één IP-adres of CIDR-bereik per regel in om toe te staan (bijv. "192.168.1.1" of "192.168.1.0/24").',
    'ACP_STOPBADBOTS_REF_WHITELIST'     => 'Referer Witte lijst',
    'ACP_STOPBADBOTS_REF_WHITELIST_EXPLAIN' => 'Voer één referer per regel in om toe te staan (bijv. "example.com").',
    'ACP_STOPBADBOTS_UA_ADDED'          => 'User-Agent succesvol toegevoegd!',
    'ACP_STOPBADBOTS_IP_ADDED'          => 'IP-adres succesvol toegevoegd!',
    'ACP_STOPBADBOTS_REF_ADDED'         => 'Referer succesvol toegevoegd!',
    'ACP_STOPBADBOTS_UA_WHITELIST_ADDED' => 'User-Agent (witte lijst) succesvol toegevoegd!',
    'ACP_STOPBADBOTS_IP_WHITELIST_ADDED' => 'IP-adres (witte lijst) succesvol toegevoegd!',
    'ACP_STOPBADBOTS_REF_WHITELIST_ADDED' => 'Referer (witte lijst) succesvol toegevoegd!',
    'ACP_STOPBADBOTS_LIST_UPDATED'      => 'Invoer succesvol bijgewerkt!',
    'ACP_STOPBADBOTS_LIST_DELETED'      => 'Invoer succesvol verwijderd!',
    'ACP_STOPBADBOTS_LISTS_DELETED'     => 'Geselecteerde invoeren succesvol verwijderd!',
    'ACP_STOPBADBOTS_DUPLICATE_ENTRY'   => 'Dubbele invoer: %s bestaat al in de %s lijst.',
    'ACP_STOPBADBOTS_LIST_UPLOADED'     => '%d invoeren succesvol aan de lijst toegevoegd.',

    // Beschrijvingen voor formulieren
    'NEW_USER_AGENT_EXPLAIN'    => 'Voer een User-Agent in om toe te voegen aan de zwarte lijst.',
    'NEW_IP_ADDRESS_EXPLAIN'    => 'Voer een IP-adres of CIDR-bereik in om toe te voegen aan de zwarte lijst.',
    'NEW_REFERER_EXPLAIN'       => 'Voer een referer in om toe te voegen aan de zwarte lijst.',
    'NEW_UA_WHITELIST_EXPLAIN'  => 'Voer een User-Agent in om toe te voegen aan de witte lijst.',
    'NEW_IP_WHITELIST_EXPLAIN'  => 'Voer een IP-adres of CIDR-bereik in om toe te voegen aan de witte lijst.',
    'NEW_REF_WHITELIST_EXPLAIN' => 'Voer een referer in om toe te voegen aan de witte lijst.',
    'SEARCH_LIST_EXPLAIN'       => 'Voer een zoekterm in om te zoeken in de User-Agent-, IP- of Referer-lijsten.',

    // Lijst leeg meldingen
    'NO_ENTRIES'        => 'Geen invoeren gevonden.',
    'NO_UA_WHITELIST'   => 'Geen User-Agent in de witte lijst.',
    'NO_IP_WHITELIST'   => 'Geen IP in de witte lijst.',
    'NO_REF_WHITELIST'  => 'Geen Referer in de witte lijst.',

    // Algemene acties
    'ADD'       => 'Toevoegen',
    'S_SUBMIT'    => 'Versturen',
    'RESET'     => 'Resetten',

    // Upload en import
    'UPLOAD_LIST_TYPE'                  => 'Lijsttype',
    'UPLOAD_LIST_TYPE_EXPLAIN'          => 'Selecteer het type lijst waarvoor het bestand geïmporteerd wordt.',
    'UPLOAD_LIST_FILE'                  => 'Lijstbestand (.txt of .csv)',
    'UPLOAD_LIST_FILE_EXPLAIN'          => 'Selecteer een tekstbestand (.txt of .csv) met één invoer per regel (bijv. User-Agents, IP’s of Referers). Bij CSV-bestanden wordt alleen het eerste veld gebruikt.',
    'UPLOAD'                            => 'Uploaden',
    'UPLOADING'                         => 'Bezig met uploaden...',
    'IMPORT_FROM_TXT_FILES'             => 'Importeren vanuit standaardlijsten (bots.txt, botsip.txt, botsref.txt)',
    'IMPORTING'                         => 'Bezig met importeren...',
    'ACP_STOPBADBOTS_DEFAULT_LISTS_IMPORTED' => 'Standaardlijsten succesvol geïmporteerd: %d invoeren toegevoegd.',
    'STOPBADBOTS_DEFAULT_LIST_IMPORTED' => 'Standaardlijst geïmporteerd: %d invoeren toegevoegd aan %s.',
    'STOPBADBOTS_IMPORT_FAILED'         => 'Importeren van standaardlijst mislukt: %s.',
    'ACP_STOPBADBOTS_IMPORT_FAILED'     => 'Fout bij importeren standaardlijst: %s.',
    'FILE_READ_ERROR'                   => 'Fout bij lezen van bestand %s. Controleer of het bestand bestaat en leesbaar is.',

    // Logs
    'ACP_STOPBADBOTS_BLOCKED'               => 'Toegang geweigerd: %s',
    'LOG_BOT_BLOCKED'                       => 'Bot geblokkeerd: %s',
    'LOG_CLEARED'                           => 'Blokkeerlogs succesvol gewist.',
    'SEARCH_LOGS'                           => 'Zoek in logs',
    'SEARCH_LOGS_EXPLAIN'                   => 'Zoek in logs op User-Agent, IP, Referer of reden.',
    'SEARCH_LOGS_PLACEHOLDER'               => 'Voer User-Agent, IP, Referer of reden in',
    'DATE_FROM'                             => 'Startdatum',
    'DATE_TO'                               => 'Einddatum',
    'EXPORT_LOGS'                           => 'Logs exporteren als CSV',
    'NO_LOG_ENTRIES'                        => 'Geen blokkeerlogs gevonden.',
    'LOG_TIME'                              => 'Datum/tijd',
    'USER_AGENT'                            => 'User-Agent',
    'IP'                                    => 'IP-adres',
    'REFERER'                               => 'Referer',
    'REASON'                                => 'Reden',
    'ALL'                                   => 'Alles',

    // Statistieken en Cron
    'STATISTICS_AND_CRON'       => 'Statistieken en Cron',
    'CRON_LAST_RUN'             => 'Laatste cron-uitvoering',
    'DAILY_BLOCKS'              => 'Totaal dagelijkse blokkades',
    'RUN_CRON'                  => 'Cron nu uitvoeren',
    'CONFIRM_RUN_CRON'          => 'Weet je zeker dat je de cron-taak nu wilt uitvoeren?',
    'NEVER'                     => 'Nooit',
    'STOPBADBOTS_CRON_DISABLED' => 'Cron-taak is uitgeschakeld.',
    'STOPBADBOTS_LOG_CLEANED'   => 'Oude logs succesvol verwijderd: %d invoeren verwijderd.',
    'STOPBADBOTS_LOG_ERROR'     => 'Cron-taak fout: %s',

    // Zoek en filters
    'SEARCH_LIST'               => 'Zoek in lijsten',
    'SEARCH_LIST_PLACEHOLDER'   => 'Voer User-Agent, IP of Referer in',
    'CLEAR_SEARCH'              => 'Zoekopdracht wissen',
    'SEARCH_RESULTS'            => 'Zoekresultaten',
    'NO_SEARCH_PERFORMED'       => 'Geen zoekopdracht uitgevoerd.',
    'NO_SEARCH_RESULTS'         => 'Geen resultaten gevonden voor de zoekterm.',
    'CHOOSE_FILE'               => 'Kies bestand',
    'FILTER_LIST'               => 'Filterlijst',
    'FILTER_UA_LIST'            => 'Filter geblokkeerde User-Agent-lijst',
    'FILTER_IP_LIST'            => 'Filter geblokkeerde IP-lijst',
    'FILTER_REF_LIST'           => 'Filter geblokkeerde Referer-lijst',
    'FILTER_UA_WHITELIST'       => 'Filter toegestane User-Agent-lijst',
    'FILTER_IP_WHITELIST'       => 'Filter toegestane IP-lijst',
    'FILTER_REF_WHITELIST'      => 'Filter toegestane Referer-lijst',
    'FILTER_BY_LIST_TYPE'       => 'Filter op lijsttype',

    // Lijstacties
    'ADD_USER_AGENT'            => 'User-Agent toevoegen',
    'ADD_IP_ADDRESS'            => 'IP-adres toevoegen',
    'ADD_REFERER'               => 'Referer toevoegen',
    'NEW_USER_AGENT'            => 'Nieuwe User-Agent',
    'NEW_IP_ADDRESS'            => 'Nieuw IP-adres',
    'NEW_REFERER'               => 'Nieuwe Referer',
    'NEW_UA_WHITELIST'          => 'Nieuwe User-Agent (witte lijst)',
    'NEW_IP_WHITELIST'          => 'Nieuw IP-adres (witte lijst)',
    'NEW_REF_WHITELIST'         => 'Nieuwe Referer (witte lijst)',
    'DELETE_SELECTED'           => 'Verwijder geselecteerde',
    'SELECT'                    => 'Selecteren',
    'SELECT_ALL'                => 'Alles selecteren',
    'NO_ENTRIES_SELECTED'       => 'Geen invoeren geselecteerd om te verwijderen.',
    'NO_USER_AGENTS'            => 'Geen User-Agent in de lijst.',
    'NO_IP_ADDRESSES'           => 'Geen IP in de lijst.',
    'NO_REFERERS'               => 'Geen Referer in de lijst.',
    'USER_AGENT_LIST'           => 'User-Agent-lijst',
    'IP_ADDRESS_LIST'           => 'IP-lijst',
    'REFERER_LIST'              => 'Referer-lijst',
    'BLOCK_LOG'                 => 'Blokkeerlog',
    'OPTIONS'                   => 'Opties',
    'ADDED_TIME'                => 'Datum toegevoegd',
    'LIST_TYPE'                 => 'Lijsttype',

    // Bevestigingen
    'CONFIRM_RESET_DEFAULT'         => 'Weet je zeker dat je de standaardinstellingen wilt herstellen? Dit verwijdert alle huidige lijsten en zet de initiële gegevens terug.',
    'CONFIRM_CLEAR_LOG'             => 'Logs wissen',
    'CONFIRM_CLEAR_LOG_MESSAGE'     => 'Weet je zeker dat je alle blokkeerlogs wilt wissen?',
    'CONFIRM_DELETE_LIST'           => 'Weet je zeker dat je deze invoer wilt verwijderen?',
    'CONFIRM_DELETE_SELECTED_LISTS' => 'Weet je zeker dat je de geselecteerde invoeren wilt verwijderen?',

    // Fouten
    'FORM_INVALID'                      => 'Ongeldig formulier. Probeer opnieuw.',
    'INVALID_IP'                        => 'Ongeldig IP- of CIDR-formaat.',
    'INVALID_REFERER'                   => 'Ongeldig referer-formaat. Moet een geldig domein of URL zijn.',
    'DB_ERROR'                          => 'Databasefout: %s',
    'LOG_ERROR'                         => 'Stop Slechte Bots-extensie fout: %s',
    'TABLE_NOT_FOUND'                   => 'Tabel niet gevonden: %s',
    'INVALID_MODE'                      => 'Ongeldige modus opgegeven.',
    'FILE_NOT_FOUND'                    => 'Het bestand %s is niet gevonden.',
    'FILE_NOT_READABLE'                 => 'Het bestand %s is niet leesbaar.',
    'FILE_READ_ERROR'                   => 'Fout bij lezen van bestand %s. Controleer of het bestand bestaat en leesbaar is.',
    'INVALID_LIST_TYPE'                 => 'Ongeldig lijsttype geselecteerd.',
    'NO_FILE_UPLOADED'                  => 'Geen bestand geüpload.',
    'INVALID_LIST_TYPE_MISMATCH'        => 'Bestandsinhoud komt niet overeen met het geselecteerde lijsttype. Controleer het bestandsformaat.',
    'INVALID_FILE_TYPE'                 => 'Ongeldig bestandstype. Alleen tekstbestanden (.txt of .csv) zijn toegestaan.',
    'UPLOAD_ERROR'                      => 'Onverwachte fout bij het verwerken van het uploaden. Probeer opnieuw.',
    'FILEINFO_EXTENSION_NOT_ENABLED'    => 'De PHP fileinfo-extensie is niet ingeschakeld. Controleer de configuratie in php.ini.',
    'INVALID_FILE_PATH'                 => 'Ongeldig of ontoegankelijk bestandspad. Controleer de uploadinstellingen in php.ini.',
    'LOAD_MORE'                         => 'Meer laden',
    'LOAD_MORE_ERROR'                   => 'Fout bij laden van meer invoeren. Probeer later opnieuw.',
    'NO_ENTRY_FOUND'                    => 'Geen invoer gevonden in de opgegeven lijst.',
    'INVALID_SESSION'                   => 'Ongeldige sessie. Log opnieuw in.',
    'IMPORT_FROM_TXT_FILES_FAILED'      => 'Importeren uit tekstbestanden mislukt.',
    'TOTAL_LOGS'                        => 'Totaal logs',
    'PAGE_NUMBER'                       => 'Pagina',
    'ACP_STOPBADBOTS_LIST_UPLOADED_WITH_TYPE' => 'StopBadBots-lijst succesvol geüpload',
    'EXPORT_LOGS'                       => 'Logs exporteren als CSV',
    'CONFIRM_EXPORT_LOGS_MESSAGE'       => 'Wil je de logs exporteren naar een CSV-bestand?',
    'ACP_STOPBADBOTS_DEBUG_ENABLED'     => 'Debugmodus inschakelen',
    'ACP_STOPBADBOTS_DEBUG_ENABLED_EXPLAIN' => 'Schakelt gedetailleerde logging in voor het debuggen van de Stop Slechte Bots-extensie. Gebruik dit alleen voor testen of diagnose, aangezien dit de loggrootte aanzienlijk kan vergroten.',
]);