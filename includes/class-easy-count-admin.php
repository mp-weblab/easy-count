<?php
if (!defined('ABSPATH')) exit;

class Easy_Count_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('wp_ajax_easy_count_get_stats', [$this, 'handle_get_stats']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);

    }

    public function enqueue_admin_assets($hook) {

    if ($hook !== 'settings_page_easy-count') {
        return;
    }

    wp_enqueue_script(
    'chart-js',
    'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
    [],
    '4.4.1',
    true
);

    wp_enqueue_script(
        'easy-count-admin-charts',
        plugin_dir_url(__FILE__) . '../assets/js/admin-charts.js',
        ['jquery', 'chart-js'],
        '1.0',
        true
    );

    wp_localize_script('easy-count-admin-charts', 'easyCountData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('easy_count_nonce')
    ]);
}

    public function add_menu() {
        add_options_page(
            'Easy Count',
            'Easy Count',
            'manage_options',
            'easy-count',
            [$this, 'render_settings_page']
        );
    }

    public function handle_get_stats() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Non autorisé', 403);
    }


    $daily   = Easy_Count_Logger::get_daily_stats();
    $weekly  = Easy_Count_Logger::get_weekly_stats();
    $monthly = Easy_Count_Logger::get_monthly_stats();
    $hourly  = Easy_Count_Logger::get_hourly_stats();

    wp_send_json_success([
    
        'daily'   => $daily,
        'weekly'  => $weekly,
        'monthly' => $monthly,
        'hourly'  => $hourly,
    ]);
}


    public function register_settings() {
        register_setting('easy_count_settings', 'easy_count_cookie_duration');
    }

    public function render_settings_page() {
            if (isset($_POST['easy_count_reset']) && check_admin_referer('easy_count_reset_action', 'easy_count_reset_nonce')) {
        delete_option('easy_count_total');
        delete_option('easy_count_logged_in');
        delete_option('easy_count_not_logged_in');
        echo '<div class="notice notice-success is-dismissible"><p>Les compteurs ont été réinitialisés avec succès.</p></div>';
    }
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
        
        $stats = Easy_Count_Logger::get_stats(7);
        ?>

<div class="wrap">
    <h1>Réglages du compteur de visites</h1>
    <h4>Vous trouverez le compteur dans apparence, personnaliser, widgets. Choisissez un emplacement (colonne latérale etc...) puis ajouter (+), parcourir et enfin widgets => Easy Count<h4>
    <h4>Durée du cookie recommandée : 1h00</h4>
    <form method="post" action="options.php">
        <?php settings_fields('easy_count_settings'); ?>
        <table class="form-table">
            <tr>
                <th scope="row">Durée du cookie</th>
                <td>
                    <table>
                        <tr>
                            <td><label for="easy_count_minutes">Minutes :</label></td>
                            <td><input id="easy_count_minutes" type="number" name="easy_count_cookie_duration[minutes]" value="<?php echo esc_attr($duration['minutes']); ?>" min="0" style="width: 50px;"></td>
                        </tr>
                        <tr>
                            <td><label for="easy_count_hours">Heures :</label></td>
                            <td><input id="easy_count_hours" type="number" name="easy_count_cookie_duration[hours]" value="<?php echo esc_attr($duration['hours']); ?>" min="0" style="width: 50px;"></td>
                        </tr>
                        <tr>
                            <td><label for="easy_count_days">Jours :</label></td>
                            <td><input id="easy_count_days" type="number" name="easy_count_cookie_duration[days]" value="<?php echo esc_attr($duration['days']); ?>" min="0" style="width: 50px;"></td>
                        </tr>
                        <tr>
                            <td><label for="easy_count_months">Mois :</label></td>
                            <td><input id="easy_count_months" type="number" name="easy_count_cookie_duration[months]" value="<?php echo esc_attr($duration['months']); ?>" min="0" style="width: 50px;"></td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        <?php submit_button('Enregistrer les réglages'); ?>
    </form>

    <hr>
    <h2>Statistiques de visites</h2>

    <?php
    $total         = (int) get_option('easy_count_total', 0);
    $logged_in     = (int) get_option('easy_count_logged_in', 0);
    $not_logged_in = (int) get_option('easy_count_not_logged_in', 0);
    ?>

    <p>
        <strong>Total visiteurs :</strong> <?php echo number_format_i18n($total); ?>
        &nbsp;|&nbsp;
        <strong>Abonnés / Non abonnés :</strong> 
        <?php echo number_format_i18n($logged_in); ?> / <?php echo number_format_i18n($not_logged_in); ?>
    </p>

    <hr>
    <div style="position: relative; height: 300px; max-width: 800px;">
    <canvas id="easy-count-daily-line" class="easy-count-chart"></canvas>
    </div>
    <div style="position: relative; height: 300px; max-width: 800px;">
        <canvas id="easy-count-daily" class="easy-count-chart"></canvas>
    </div>
    <div style="position: relative; height: 300px; max-width: 800px;">
        <canvas id="easy-count-weekly" class="easy-count-chart"></canvas>
    </div>
    <div style="position: relative; height: 300px; max-width: 800px;">
        <canvas id="easy-count-monthly" class="easy-count-chart"></canvas>
    </div>
    <form method="post" style="margin-top: 20px;">
    <?php wp_nonce_field('easy_count_reset_action', 'easy_count_reset_nonce'); ?>
    <input type="submit" name="easy_count_reset" class="button button-secondary" value="Réinitialiser le compteur">
    <p class="description">Remise du compteur à zéro sans supprimer les logs détaillés.</p>
</form>
</div>

        <?php
    }
}
