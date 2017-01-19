<?php

/**
 * User:        Florian Thiévent
 * File:        LWRCore.php
 * Version:     1.0
 * Description: Core Functions of the Wordpress Plugin LWR Events
 */
class LWREventsCore
{

    function __construct() {
        //Admin Interface laden
        $this->registerAJAXFunctions();

        // Todo Admin Interface...
        // Admin Interface laden
        add_action('admin_menu', array($this, 'loadAdminInterface'));
    }

    /**
     * Return a list with all events ordered by now to the past
     * @return string
     */
    function lwrShortcodeList() {
        $lwr = new LWREventsCore();
        $args = array('post_type' => 'lwrevents',
                      'order' => $lwr->getSettingsFromDB('lwr_sort_list'),
                      'orderby' => 'meta_value',
                      'meta_key' => 'lwrDatumVonSQL',);

        $custom_posts = new WP_Query($args);
        $returnstring = '<table><tr><th>Datum</th><th>Anlass</th><th>Kommentare</th></tr>';


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
        }

        $returnstring .= '</table>';
        echo "<pre>";
        print_r($custom_posts->last_query);
        echo "</pre>";
        return $returnstring;
    }

    /**
     * Return a List with all Events in the Future
     * @return string
     */
    function lwrShortcodeListFuture() {
        $lwr = new LWREventsCore();
        $today = date('Y-m-d');

        $args = array('post_type' => 'lwrevents',
                      'meta_query' => array('relation' => 'AND',
                                            'lwrZeitVon' => array('key' => 'lwrZeitVon',
                                                                  'compare' => 'EXISTS',),
                                            'lwrDatumVonSQL' => array('key' => 'lwrDatumVonSQL',
                                                                   'compare' => '>=',
                                                                   'value' => $today),),
                      'orderby' => array('lwrDatumVonSQL' => $lwr->getSettingsFromDB('lwr_sort_list_future')));

        $custom_posts = new WP_Query($args);

        $returnstring = '<table><tr><th>Datum</th><th>Anlass</th><th>Kommentare</th></tr>';

        if ($custom_posts->have_posts()) {

            while ($custom_posts->have_posts()) {
                $custom_posts->the_post();
                $term = get_the_terms(get_the_ID(), 'Sportart');

                $bis = '';
                if ($lwr->getEventMeta(get_the_ID(), 'lwrDatumBis') != '') {
                    $bis = '-' . $lwr->getEventMeta(get_the_ID(), 'lwrDatumBis');
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
        }

        $returnstring .= '</table>';
        echo "<pre>";
        print_r($custom_posts->last_query);
        echo "</pre>";
        return $returnstring;
    }

    /**
     * Funktion Admin Interface laden
     */
    function loadAdminInterface() {
        add_submenu_page('edit.php?post_type=lwrevents', 'Einstellungen', 'Einstellungen', 'manage_options', 'lwr-settings', array($this, 'lwr_settings_page'));
    }

    function lwr_settings_page() {
        return (include_once(LWR_PLUGIN_PATH . '/views/backend/lwr-settings-view.php'));
    }

    /**
     * Ajax Funktionen im Core registrieren
     */
    function registerAJAXFunctions() {
        //add_action( 'wp_ajax_signInUserForEvent', array($this, 'signInUserForEvent') );
        //add_action( 'wp_ajax_nopriv_signInUserForEvent', array($this, 'signInUserForEvent') );

        add_action('wp_ajax_user_sign_event', array($this, 'user_sign_event'));
        add_action('wp_ajax_nopriv_user_sign_event', array($this, 'user_sign_event'));

        add_action('wp_ajax_update_sign_table', array($this, 'update_sign_table'));
        add_action('wp_ajax_nopriv_update_sign_table', array($this, 'update_sign_table'));
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

        $returnarray = array('ja' => array('count' => count($cJa), 'users' => $this->getUsernamesForTable($cJa)),
                             'evtl' => array('count' => count($cEvtl), 'users' => $this->getUsernamesForTable($cEvtl)),
                             'nein' => array('count' => count($cNein), 'users' => $this->getUsernamesForTable($cNein)));

        echo json_encode($returnarray, JSON_FORCE_OBJECT);
        die();
    }

    private function getDataForUserTable($eventID, $status) {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM " . $wpdb->prefix . "lwrevents_signin lwr
JOIN " . $wpdb->prefix . "users us ON lwr.uid = us.ID
WHERE eid = '" . $eventID . "' AND status = '" . $status . "'", ARRAY_A);
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
            $wpdb->insert($lwrtable, array('eid' => $eventID,
                                           'uid' => $userID,
                                           'status' => $signInState));
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
     * Gibt eine Metainformation für einen Post als String zurück
     * @param $id
     * @param $key
     * @return mixed
     */
    function getEventMeta($id, $key) {

        return get_post_meta($id, $key, true);
    }

    /**
     * DateString für Frontend aus Datenbank zusammenbasteln
     * @param $dayA
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
     * @param $eventID
     * @param $status
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
     * @param $postID
     * @param $userID
     * @return null|string
     */
    function getEventUserState($postID, $userID) {
        global $wpdb;

        $state = $wpdb->get_var("SELECT status FROM " . $wpdb->prefix . "lwrevents_signin WHERE uid = '" . $userID . "' AND eid = '" . $postID . "'");
        return $state;
    }

    /**
     * Einstellungen in der Datenbank speichern
     * @param $post
     */
    function saveSettingsInDB($post) {
        global $wpdb;

        $wpdb->replace($wpdb->prefix . 'options', array('option_name' => 'lwr_sort_list_future',
                                                        'option_value' => $post['lwr_sort_list_future']));

        $wpdb->replace($wpdb->prefix . 'options', array('option_name' => 'lwr_sort_list',
                                                        'option_value' => $post['lwr_sort_list']));

    }

    /**
     * Einstellungen aus der Datenbank laden
     * @return String
     */
    function getSettingsFromDB($option_name) {
        global $wpdb;
        $setting = $wpdb->get_row("SELECT * from " . $wpdb->prefix . "options WHERE option_name = '" . $option_name . "'", ARRAY_A);

        return $setting['option_value'];
    }

    /**
     * Select Liste für die Einstellungen laden und entsprechend prüfen welches Feld
     * @param $list_name
     * @return String $list_entry
     */
    function getSettingsSelectList($list_name) {

        $setting = $this->getSettingsFromDB($list_name);

        var_dump($setting);

        if ($setting == 'DESC') {
            $list_entry = '<option value="DESC" selected>absteigend</option><option value="ASC">aufsteigend</option>';
        } elseif($setting=='ASC') {
            $list_entry = '<option value="DESC">absteigend</option><option value="ASC" selected>aufsteigend</option>';
        }else{
            $list_entry = '<option value="DESC">absteigend</option><option value="ASC">aufsteigend</option>';
        }
        return $list_entry;
    }

}