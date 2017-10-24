<?php
    /*
    Plugin Name: LWR Events
    Plugin URI: http://www.lichtwellenreiter.ch
    Description: LWREvents ist ein Plugin, um Kurse und Anlässe eines Vereins auf der Webseite darzustellen. Es bietet den registrierten Nutzern ebenfalls die Möglichkeit, sich direkt anzumelden. Ein Widget für die kommenden Anlässe und ein Excel Export der Anmeldungen steht ebenfalls zur Verfügung.
    Author: licht.wellen.reiter
    Author URI: http://www.lichtwellenreiter.ch
    Version: 1.7.3.7
    */

    /**
     * Include all Classes
     * 1: Core Functions
     * 2: Custom Post Type
     * 3: List Widget
     * 4: Calendar Widget
     */
    define('LWR_PLUGIN_PATH', plugin_dir_path(__FILE__));
    include('core/LWREventsCore.php');                          // Core Functionalities
    include('core/LWREventsCPT.php');                           // Custom Post Type Functions
    include('core/LWREventsWidget.php');                        // EventsWidget Definition
    include('core/LWREventsCalendarWidget.php');                // Calendar Widget, not use yet
    include('core/LWREventsIcs.php');                           // ICS Creator, not in use yet
    include('core/PHPExcel.php');                               // Excel Generator
    include('core/pupdatechecker/plugin-update-checker.php');   // Plugin Update Checker

    if (!class_exists('LWREvents')) {
        class LWREvents {

            public $lwrCore;

            /**
             * LWREvents constructor.
             */
            function __construct() {

                // Initilaize LWREventsCore
                $lwrCore = new LWREventsCore();
                $this->setLwrCore($lwrCore);

                $lwrEventsCPT = new LWREventsCPT();

                register_activation_hook(__FILE__, array($this, 'lwr_events_activate'));
                register_deactivation_hook(__FILE__, array($this, 'lwr_events_deactivate'));
                //register_uninstall_hook(__FILE__, array($this, 'lwr_events_uninstall'));

                //Update Checker
                $puc = Puc_v4_Factory::buildUpdateChecker(
                    'https://github.com/lichtwellenreiter/lwrevents',
                    __FILE__,
                    'lwrevents',
                    1
                );
                $puc->setBranch('master');
            }


            /**
             * @param mixed $lwrCore
             */
            public function setLwrCore($lwrCore) {
                $this->lwrCore = $lwrCore;

                add_action('admin_enqueue_scripts', array($this, 'lwr_events_loadStylesAndJSBackend'));
                add_action('wp_enqueue_scripts', array($this, 'lwr_events_loadStylesAndJSFrontend'));
            }

            /**
             * LWREvents Plugin activation Hook
             */
            public function lwr_events_activate() {
                global $wpdb;
                $lwr_events_table = $wpdb->prefix . 'lwrevents_signin';

                if ($wpdb->get_var("show tables like '$lwr_events_table'") != $lwr_events_table) {
                    $sql = "CREATE TABLE `" . $wpdb->prefix . "lwrevents_signin` ( `id` INT(9) NOT NULL AUTO_INCREMENT , `eid` INT(5) NOT NULL , `uid` INT(5) NOT NULL , `status` INT(3) NOT NULL , `comment` TEXT NULL , `comment_date` DATETIME NULL , PRIMARY KEY (`id`)) ENGINE = MyISAM;";

                    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
                    dbDelta($sql);
                }
            }

            /**
             * LWREvents Plugin deactivation Hook
             */
            public function lwr_events_deactivate() {

                // Ajax gschmeus löschen.
                remove_action('wp_ajax_user_sign_event', array('LWREventsCore', 'user_sign_event'));
                remove_action('wp_ajax_nopriv_user_sign_event', array('LWREventsCore', 'user_sign_event'));

                remove_action('wp_ajax_update_sign_table', array('LWREventsCore', 'update_sign_table'));
                remove_action('wp_ajax_nopriv_update_sign_table', array('LWREventsCore', 'update_sign_table'));

            }

            public function lwr_events_uninstall() {
                global $wpdb;
                $wpdb->query("DROP TABLE '" . $wpdb->prefix . "lwrevents_signin'");
            }

            function lwr_events_widget_init() {
                $lwrEventsWidget = new LWREventsWidget();
                add_action('widgets_init', array($lwrEventsWidget, 'lwr_load_widget'));

                $lwrEventsCalWidget = new LWREventsCalendarWidget();
                add_action('widgets_init', array($lwrEventsCalWidget, 'lwr_load_cal_widget'));

                add_action('wp_head', array($this, 'add_ajax_library'));
            }

            /**
             * Adds the WordPress Ajax Library to the frontend.
             */
            public function add_ajax_library() {

                $html = '<script type="text/javascript">';
                $html .= 'var ajaxurl = "' . admin_url('admin-ajax.php') . '"';
                $html .= '</script>';

                echo $html;

            } // end add_ajax_library


            function lwr_events_loadStylesAndJSBackend() {
                wp_register_script('jqueryvalidate', plugin_dir_url(__FILE__) . 'views/assets/js/jqvalidate/jquery.validate.js');
                wp_enqueue_script('jqueryvalidate');
                wp_register_script('lwrevents-backend', plugin_dir_url(__FILE__) . 'views/assets/js/lwr-events-backend.min.js');
                wp_enqueue_script('lwrevents-backend');
                wp_enqueue_script('jquery-ui-datepicker');
                wp_enqueue_style('jquery-ui-datepicker', plugin_dir_url(__FILE__) . 'views/assets/css/jquery-ui.min.css');

            }

            function lwr_events_loadStylesAndJSFrontend() {
                //Load Fontawesome
                wp_enqueue_style('fontawesome', plugin_dir_url(__FILE__) . 'views/assets/css/font-awesome.min.css');
                wp_enqueue_style('lwrevent-style', plugin_dir_url(__FILE__) . 'views/assets/css/lwr-event-style.min.css');
                wp_localize_script('lwrevents', 'lwrevent', array('ajax_url' => admin_url('admin-ajax.php')));

                /**
                 * Calendar Scripts
                 */
                wp_register_script('lwrevents-ics', plugin_dir_url(__FILE__) . 'views/assets/js/ics.min.js');
                wp_enqueue_script('lwrevents-ics');
                wp_register_script('lwrevents-filesaver', plugin_dir_url(__FILE__) . 'views/assets/js/FileSaver.min.js');
                wp_enqueue_script('lwrevents-filesaver');
                wp_register_script('lwrevents-blob', plugin_dir_url(__FILE__) . 'views/assets/js/Blob.js');
                wp_enqueue_script('lwrevents-blob');

            }

            public static function lwr_frontview_templates($template) {

                //LWREvent Single Template laden
                if (is_singular('lwrevents')) {
                    $template = plugin_dir_path(__FILE__) . 'views/frontend/single-lwrevents.php';
                }

                return $template;
            }

            function lwr_custom_post_type_template($archive_template) {
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

// Instantiate new LWRPlugin Object
    if (class_exists('LWREvents')) {
        $lwrPluginObject = new LWREvents();
    }
    if (isset($lwrPluginObject)) {
        add_action('init', array($lwrPluginObject, 'lwr_events_activate'));


        $lwrPluginObject->lwr_events_widget_init();

        add_action('init', array('LWREventsCPT', 'lwr_events_cpt_config'), 0);

        add_filter('template_include', array('LWREvents', 'lwr_frontview_templates'), 0);
        add_filter('archive_template', array('LWREvents', 'lwr_custom_post_type_template'), 0);

        add_shortcode('lwrevents-list-future', array('LWREventsCore', 'lwrShortcodeListFuture'));
        add_shortcode('lwrevents-list', array('LWREventsCore', 'lwrShortcodeList'));

    }