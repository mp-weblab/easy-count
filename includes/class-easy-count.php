<?php
if (!defined('ABSPATH')) exit;

class Easy_Count {

    private $plugin_url;

    public function __construct() {
        $this->plugin_url = plugin_dir_url(__DIR__) . 'easy-count/';
        add_action('wp_enqueue_scripts', [$this, 'enqueue_styles']);
        add_action( 'wp', [ $this, 'maybe_increment_visit' ] );
        add_shortcode('easy-count', [$this, 'render_shortcode']);
        add_action('template_redirect', [$this, 'maybe_increment_visit']);
    }

public function maybe_increment_visit() {
    static $already_ran = false;
    if ( $already_ran ) return;
    $already_ran = true;

    // Ne pas compter en admin, REST, AJAX, ou requêtes de fichiers statiques
    if (
        is_admin() ||
        wp_doing_ajax() ||
        wp_is_json_request()
    ) {
        return;
    }

    // Log uniquement si éligible

    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);

    // Ne pas compter les ressources ou certaines URLs
    if (
        strpos($path, '/wp-login.php') === 0 ||
        strpos($path, '/wp-json') === 0 ||
        preg_match('/\.(css|js|jpg|jpeg|png|gif|svg|ico|woff|woff2|ttf|eot|otf)$/i', $path)
    ) {
        return;
    }
    if (is_bot()) {
        return;
    }

$duration = easy_count_get_cookie_duration_seconds();

// Compteur global (uniquement si cookie non présent)
if (!isset($_COOKIE['easy_counted'])) {
    // Total global
    $count = (int) get_option('easy_count_total', 0);
    if ($count < EASY_COUNT_MIN) {
        $count = EASY_COUNT_MIN;
    }
    update_option('easy_count_total', $count + 1);

    // Abonnés / non abonnés
    if (is_user_logged_in()) {
        $count_logged = (int) get_option('easy_count_logged_in', 0);
        update_option('easy_count_logged_in', $count_logged + 1);
    } else {
        $count_not_logged = (int) get_option('easy_count_not_logged_in', 0);
        update_option('easy_count_not_logged_in', $count_not_logged + 1);
    }

    // Pose le cookie global
    if ($duration > 0) {
        setcookie(
            'easy_counted',
            '1',
            time() + $duration,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }

easy_count_record_hourly_visit();

}
    if (is_singular()) {
        $this->increment_visit_count();
    }
}

    // Rendu du shortcode [easy-count]

    public function render_shortcode($atts = [], $content = null) {

        $visits = $this->get_visit_count();
        $digits = str_split(number_format_i18n($visits)); // découpe chaque caractère (chiffre ou espace)

        ob_start();
        ?>
        <div class="easy-count-widget-container">
    <p class="easy-count-title">Nombre de visites :</p>
    <div class="easy-count-digits">
        <?php foreach ($digits as $digit): ?><span class="easy-count-digit"><?php echo esc_html($digit); ?></span><?php endforeach; ?>
    </div>
</div>



        <?php
        return ob_get_clean();
    }

    
     //Ajoute la feuille de style CSS front

    public function enqueue_styles() {

        $plugin_dir = dirname(__FILE__, 2);
        $css_url = plugins_url('assets/css/style.css', $plugin_dir . '/dummy.php'); 
        $css_url = plugins_url('assets/css/style.css', dirname(dirname(__FILE__)) . '/easy-count.php'); 
        $js_url = plugins_url('assets/js/easy-count-inline-style.js', __DIR__ . '/../');
        wp_enqueue_script('easy-count-inline-style', $js_url, [], time(), true);

        // En dev, version timestamp pour éviter cache, sinon version fixe
        $version = defined('WP_DEBUG') && WP_DEBUG ? time() : '1.0.0';

        wp_enqueue_style(
            'easy-count-style',
            $css_url,
            [],
            time(),
            'all'
        );
    }

    
    // Récupère le nombre de visites pour le post courant
    private function get_visit_count() {
        $post_id = get_the_ID();
        $visits = get_post_meta($post_id, '_easy_count_visits', true);
        return $visits ? (int)$visits : 0;
    }


    // Incrémente la visite si cookie non présent

    private function increment_visit_count() {
        if ($this->has_visited()) {
            return;
        }
        $post_id = get_the_ID();
        $visits = $this->get_visit_count() + 1;
        update_post_meta($post_id, '_easy_count_visits', $visits);
        $this->set_visit_cookie();
    }

     //Vérifie si le visiteur a déjà visité ce post (cookie)
    private function has_visited() {
        $post_id = get_the_ID();
        return isset($_COOKIE['easy_count_visited_' . $post_id]);
    }

     //Pose un cookie marquant la visite sur ce post
    private function set_visit_cookie() {
        $post_id = get_the_ID();

        // Durée du cookie configurable ici
        $months = 0;
        $days = 1;
        $hours = 0;
        $minutes = 0;

        $expiration = time()
                    + ($months * 30 * 24 * 60 * 60)
                    + ($days * 24 * 60 * 60)
                    + ($hours * 60 * 60)
                    + ($minutes * 60);

        setcookie(
            'easy_count_visited_' . $post_id,
            '1',
            $expiration,
            COOKIEPATH,
            COOKIE_DOMAIN,
            is_ssl(),
            true
        );
    }
}
