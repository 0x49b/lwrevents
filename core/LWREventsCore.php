<?php

/**
 * User:        Florian Thiévent
 * File:        LWRCore.php
 * Version:     1.0
 * Description: Core Functions of the Wordpress Plugin LWR Events
 */
class LWREventsCore {

    function __construct() {
        // load Ajax Functionality
        $this->registerAJAXFunctions();

        // Todo Admin Interface...
        // Admin Interface laden
        add_action('admin_menu', array($this, 'loadAdminInterface'));
    }

    /**
     * Ajax Funktionen im Core registrieren
     */
    function registerAJAXFunctions() {
        //add_action( 'wp_ajax_signInUserForEvent', array($this, 'signInUserForEvent') );
        //add_action( 'wp_ajax_nopriv_signInUserForEvent', array($this, 'signInUserForEvent') );

        add_action('wp_ajax_user_sign_event', array($this, 'user_sign_event'));
        add_action('wp_ajax_nopriv_user_sign_event', array($this, 'user_sign_event'));
        add_action('wp_ajax_nopriv_generate_calendar', array($this, 'generate_calendar'));
        add_action('wp_ajax_nopriv_generate_calendar_category', array($this, 'generate_calendar_category'));


        add_action('wp_ajax_update_sign_table', array($this, 'update_sign_table'));
        add_action('wp_ajax_nopriv_update_sign_table', array($this, 'update_sign_table'));
        add_action('wp_ajax_generate_calendar', array($this, 'generate_calendar'));
        add_action('wp_ajax_generate_calendar_category', array($this, 'generate_calendar_category'));
    }

    /**
     * Return a List with all Events in the Future
     * added correct ordering key, finally
     * @return string
     */

