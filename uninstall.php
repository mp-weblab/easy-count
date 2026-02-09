<?php
// Empêche l'accès direct
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Supprimer les options enregistrées
delete_option('easy_count_total');
delete_option('easy_count_subscribers');
delete_option('easy_count_non_subscribers');

// Ajoute ici toute autre option utilisée par ton plugin

