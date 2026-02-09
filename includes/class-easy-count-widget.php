<?php

if (!defined('ABSPATH')) exit;

if (!class_exists('Easy_Count_Widget')) {
    class Easy_Count_Widget extends WP_Widget {

        public function __construct() {
            parent::__construct(
                'easy_count_widget', // ID de base du widget
                __('Easy Count', 'easy-count'), // Nom du widget
                array('description' => __('Affiche le compteur de visites', 'easy-count')) // Options du widget
            );
        }

        public function widget($args, $instance) {
            $count = (int) get_option('easy_count_total', 0);
            $formatted_count = str_pad($count, 7, '0', STR_PAD_LEFT);
            $digits = str_split($formatted_count);

            echo $args['before_widget'];
echo '<div class="easy-count-widget-container">';
echo '<div class="easy-count-title">' . __('Nombre de visites', 'easy-count') . '</div>';
echo '<div class="easy-count-digits">';
foreach ($digits as $digit) {
    echo '<span>' . esc_html($digit) . '</span>';
}
echo '</div>';
echo '</div>';

echo $args['after_widget'];

        }

    }
}
