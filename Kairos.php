<?php
/*
Plugin Name: Kairos
Plugin URI: https://github.com/0x49b/kairos
Description: Kairos ist ein Plugin, um Kurse und Anlässe eines Vereins auf der Webseite darzustellen. Es bietet den registrierten Nutzern ebenfalls die Möglichkeit, sich direkt anzumelden. Ein Widget für die kommenden Anlässe steht ebenfalls zur Verfügung.
Author: florian.thievent
Author URI: http://www.thievent.org
Version: 1.8.0.6
*/

/**
 * Include all Classes
 * 1: Core Functions
 * 2: Custom Post Type
 * 3: List Widget
 * 4: Calendar Widget
 */
define('KAIROS_PLUGIN_PATH', plugin_dir_path(__FILE__));
include('core/KairosCore.php');                          // Core Functionalities
include('core/KairosCPT.php');                           // Custom Post Type Functions
include('core/KairosWidget.php');                        // EventsWidget Definition
include('core/KairosCalendarWidget.php');                // Calendar Widget, not use yet

// Plugin Update Checker
require 'plugin-update-checker/plugin-update-checker.php';

use YahnisElsts\PluginUpdateChecker\v5\PucFactory;


if (!class_exists('Kairos')) {

    $puc = PucFactory::buildUpdateChecker(
        'https://github.com/0x49b/lwrevents/',
        __FILE__,
        'lwrevents'
    );

    //Set the branch that contains the stable release.
    $puc->setBranch('master');

    class Kairos
    {

        public $lwrCore;

        /**
         * Kairos constructor.
         */
        function __construct()
        {

            // Initialize KairosCore
            $lwrCore = new KairosCore();
            $this->setLwrCore($lwrCore);

            $lwrEventsCPT = new KairosCPT();

            register_activation_hook(__FILE__, array(__CLASS__, 'kairos_activate'));
            register_deactivation_hook(__FILE__, array(__CLASS__, 'kairos_deactivate'));
            register_uninstall_hook(__FILE__, array(__CLASS__, 'kairos_uninstall'));
        }


        /**
         * @param mixed $lwrCore
         */
        public function setLwrCore($lwrCore)
        {
            $this->lwrCore = $lwrCore;

            add_action('admin_enqueue_scripts', array($this, 'kairos_load_backend_assets'));
            add_action('wp_enqueue_scripts', array($this, 'kairos_load_frontend_assets'));
        }

        /**
         * Kairos Plugin activation Hook
         */
        public static function kairos_activate()
        {
            global $wpdb;
            $lwr_events_table = $wpdb->prefix . 'lwrevents_signin';

            if ($wpdb->get_var("show tables like '$lwr_events_table'") != $lwr_events_table) {
                $sql = "CREATE TABLE `" . $wpdb->prefix . "lwrevents_signin` ( `id` INT(9) NOT NULL AUTO_INCREMENT , `eid` INT(5) NOT NULL , `uid` INT(5) NOT NULL , `status` INT(3) NOT NULL , `comment` TEXT NULL , `comment_date` DATETIME NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;";

                require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                dbDelta($sql);
            }

            add_option('lwr_empty_events', '');
            add_option('lwr_events_contact_mail', '');
            add_option('lwr_signin_for_users', 0);
            add_option('lwr_future_max', 10);
            add_option('lwr_all_max', 10);
            add_option('lwr_archiv_max', 10);
        }

        /**
         * Kairos Plugin deactivation Hook
         */
        public static function kairos_deactivate()
        {

            // Ajax gschmeus löschen.
            remove_action('wp_ajax_user_sign_event', array('KairosCore', 'user_sign_event'));
            remove_action('wp_ajax_nopriv_user_sign_event', array('KairosCore', 'user_sign_event'));

            remove_action('wp_ajax_update_sign_table', array('KairosCore', 'update_sign_table'));
            remove_action('wp_ajax_nopriv_update_sign_table', array('KairosCore', 'update_sign_table'));

        }

        public static function kairos_uninstall()
        {
            global $wpdb;
            $wpdb->query("DROP TABLE '" . $wpdb->prefix . "lwrevents_signin'");

            delete_option('lwr_empty_events');
            delete_option('lwr_events_contact_mail');
            delete_option('lwr_signin_for_users');
            delete_option('lwr_future_max');
            delete_option('lwr_all_max');
            delete_option('lwr_archiv_max');
        }

        function kairos_widget_init()
        {
            $lwrEventsWidget = new KairosWidget();
            add_action('widgets_init', array($lwrEventsWidget, 'kairos_load_widget'));

            $lwrEventsCalWidget = new KairosCalendarWidget();
            add_action('widgets_init', array($lwrEventsCalWidget, 'kairos_load_cal_widget'));

            add_action('wp_head', array($this, 'add_ajax_library'));
        }

        /**
         * Adds the WordPress Ajax Library to the frontend.
         */
        public function add_ajax_library()
        {

            $html = '<script type="text/javascript">';
            $html .= 'var ajaxurl = "' . admin_url('admin-ajax.php') . '"';
            $html .= '</script>';

            echo $html;

        } // end add_ajax_library


        function kairos_load_backend_assets()
        {
            wp_register_script('jqueryvalidate', plugin_dir_url(__FILE__) . 'views/assets/js/jqvalidate/jquery.validate.js');
            wp_enqueue_script('jqueryvalidate');
            wp_register_script('kairos-backend', plugin_dir_url(__FILE__) . 'views/assets/js/lwr-events-backend.min.js');
            wp_enqueue_script('kairos-backend');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_style('jquery-ui-datepicker', plugin_dir_url(__FILE__) . 'views/assets/css/jquery-ui.min.css');

        }

        function kairos_load_frontend_assets()
        {
            wp_register_script('kairos-jquery', plugin_dir_url(__FILE__) . 'views/assets/js/jquery.min.js');
            wp_enqueue_script('kairos-jquery');

            //Load Fontawesome
            wp_enqueue_style('kairos-fontawesome', plugin_dir_url(__FILE__) . 'views/assets/css/font-awesome.min.css');
            wp_enqueue_style('kairos-style', plugin_dir_url(__FILE__) . 'views/assets/css/lwr-event-style.min.css');
            wp_localize_script('lwrevents', 'lwrevent', array('ajax_url' => admin_url('admin-ajax.php')));

            /**
             * Calendar Scripts
             */
            wp_register_script('kairos-ics', plugin_dir_url(__FILE__) . 'views/assets/js/ics.min.js');
            wp_enqueue_script('kairos-ics');
            wp_register_script('kairos-filesaver', plugin_dir_url(__FILE__) . 'views/assets/js/FileSaver.min.js');
            wp_enqueue_script('kairos-filesaver');
            wp_register_script('kairos-blob', plugin_dir_url(__FILE__) . 'views/assets/js/Blob.js');
            wp_enqueue_script('kairos-blob');

        }

        public static function kairos_frontview_templates($template)
        {

            //Kairos Event Single Template laden
            if (is_singular('lwrevents')) {
                $template = plugin_dir_path(__FILE__) . 'views/frontend/single-lwrevents.php';
            }

            return $template;
        }

        public static function kairos_custom_post_type_template($archive_template)
        {
            //Get Query Object for actual WP Query
            $qobj = get_queried_object();
            // Check for Taxonomy to display custom archive page
            if (is_archive() && $qobj->taxonomy == 'Sportart') {
                $archive_template = plugin_dir_path(__FILE__) . 'views/frontend/archive-lwrevents.php';
            }

            return $archive_template;
        }


    }
}

