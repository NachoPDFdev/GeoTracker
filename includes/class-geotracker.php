<?php
class GeoTracker {
    public static function init() {
        add_action('admin_menu', array(__CLASS__, 'admin_menu'));
    }

    public static function activate() {
        // Código de activación
    }

    public static function deactivate() {
        // Código de desactivación
    }

    public static function admin_menu() {
        add_menu_page('Geolocalización', 'Geolocalización', 'manage_options', 'geotracker', array(__CLASS__, 'admin_page'), 'dashicons-location-alt');
    }

    public static function admin_page() {
        require_once GEOTRACKER_PLUGIN_DIR . 'includes/admin-page.php';
    }
}
