<?php
if (!defined('ABSPATH')) exit;

class Easy_Count_Logger {

    // Enregistre une visite
    public static function log_visit() {

        if (!isset($_COOKIE['easy_count_session'])) {

            // Créer le cookie de session pour éviter les comptages multiples (10 minutes)
            setcookie('easy_count_session', '1', time() + (10 * 60), '/', '', true, true);

            // Incrémenter le total général
            $total = (int) get_option('easy_count_total', 0);
            update_option('easy_count_total', $total + 1);


            // Incrémenter les non abonnés (par défaut)
            $non_subs = (int) get_option('easy_count_total_non_subscribers', 0);
            update_option('easy_count_total_non_subscribers', $non_subs + 1);

            // Enregistrer la visite journalière
            self::record_daily_visit();

            // Log debug pour la visite du jour
            $today = date('Y-m-d');
            $count = (int) get_option("easy_count_day_{$today}", 0);


            // Incrémenter la statistique horaire
            $hour = date('H');
            $hourly_visits = get_option('easy_count_hourly_visits', []);
            if (!isset($hourly_visits[$hour])) {
                $hourly_visits[$hour] = 0;
            }
            $hourly_visits[$hour]++;
            update_option('easy_count_hourly_visits', $hourly_visits);
        }
    }

    // Récupère les stats horaires formatées
    public static function get_hourly_stats() {
        $raw = get_option('easy_count_hourly', []);
        $stats = [];

        // Initialiser toutes les heures à 0
        for ($i = 0; $i < 24; $i++) {
            $hour = str_pad($i, 2, '0', STR_PAD_LEFT); // "00", "01", ..., "23"
            $label = $hour . 'h';
            $stats[$label] = 0;
        }

        // Ajouter les données existantes
        foreach ($raw as $datetime => $count) {
            $hour = date('H', strtotime($datetime));
            $label = $hour . 'h';
            $stats[$label] += $count;
        }
        return $stats;
    }

    // Alias pour compatibilité, récupère stats journalières sur X jours
    public static function get_stats($days = 7) {
        return self::get_daily_stats($days);
    }

    // Récupère les stats hebdomadaires (6 dernières semaines)
    public static function get_weekly_stats() {
    $daily = self::get_daily_stats(42); // 6 semaines * 7 jours
    $weeks = [];

    for ($i = 5; $i >= 0; $i--) {
        $start = strtotime("monday -$i week", strtotime('today'));
        $end = strtotime("+6 days", $start);
        $label = date('d/m', $start) . ' - ' . date('d/m', $end);
        $weeks[$label] = 0;

        for ($d = 0; $d < 7; $d++) {
            $day = date('Y-m-d', strtotime("+$d days", $start));
            $weeks[$label] += $daily[$day] ?? 0;
        }

    }
    return $weeks;
}
    // Récupère les stats mensuelles (6 derniers mois)
    public static function get_monthly_stats() {
    // On récupère 6 mois * 31 jours max = 186 jours (marge large)
    $daily = self::get_daily_stats(186);
    $months = [];

    for ($i = 5; $i >= 0; $i--) {
        $month = date('Y-m', strtotime("-$i months"));
        $months[$month] = 0;

        $days_in_month = date('t', strtotime($month . '-01'));
        for ($d = 1; $d <= $days_in_month; $d++) {
            $day = sprintf('%s-%02d', $month, $d);
            $months[$month] += $daily[$day] ?? 0;
        }

    }
    return $months;
}



    // Récupère les stats journalières sur un nombre de jours donné (par défaut 7)
    public static function get_daily_stats(int $days = 7): array {
    $hourly = get_option('easy_count_hourly', []);
    $daily_counts = [];

    // Agrégation des visites horaires en visites journalières
    foreach ($hourly as $datetime => $count) {
        $day = substr($datetime, 0, 10); // YYYY-MM-DD
        if (!isset($daily_counts[$day])) {
            $daily_counts[$day] = 0;
        }
        $daily_counts[$day] += $count;
    }

    // Maintenant on récupère uniquement les $days derniers jours
    $result = [];
    for ($i = $days - 1; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-{$i} days"));
        $result[$date] = $daily_counts[$date] ?? 0;
    }

    return $result;
}


    // Enregistre une visite pour la date du jour
    public static function record_daily_visit() {
        $today = date('Y-m-d');
        $count = (int) get_option("easy_count_day_{$today}", 0);
        update_option("easy_count_day_{$today}", $count + 1);

    }

}