    static function lwrShortcodeListFuture() {
        $lwr = new LWREventsCore();
        $today = date('Y-m-d');
        $todayUnix = strtotime(date('d.m.Y H:i:s'));

        $args = array(
            'post_type' => 'lwrevents',
            'posts_per_page' => $lwr->getSettingsFromDB('lwr_future_max'),
            'meta_query' => array(
                'relation' => 'AND',
                'lwrZeitVon' => array(
                    'key' => 'lwrZeitVon',
                    'compare' => 'EXISTS',
                ),
                'lwrDatumVonSQL' => array(
                    'key' => 'lwrDatumVonSQL',
                    'compare' => '>=',
                    'value' => $today,
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'lwrDatumZeitVonUnix',
            'order' => $lwr->getSettingsFromDB('lwr_sort_list_future'),
        );

        $custom_posts = new WP_Query($args);

        $returnstring = '<table id="agendaTableFuture"><thead><tr><th>Datum</th><th>Anlass</th><th>Kommentare</th></tr></thead><tbody>';

        if ($custom_posts->have_posts()) {

            while ($custom_posts->have_posts()) {
                $custom_posts->the_post();
                $term = get_the_terms(get_the_ID(), 'Sportart');

                $bis = '';
                if ($lwr->getEventMeta(get_the_ID(), 'lwrDatumBis') != '') {
                    $bis = '-<br/>' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumBis');
                }

                $returnstring .= '
                <tr><td>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumVon') . $bis . ' 
                </td>
                <td>
                <strong><a href="' . get_the_permalink() . '">' . $term[0]->name . ' : ' . get_the_title() . '</a></strong><br/>
                ' . get_the_excerpt() . '
                </td>
                <td>
                <a href="' . get_comments_link(get_the_ID()) . '">' . get_comments_number() . '</a>
                </td>
                </tr>';
            }
        } else {

            $returnstring .= "<tr><td colspan='3'>" . $lwr->getSettingsFromDB('lwr_empty_events') . "</td></tr>";

        }

        $returnstring .= '</tbody></table>';

        return $returnstring;
    }

    /**
     * Return a list with all events ordered by now to the past
     * @return string
     */
    static function lwrShortcodeList() {
        $lwr = new LWREventsCore();
        $args = array(
            'post_type' => 'lwrevents',
            'order' => $lwr->getSettingsFromDB('lwr_sort_list'),
            'orderby' => 'meta_value',
            'meta_key' => 'lwrDatumVonSQL',
            'posts_per_page' => $lwr->getSettingsFromDB('lwr_all_max'),
        );

        $custom_posts = new WP_Query($args);
        $returnstring = '<table id="agendaTable"><thead><tr><th>Datum</th><th>Anlass</th><th>Kommentare</th></tr><thead><tbody>';


        if ($custom_posts->have_posts()) {

            while ($custom_posts->have_posts()) {
                $custom_posts->the_post();
                $term = get_the_terms(get_the_ID(), 'Sportart');
                $name = '';

                (!$term) ? $name = '???' : $name = $term[0]->name;

                $returnstring .= '
                <tr><td>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumVon') . ' <br/>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumBis') . '
                </td>
                <td>
                
                <strong><a href="' . get_the_permalink() . '">' . $name . ' : ' . get_the_title() . '</a></strong><br/>
                ' . get_the_excerpt() . '
                </td>
                <td>
                <a href="' . get_comments_link(get_the_ID()) . '">' . get_comments_number() . '</a>
                </td>
                </tr>';
            }
        } else {

            $returnstring .= "<tr><td colspan='3'>" . $lwr->getSettingsFromDB('lwr_empty_events') . "</td></tr>";

        }

        $returnstring .= '</tbody></table>';

        return $returnstring;
    }

    /**
     * Get a list for an archive page with category
     * @param $category
     * @return string
     */
    function lwrGetArchiveForCategory($category) {
        $lwr = new LWREventsCore();
        $today = date('Y-m-d');
        $todayUnix = strtotime(date('d.m.Y H:i:s'));

        $custom_posts = new WP_Query(array(
            'post_type' => 'lwrevents',
            'order' => $lwr->getSettingsFromDB('lwr_sort_list_archive'),
            'orderby' => 'meta_value',
            'meta_key' => 'lwrDatumVonSQL',
            'posts_per_page' => $lwr->getSettingsFromDB('lwr_archiv_max'),
            'tax_query' => array(
                array(
                    'taxonomy' => 'Sportart',
                    'field' => 'slug',
                    'terms' => $category,
                ),
            ),
            'meta_query' => array(
                'relation' => 'AND',
                'lwrZeitVon' => array(
                    'key' => 'lwrZeitVon',
                    'compare' => 'EXISTS',
                ),
                'lwrDatumVonSQL' => array(
                    'key' => 'lwrDatumVonSQL',
                    'compare' => '>=',
                    'value' => $today,
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'lwrDatumZeitVonUnix',
        ));
        $returnstring = '<table id="agendaTable"><thead><tr><th>Datum</th><th>Anlass</th><th>Kommentare</th></tr><thead><tbody>';


        if ($custom_posts->have_posts()) {

            while ($custom_posts->have_posts()) {
                $custom_posts->the_post();
                $term = get_the_terms(get_the_ID(), 'Sportart');
                $returnstring .= '
                <tr><td>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumVon') . ' <br/>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumBis') . '
                </td>
                <td>
                <strong><a href="' . get_the_permalink() . '">' . $term[0]->name . ' : ' . get_the_title() . '</a></strong><br/>
                ' . get_the_excerpt() . '
                </td>
                <td>
                <a href="' . get_comments_link(get_the_ID()) . '">' . get_comments_number() . '</a>
                </td>
                </tr>';
            }
        } else {

            $returnstring .= "<tr><td colspan='3'>" . $lwr->getSettingsFromDB('lwr_empty_events') . "</td></tr>";

        }

        $returnstring .= '</tbody></table>';

        return $returnstring;
    }

    /**
     * Einstellungen aus der Datenbank laden
     * @return String
     */
    function getSettingsFromDB($option_name) {
        //global $wpdb;
        //$setting = $wpdb->get_row("SELECT * FROM " . $wpdb->prefix . "options WHERE option_name = '" . $option_name . "'", ARRAY_A);
        return get_option($option_name, '');
    }

    /**
     * Gibt eine Metainformation für einen Post als String zurück
     *
     * @param $id
     * @param $key
     *
     * @return mixed
     */
    function getEventMeta($id, $key) {

        return get_post_meta($id, $key, true);
    }

    /**
     * Funktion Admin Interface laden
     */
    function loadAdminInterface() {
        add_submenu_page('edit.php?post_type=lwrevents', 'Einstellungen', 'Einstellungen', 'manage_options', 'lwr-settings', array(
            $this, 'lwr_settings_page'));
    }

    function lwr_settings_page() {
        return (include_once(LWR_PLUGIN_PATH . '/views/backend/lwr-settings-view.php'));
    }


    /**
     * Generate .ics for Event
     * @param $post_id
     */
    function generate_calendar() {

        $postid = $_GET['eventID'];
        $post = get_post($postid, ARRAY_A, 'raw');
        $postmeta = get_post_meta($postid);

        $event = array(
            'postinfo' => $post,
            'postmeta' => $postmeta,
        );

        echo json_encode($event);
        die();

    }

    /**
     * Generate .ics for Event
     * @param $post_id
     */
    function generate_calendar_category() {

        $category = $_GET['category'];

        $lwr = new LWREventsCore();
        $today = date('Y-m-d');
        $todayUnix = strtotime(date('d.m.Y H:i:s'));

        $custom_posts = new WP_Query(array(
            'post_type' => 'lwrevents',
            'order' => $lwr->getSettingsFromDB('lwr_sort_list_archive'),
            'orderby' => 'meta_value',
            'meta_key' => 'lwrDatumVonSQL',
            'posts_per_page' => $lwr->getSettingsFromDB('lwr_archiv_max'),
            'tax_query' => array(
                array(
                    'taxonomy' => 'Sportart',
                    'field' => 'slug',
                    'terms' => $category,
                ),
            ),
            'meta_query' => array(
                'relation' => 'AND',
                'lwrZeitVon' => array(
                    'key' => 'lwrZeitVon',
                    'compare' => 'EXISTS',
                ),
                'lwrDatumVonSQL' => array(
                    'key' => 'lwrDatumVonSQL',
                    'compare' => '>=',
                    'value' => $today,
                ),
            ),
            'orderby' => 'meta_value',
            'meta_key' => 'lwrDatumZeitVonUnix',
        ));

        $postarr = array();

        if ($custom_posts->have_posts()) {

            while ($custom_posts->have_posts()) {
                $custom_posts->the_post();
                $term = get_the_terms(get_the_ID(), 'Sportart');
                $postarr[] .= '
                <tr><td>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumVon') . ' <br/>
                    ' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumBis') . '
                </td>
                <td>
                <strong><a href="' . get_the_permalink() . '">' . $term[0]->name . ' : ' . get_the_title() . '</a></strong><br/>
                ' . get_the_excerpt() . '
                </td>
                <td>
                <a href="' . get_comments_link(get_the_ID()) . '">' . get_comments_number() . '</a>
                </td>
                </tr>';
            }
        }

        //echo json_encode($custom_posts->posts);
        echo json_encode($postarr);
        die();

    }

    /**
     * Nach der Anmeldung die Tabelle der Anmeldungen aktualisieren.
     * Ajax Call update_sign_table
     */
    function update_sign_table() {
        $eventID = $_POST['eventID'];

        $cJa = $this->getDataForUserTable($eventID, 2);
        $cEvtl = $this->getDataForUserTable($eventID, 1);
        $cNein = $this->getDataForUserTable($eventID, 0);

        $returnarray = array(
            'ja' => array('count' => count($cJa), 'users' => $this->getUsernamesForTable($cJa)),
            'evtl' => array('count' => count($cEvtl), 'users' => $this->getUsernamesForTable($cEvtl)),
            'nein' => array('count' => count($cNein), 'users' => $this->getUsernamesForTable($cNein)),
        );

        echo json_encode($returnarray, JSON_FORCE_OBJECT);
        die();
    }

    private function getDataForUserTable($eventID, $status) {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "lwrevents_signin lwr JOIN " . $wpdb->prefix . "users us ON lwr.uid = us.ID WHERE eid = '" . $eventID . "' AND status = '" . $status . "'", ARRAY_A);
    }

    private function getUsernamesForTable($aData) {
        $retarr = '';
        $count = count($aData);
        $i = 0;
        foreach ($aData as $data) {
            $i++;

            if ($count === $i) {
                $retarr .= $data['user_login'];
            } else {
                $retarr .= $data['user_login'] . ', ';
            }

        }

        return $retarr;
    }

    /**
     * Anmeldung des Users in der DB speichern. Dies ist ein AJAX Request mit user_sign_event
     */
    function user_sign_event() {
        global $wpdb;

        $signInState = $_POST['signInState'];
        $userID = $_POST['userID'];
        $eventID = $_POST['eventID'];

        $lwrtable = $wpdb->prefix . 'lwrevents_signin';


        $check = $wpdb->get_var("SELECT COUNT(eid) FROM " . $lwrtable . " WHERE uid = '" . $userID . "' AND eid = '" . $eventID . "'");


        if ($check == 0) {
            $wpdb->insert($lwrtable, array(
                'eid' => $eventID,
                'uid' => $userID,
                'status' => $signInState,
            ));
        } else {
            $wpdb->query("UPDATE " . $lwrtable . " SET status = '" . $signInState . "' WHERE uid = '" . $userID . "' AND eid = '" . $eventID . "'");
        }

        $this->sendEmail($eventID, $userID, $signInState);

        echo json_encode($check);
        die();
    }

    private function sendEmail($userID, $eventID, $status) {

        global $wpdb;
        $adminMail = $this->getKursleiterEmail($eventID);
        $post = $wpdb->query("SELECT post_title FROM " . $wpdb->prefix . "posts WHERE ID='" . $eventID . "'", ARRAY_A);
        $user = get_userdata($userID);

        $subject = 'Anmeldung an Kurs/Anlass: ' . $post['post_title'];
        $message = $user->user_nicename . ' hat sich angemeldet oder die Anmeldung geändert.';

        foreach ($adminMail as $admin) {
            wp_mail($admin, $subject, $message);
        }
    }

    private function getKursleiterEmail($eventID) {

        $adminMailraw = $this->getEventMeta($eventID, 'lwrMailOK');
        $adminSplit = explode(',', $adminMailraw);

        return $adminSplit;
    }

    /**
     * Gibt MetaInformationen eines Post als echo zurück
     *
     * @param $id
     * @param $key
     */
    function eventMeta($id, $key) {
        echo get_post_meta($id, $key, true);
    }

    function checkSignInForEvent($eventID) {
        global $wpdb;

        $maxNum = $this->getEventMeta($eventID, 'lwrMaxTN');
        $countNum = $wpdb->get_var("SELECT COUNT(eid) FROM " . $wpdb->prefix . "lwrevents_signin WHERE eid = '" . $eventID . "' AND status != '0'");

        if ($countNum == $maxNum) {
            return true;
        } else {
            return false;
        }

    }

    /**
     * Gibt den Zeit String für einen Event als echo zurück
     *
     * @param $eventID
     *
     * @return String
     */
    function eventTime($eventID) {
        echo "CLOCK";
    }

    /**
     * Gibt den Zeit String für einen Event zurück
     *
     * @param $eventID
     *
     * @return String
     */
    function getEventTime($eventID) {

        $dateFrom = $this->getEventMeta($eventID, 'lwrDatumVon');
        $timeFrom = $this->getEventMeta($eventID, 'lwrZeitVon');
        $dateTil = $this->getEventMeta($eventID, 'lwrDatumBis');
        $timeTil = $this->getEventMeta($eventID, 'lwrZeitBis');

        $retstr = '';

        if ($dateFrom != '' && $timeFrom != '' and $timeTil != '' && $dateTil != '') {
            $retstr = $dateFrom . ' ' . $timeFrom . ' Uhr - ' . $dateTil . ' ' . $timeTil . ' Uhr';
        } elseif ($dateFrom != '' && $timeFrom != '' and $timeTil != '') {
            $retstr = $dateFrom . ', ' . $timeFrom . ' - ' . $timeTil . ' Uhr';
        } elseif ($dateFrom != '' && $timeFrom != '') {
            $retstr = $dateFrom . ', ' . $timeFrom . ' Uhr';
        }

        return $retstr;
    }

    /**
     * DateString für Frontend aus Datenbank zusammenbasteln
     *
     * @param $dayA
     *
     * @return string
     */
    function getDayString($dayA) {

        $days = explode(',', $dayA);
        $daysString = '';
        for ($i = 0; $i < count($days); $i++) {
            switch ($days[$i]) {
                case 'mo':
                    $daysString .= 'Montagx';
                    break;
                case 'di':
                    $daysString .= 'Dienstagx';
                    break;
                case 'mi':
                    $daysString .= 'Mittwochx';
                    break;
                case 'do':
                    $daysString .= 'Donnerstagx';
                    break;
                case 'fr':
                    $daysString .= 'Freitagx';
                    break;
                case 'sa':
                    $daysString .= 'Samstagx';
                    break;
                case 'so':
                    $daysString .= 'Sonntagx';
                    break;
            }

        }

        $day = explode('x', $daysString);
        $comma = count($day) - 1;

        $returnString = 'Jeweils ';

        for ($i = 0; $i < $comma; $i++) {
            if ($i == 0) {
                $returnString .= $day[$i];
            } elseif ($i > 0 && $i < $comma) {
                $returnString .= ', ' . $day[$i];
            } elseif ($i == $comma) {
                $returnString .= ' und ' . $day[$i];
            }
        }

        return $returnString;

    }

    /**
     * Alle Anmeldungen aus der Datenbank für einen Event holen.
     *
     * @param $eventID
     * @param $status
     *
     * @return array|null|object
     */
    function getSigninUsersForEventAndStatus($eventID, $status) {
        global $wpdb;
        $userstring = '';

        //Status: 0=nein, 1=vielleicht, 2=ja
        $signquery = $wpdb->get_results("SELECT lwr.id,us.display_name FROM " . $wpdb->prefix . "lwrevents_signin lwr
JOIN " . $wpdb->prefix . "users us ON lwr.uid = us.ID
WHERE lwr.status = '" . $status . "' AND lwr.eid = '" . $eventID . "'", ARRAY_A);

        $num = count($signquery);
        $loop = 1;

        foreach ($signquery as $user) {

            if ($num != $loop) {
                $userstring .= $user['display_name'] . ', ';
            } else {
                $userstring .= $user['display_name'];
            }
            $loop++;
        }

        return $userstring;
    }

    /**
     * @param $eventID
     * @param $status
     *
     * @return int
     */
    function getSigninCountForEventAndStatus($eventID, $status) {
        global $wpdb;

        //Status: 0=nein, 1=vielleicht, 2=ja
        $signquery = $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "lwrevents_signin lwr
JOIN " . $wpdb->prefix . "users us ON lwr.uid = us.ID
WHERE lwr.status = '" . $status . "' AND lwr.eid = '" . $eventID . "'");

        return count($signquery);
    }

    /**
     * Status einer Anmeldung in der Datenbank suchen
     *
     * @param $postID
     * @param $userID
     *
     * @return null|string
     */
    function getEventUserState($postID, $userID) {
        global $wpdb;

        $state = $wpdb->get_var("SELECT status FROM " . $wpdb->prefix . "lwrevents_signin WHERE uid = '" . $userID . "' AND eid = '" . $postID . "'");

        return $state;
    }

    /**
     * Einstellungen in der Datenbank speichern
     *
     * @param $post
     */
    function saveSettingsInDB($post) {
        global $wpdb;


        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_sort_list_future',
            'option_value' => $post['lwr_sort_list_future'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_future_max',
            'option_value' => $post['lwr_future_max'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_archiv_max',
            'option_value' => $post['lwr_archiv_max'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_all_max',
            'option_value' => $post['lwr_all_max'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_sort_list',
            'option_value' => $post['lwr_sort_list'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_sort_list_archive',
            'option_value' => $post['lwr_sort_list_archive'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_events_contact_mail',
            'option_value' => $post['lwr_events_contact_mail'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_empty_events',
            'option_value' => $post['lwr_empty_events'],
        ));

        $wpdb->replace($wpdb->prefix . 'options', array(
            'option_name' => 'lwr_events_contact_mail',
            'option_value' => $post['lwr_events_contact_mail'],
        ));

        if ($post['lwr_signin_for_users'] == 1) {
            $wpdb->replace($wpdb->prefix . 'options', array(
                'option_name' => 'lwr_signin_for_users',
                'option_value' => $post['lwr_signin_for_users'],
            ));
        } else {
            $wpdb->replace($wpdb->prefix . 'options', array(
                'option_name' => 'lwr_signin_for_users',
                'option_value' => 0,
            ));
        }

    }

    /**
     * Select Liste für die Einstellungen laden und entsprechend prüfen welches Feld
     *
     * @param $list_name
     *
     * @return String $list_entry
     */
    function getSettingsSelectList($list_name) {

        $setting = $this->getSettingsFromDB($list_name);

        var_dump($setting);

        if ($setting == 'DESC') {
            $list_entry = '<option value="DESC" selected>absteigend</option><option value="ASC">aufsteigend</option>';
        } elseif ($setting == 'ASC') {
            $list_entry = '<option value="DESC">absteigend</option><option value="ASC" selected>aufsteigend</option>';
        } else {
            $list_entry = '<option value="DESC">absteigend</option><option value="ASC">aufsteigend</option>';
        }

        return $list_entry;
    }

    /**
     * Liest die Checkboxstate aus und gibt diesen zurück
     *
     * @param $state
     */
    function getCheckboxState($checkbox) {

        $state = $this->getSettingsFromDB($checkbox);

        if ($state == 1) {
            return 'checked';
        } else {
            return '';
        }

    }

    /**
     * Status einer Checkbox in den Settings als Boolsche Variable zurück liefern
     *
     * @param $checkbox
     *
     * @return bool
     */
    function getCheckboxStateBool($checkbox) {

        $state = $this->getSettingsFromDB($checkbox);

        if ($state == 1) {
            return true;
        } else {
            return false;
        }

    }

    function getPagination() {

        global $wp_query;

        $big = 999999999; // need an unlikely integer

        $paginate_links = paginate_links(array(
            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
            'format' => '?paged=%#%',
            'current' => max(1, get_query_var('paged')),
            'total' => $wp_query->max_num_pages,
            'next_text' => '&raquo;',
            'prev_text' => '&laquo',
            'add_args' => false,
        ));

        // Display the pagination if more than one page is found
        if ($paginate_links) : ?>

            <div class="post-pagination clearfix">
                <?php echo $paginate_links; ?>
            </div>

        <?php
        endif;

    }


    /**
     * Get the metadata for a post an display them on the page
     */
    function lwrDisplayPostMeta() {

        // Get Theme Options from Database
        $theme_options = array('meta_date' => true, 'meta_author' => true);

        // Display Date unless user has deactivated it via settings
        if (isset($theme_options['meta_date']) and $theme_options['meta_date'] == true) : ?>

            <span class="meta-date sep">
			<?php printf('<a href="%1$s" title="%2$s" rel="bookmark"><time class="entry-date published updated" datetime="%3$s">%4$s</time></a>',
                esc_url(get_permalink()),
                esc_attr(get_the_time()),
                esc_attr(get_the_date('c')),
                esc_html(get_the_date())
            );
            ?>
			</span>

        <?php endif;

        // Display Author unless user has deactivated it via settings
        if (isset($theme_options['meta_author']) and $theme_options['meta_author'] == true) : ?>

            <span class="meta-author sep">
			<?php printf('<span class="author vcard"><a class="fn" href="%1$s" title="%2$s" rel="author">%3$s</a></span>',
                esc_url(get_author_posts_url(get_the_author_meta('ID'))),
                esc_attr(sprintf(__('View all posts by %s', 'dynamic-news-lite'), get_the_author())),
                get_the_author()
            );
            ?>
			</span>

        <?php endif;

        if (comments_open()) : ?>

            <span class="meta-comments">
				<?php comments_popup_link(__('Leave a comment', 'dynamic-news-lite'), __('One comment', 'dynamic-news-lite'), __('% comments', 'dynamic-news-lite')); ?>
			</span>

        <?php endif; ?>

        <?php
        edit_post_link('Edit Event');
    }

}