// Instantiate new Kairos Plugin Object
if (class_exists('Kairos')) {
    $kairosPluginObject = new Kairos();
}
if (isset($kairosPluginObject)) {
    add_action('init', array('Kairos', 'kairos_activate'));


    $kairosPluginObject->kairos_widget_init();

    add_action('init', array('KairosCPT', 'kairos_cpt_config'), 0);

    add_filter('template_include', array('Kairos', 'kairos_frontview_templates'), 0);
    add_filter('archive_template', array('Kairos', 'kairos_custom_post_type_template'), 0);

    // Keep old shortcodes for backwards compatibility
    add_shortcode('lwrevents-list-future', array('KairosCore', 'kairosShortcodeListFuture'));
    add_shortcode('lwrevents-list', array('KairosCore', 'kairosShortcodeList'));

    // Add new Kairos-branded shortcodes
    add_shortcode('kairos-list-future', array('KairosCore', 'kairosShortcodeListFuture'));
    add_shortcode('kairos-list', array('KairosCore', 'kairosShortcodeList'));

}

if (!function_exists('my_plugin_check_for_updates')) {

    function my_plugin_check_for_updates($update, $plugin_data, $plugin_file)
    {

        static $response = false;

        if (empty($plugin_data['UpdateURI']) || !empty($update))
            return $update;

        if ($response === false)
            $response = wp_remote_get($plugin_data['UpdateURI']);

        if (empty($response['body']))
            return $update;

        $custom_plugins_data = json_decode($response['body'], true);

        if (!empty($custom_plugins_data[$plugin_file]))
            return $custom_plugins_data[$plugin_file];
        else
            return $update;

    }

}
