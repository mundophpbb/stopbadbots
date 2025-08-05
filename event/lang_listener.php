<?php
/**
 * @package mundophpbb\stopbadbots
 * @copyright (c) 2024 Mundo phpBB
 * @license https://opensource.org/licenses/GPL-2.0 GPL-2.0-only
 */

namespace mundophpbb\stopbadbots\event;

use phpbb\language\language;
use phpbb\event\data;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class lang_listener implements EventSubscriberInterface
{
    protected $language;

    /**
     * Construtor
     *
     * @param language $language Sistema de idiomas
     */
    public function __construct(language $language)
    {
        // Log detalhado para depuração
        error_log('lang_listener: Verificando dependência - language: ' . (is_object($language) ? get_class($language) : 'null'));

        // Verificar dependência
        if (!$language) {
            error_log('lang_listener: Dependência ausente: language');
            throw new \Exception('Dependência ausente no lang_listener: language');
        }

        $this->language = $language;
    }

    /**
     * Registra os eventos que o listener vai escutar
     *
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            'core.user_setup' => 'load_language_on_setup',
        ];
    }

    /**
     * Carrega o idioma da extensão durante a configuração do usuário
     *
     * @param data $event Dados do evento
     */
    public function load_language_on_setup(data $event)
    {
        error_log('lang_listener: Carregando idioma para evento core.user_setup');
        $lang_set_ext = $event['lang_set_ext'];
        $lang_set_ext[] = [
            'ext_name' => 'mundophpbb/stopbadbots',
            'lang_set' => 'stopbadbots',
        ];
        $event['lang_set_ext'] = $lang_set_ext;
        error_log('lang_listener: Idioma adicionado para mundophpbb/stopbadbots');
    }
}