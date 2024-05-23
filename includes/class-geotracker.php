<?php
class GeoTracker {

    public static function init() {
        // Registrar ganchos y filtros
        add_action('admin_menu', array(__CLASS__, 'add_admin_menu'));
        add_action('init', array(__CLASS__, 'register_endpoints'));
        add_action('init', array(__CLASS__, 'add_tecnico_role'));
    }

    public static function activate() {
        // Código para ejecutar en la activación del plugin
        self::add_tecnico_role();
        flush_rewrite_rules();
    }

    public static function deactivate() {
        // Código para ejecutar en la desactivación del plugin
        flush_rewrite_rules();
    }

    public static function add_admin_menu() {
        add_menu_page('Geolocalización', 'Geolocalización', 'manage_options', 'geotracker', array(__CLASS__, 'admin_page'), 'dashicons-location-alt');
    }

    public static function admin_page() {
        include GEOTRACKER_PLUGIN_DIR . 'includes/admin-page.php';
    }

    public static function register_endpoints() {
        register_rest_route('geotracker/v1', '/update-location', array(
            'methods' => 'POST',
            'callback' => 'geotracker_update_location',
            'permission_callback' => '__return_true',
        ));
    }

    public static function add_tecnico_role() {
        add_role(
            'tecnico',
            __('Técnico'),
            array(
                'read' => true,
                'edit_posts' => false,
                'delete_posts' => false,
            )
        );
    }
}
