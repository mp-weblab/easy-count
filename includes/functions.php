<?php
if (!defined('ABSPATH')) exit;


//Détection améliorée des bots via User-Agent + log pour vérification
function is_bot() {
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        return true;
    }

    $bots = [
    // Générique, liste non exhaustive. Éviter le bot 'moz' sinon le compteur sera faussé.
    'bot',
    'crawl',
    'slurp',
    'spider',
    'crawler',
    'scanner',
    'fetch',
    'monitor',
    'scrapy',
    'python',
    'curl',
    'wget',
    'axios',
    'httpclient',
    'http_request2',
    'java',
    'libwww',
    'perl',
    'go-http-client',
    'node-superagent',
    'phpspider',
    'okhttp',
    'aiohttp',

    // Grands moteurs de recherche
    'googlebot',
    'bingbot',
    'yandex',
    'baiduspider',
    'duckduckbot',
    'sogou',
    'exabot',
    'facebot',
    'ia_archiver',
    'applebot',
    'seznambot',
    'qwantify',
    'mj12bot',

    // Réseaux sociaux
    'facebookexternalhit',
    'facebot',
    'twitterbot',
    'linkedinbot',
    'slackbot',
    'discordbot',
    'telegrambot',
    'skypeuripreview',
    'whatsapp',
    'viber',
    'pinterest',
    'redditbot',
    'tumblr',
    'nuzzel',
    'bitlybot',
    'vkshare',

    // SEO, marketing, analyse
    'semrushbot',
    'ahrefsbot',
    'dotbot',
    //'moz', Éviter de l'implémenter
    'rogerbot',
    'linkdexbot',
    'screaming frog',
    'serpstatbot',
    'seokicks-robot',
    'sitecheckerbot',
    'netcraftsurveyagent',
    'uptime',
    'pingdom',
    'gtmetrix',
    'siteimprove',
    'contentking',

    // Archives et prévisualisation
    'archive.org_bot',
    'ia_archiver',
    'waybackarchive',
    'urlresolver',
    'pagepeeker',
    'webpreview',
    'outbrain',

    // Sécurité, analyse, pentest
    'zgrab',
    'nmap',
    'masscan',
    'shodan',
    'qualys',
    'censys',
    'wprecon',
    'wpscan',
    'sitelockspider',
    'acunetix',
    'netsparker',

    // Autres
    'uptimerobot',
    'datadog',
    'statuscake',
    'newrelicpinger',
    'hubspot',
    'mailchimp',
    'clicky',
    ];

    $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
    foreach ($bots as $bot) {
        if (strpos($agent, $bot) !== false) {
            return true;
        }
    }
    return false;
}

 // Enregistre une visite dans la tranche horaire actuelle.
function easy_count_record_hourly_visit() {
    $hour_key = date('Y-m-d H:00');      
    $visits = get_option('easy_count_hourly', []);

    if (!is_array($visits)) {
        $visits = [];
    }

    if (isset($visits[$hour_key])) {
        $visits[$hour_key]++;
    } else {
        $visits[$hour_key] = 1;
    }

    update_option('easy_count_hourly', $visits);

}
