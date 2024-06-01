<?php

/**
 * Plugin Name: Simple Event Listing feed from Google Sheets
 * Description: A plugin that displays Event Listings.
 * Version: 1.0.0
 * Author: pjaudiomv
 * Author URI: https://github.com/pjaudiomv/simple-event-sheets/
 */

if (basename($_SERVER['PHP_SELF']) == basename(__FILE__)) {
    die('Sorry, but you cannot access this page directly.');
}

spl_autoload_register(function (string $class) {
    if (strpos($class, 'SimpleEventSheets\\') === 0) {
        $class = str_replace('SimpleEventSheets\\', '', $class);
        require __DIR__ . '/src/' . str_replace('\\', '/', $class) . '.php';
    }
});

use SimpleEventSheets\Settings;
use SimpleEventSheets\Events;

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class SimpleEventSheetsPlugin
{
    // phpcs:enable PSR1.Classes.ClassDeclaration.MissingNamespace

    private static $instance = null;

    public function __construct()
    {
        add_action('admin_menu', [$this, 'optionsMenu']);
        add_action('wp_enqueue_scripts', [$this, 'assets']);
        add_shortcode('simple_event_sheets', [$this, 'events']);
    }

    public function optionsMenu()
    {
        $dashboard = new Settings();
        $dashboard->createMenu(plugin_basename(__FILE__));
    }

    public function events($atts)
    {
        $event = new Events();
        return $event->renderEvents($atts);
    }

    public function assets()
    {
        if (!is_admin()) {
            $event = new Events();
            wp_enqueue_style("simple-event-sheets-css", plugin_dir_url(__FILE__) . "src/assets/css/simple-event-sheets.css", false, filemtime(plugin_dir_path(__FILE__) . "src/assets/css/simple-event-sheets.css"), false);
            wp_enqueue_script('simple-event-sheets-js', plugin_dir_url(__FILE__) . "src/assets/js/simple-event-sheets.js", ['jquery'], '1.0', true);
            wp_localize_script('simple-event-sheets-js', 'simpleEventSheetsParams', [
                'SHEET_ID' => esc_js(get_option('simple_event_sheets_sheet_id')),
                'SHEET_NAME' => esc_js(get_option('simple_event_sheets_sheet_name')),
                'API_KEY' => esc_js(get_option('simple_event_sheets_google_api_key')),
                'SHOW_PASSED_EVENTS' => get_option('simple_event_sheets_passed_events_checkbox'),
                'EVENTS' => $event->optionalGetEvents()
            ]);
        }
    }

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

function initializeSimpleEventSheetsPlugin()
{
    SimpleEventSheetsPlugin::getInstance();
}
add_action('plugins_loaded', 'initializeSimpleEventSheetsPlugin');
