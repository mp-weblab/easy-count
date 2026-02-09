<?php
/*
 * Plugin Name: Easy-count
 * Plugin URI: https://example.com
 * Description: Compteur de visites sous forme de widget proposant une interface admin avec des statistiques détaillées et un cookie paramétrable (min/h/j/m).
 * Version: 1.0
 * Author: Crabouille777
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: easy-count
 *
 * Note de l'auteur :
 * Ce plugin est distribué gratuitement dans un esprit de partage.
 * Merci de ne pas le vendre ou monétiser sous une forme quelconque.
 */


if (!defined('ABSPATH')) exit;

add_action('init', function () {

    if (session_status() === PHP_SESSION_NONE) {

        }
    }, 1);

//  valeur minimale de départ (valeur d'un ancien compteur à incrémenter manuellement).
   define('EASY_COUNT_MIN', 0); 


require_once plugin_dir_path(__FILE__) . 'includes/class-easy-count-widget.php'; // Chargement classe widget
require_once plugin_dir_path(__FILE__) . 'includes/class-easy-count-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-easy-count-logger.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-easy-count.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';


// Création de l'instance globale de Easy_Count
global $easy_count;

add_action('plugins_loaded', function() {
    global $easy_count;
    $easy_count = new Easy_Count();

    new Easy_Count_Admin();

});




// Fonction utilitaire pour détecter les bots via le user agent

if (!function_exists('is_bot')) {
    function is_bot() {
        if (empty($_SERVER['HTTP_USER_AGENT'])) {
            return true;
        }
        $bots = ['bot', 'crawl', 'slurp', 'spider', 'curl', 'wget', 'robot', 'archive', 'transcoder', 'fetch', 'monitor'];
        $agent = strtolower($_SERVER['HTTP_USER_AGENT']);
        foreach ($bots as $bot) {
            if (strpos($agent, $bot) !== false) {
                return true;
            }
        }
        return false;
    }
}

function easy_count_get_cookie_duration_seconds() {
    $duration = get_option('easy_count_cookie_duration', [
        'months' => 0,
        'days' => 0,
        'hours' => 2,
        'minutes' => 0
    ]);

    $months = (int) ($duration['months'] ?? 0);
    $days = (int) ($duration['days'] ?? 0);
    $hours = (int) ($duration['hours'] ?? 0);
    $minutes = (int) ($duration['minutes'] ?? 0);

    $seconds = 0;
    $seconds += $months * 30 * 24 * 3600;
    $seconds += $days * 24 * 3600;
    $seconds += $hours * 3600;
    $seconds += $minutes * 60;

    return $seconds;
}

add_action('init', function() {
    load_plugin_textdomain('easy-count', false, dirname(plugin_basename(__FILE__)) . '/languages');
});



add_action('template_redirect', function() {
    global $easy_count;
    if ($easy_count instanceof Easy_Count) {
        $easy_count->maybe_increment_visit();
    }
});


// Widget
add_action('widgets_init', function() {
    register_widget('Easy_Count_Widget');
});

// Initialisation du plugin
function easy_count_init_plugin() {
}
add_action('plugins_loaded', 'easy_count_init_plugin');


// Fonction d'activation du plugin - initialise le compteur global
function easy_count_activate() {
    $current = get_option('easy_count_total', false);

    // Si l’option n’existe pas, ou si elle est inférieure à la valeur minimale, on met à jour
    if ($current === false || $current < EASY_COUNT_MIN) {
        update_option('easy_count_total', EASY_COUNT_MIN);
    }
}
register_activation_hook(__FILE__, 'easy_count_activate');

// Lien vers les paramètres dans la page des plugins
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'easy_count_add_settings_link');
function easy_count_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=easy-count') . '">Paramètres</a>';
    array_unshift($links, $settings_link);
    return $links;
}






