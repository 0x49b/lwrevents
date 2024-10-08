<?php
    /**
     * Template Name: LWREvents
     * File:          single-lwrevents.php
     * Project:       LWREvents
     * Version:       1.8.0.6
     * Description:   Displays a LWR Event
     */

    get_header(); ?>

    <style>
        .lwr-event-detail-table {
            border: none !important;
        }

        .lwr-event-detail-table,
        .lwr-event-detail-table tr,
        .lwr-event-detail-table tr td,
        .lwr-event-detail-user-table,
        .lwr-event-detail-user-table tr,
        .lwr-event-detail-user-table tr td {
            border: none;
        }

        .lwr-event-detail-user-table tr {
            line-height: 12px;
        }

        .lwr-event-detail-user-table tr td {
            font-size: 12px;
        }

        .lwr-event-detail-user-table-first {
            background: rgba(0, 205, 0, 0.27);
        }

        .lwr-event-detail-user-table-second {
            background: rgba(205, 178, 0, 0.27);
        }

        .lwr-event-detail-user-table-third {
            background: rgba(205, 62, 0, 0.27);
        }

        .lwr-event-detail-user-table-number {
            width: 10px;
            text-align: right;
        }

        .lwr-event-detail-user-table-title {
            width: 135px;
        }

        .lwr-comments {
            margin-bottom: 20px;
        }

    </style>

    <div id="content" class="primary" role="main">

        <?php 
            $loop = new WP_Query(array('post_type' => 'lwrevents',));
            $lwr = new LWREventsCore($post->ID);
        ?>

        <?php if (have_posts()) : while (have_posts()) : the_post();?>

            <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
                <h1 class="entry-title post-title"><?php echo get_the_term_list($post->ID, 'Sportart', '', ', ', '') . ': ' . get_the_title(); ?></h1>
                <div class="entry-meta postmeta clearfix"><?php $lwr->lwrDisplayPostMeta(); ?></div>
                <div class="entry clearfix">
                    <?php the_content(); ?>
                    <h4>Details</h4>
                    <table class="lwr-event-detail-table">
                        <?php echo $lwr->getEventTime($post->ID) . '<br/>'; ?>
                        <br/>
                        <?php
                            $days = $lwr->getEventMeta($post->ID, 'lwrTage');
                            if ($days != '') {
                                ?>
                                <small><?php echo $lwr->getDayString($days); ?></small>
                                <?php
                            }
                        ?>
                        </td>
                        </tr>
                        <tr>
                            <td>Ort</td>
                            <td><?php $lwr->eventMeta($post->ID, 'lwrOrt'); ?></td>
                        </tr>

                        <?php

                            if ($lwr->getEventMeta($post->ID, 'lwrOK')) {
                                ?>
                                <tr>
                                    <td>Organisation</td>
                                    <td><?php $lwr->eventMeta($post->ID, 'lwrOK'); ?></td>
                                </tr>
                                <?php
                            }

                            if ($lwr->getEventMeta($post->ID, 'lwrMaxTN')) {
                                ?>
                                <tr>
                                    <td>max. Teilnehmer</td>
                                    <td><?php $lwr->eventMeta($post->ID, 'lwrMaxTN'); ?></td>
                                </tr>
                                <?php
                            }

                            if ($lwr->getEventMeta($post->ID, 'lwrVoraussetzung')) {
                                ?>
                                <tr>
                                    <td>Voraussetzungen</td>
                                    <td><?php $lwr->eventMeta($post->ID, 'lwrVoraussetzung'); ?><?php ?></td>
                                </tr>
                                <?php
                            }

                            if ($lwr->getEventMeta($post->ID, 'lwrAusruestung')) {
                                ?>
                                <tr>
                                    <td>Ausrüstung</td>
                                    <td><?php $lwr->eventMeta($post->ID, 'lwrAusruestung'); ?></td>
                                </tr>
                                <?php
                            }

                            if (new DateTime() < new DateTime($lwr->getEventMeta($post->ID, 'lwrAnmelden'))) {


                                ?>
                                <tr>
                                    <td>Anmelden bis</td>
                                    <td>
                                        <?php $lwr->eventMeta($post->ID, 'lwrAnmelden'); ?>
                                    </td>
                                </tr>

                                <?php if (is_user_logged_in()) {

                                    if ($lwr->checkSignInForEvent($post->ID) == false) {


                                        $state = $lwr->getEventUserState($post->ID, get_current_user_id());

                                        $ja = $evtl = $nein = '';

                                        switch ($state) {
                                            case '0':
                                                $nein = 'checked';
                                                break;
                                            case '1':
                                                $evtl = 'checked';
                                                break;
                                            case '2':
                                                $ja = 'checked';
                                                break;
                                            default:
                                                $ja = $evtl = $nein = '';
                                                break;
                                        }
                                        ?>
                                        <tr>
                                            <td>
                                                <label><input type="radio" class="signInVal" name="signInState[]"
                                                                value="2" <?php echo $ja; ?>/> Ja</label>&nbsp;&nbsp;
                                                <label><input type="radio" class="signInVal" name="signInState[]"
                                                                value="1" <?php echo $evtl; ?>/> Evtl.</label>&nbsp;&nbsp;
                                                <label><input type="radio" class="signInVal" name="signInState[]"
                                                                value="0" <?php echo $nein; ?>/> Nein</label>
                                            </td>
                                            <td><a id="lwrSignInLink"
                                                    href="#"><?php ($state != '') ? print('Änderung speichern') : print('anmelden'); ?></a>
                                            </td>
                                        </tr>
                                        <?php
                                    } else {
                                        ?>
                                        <tr>
                                            <td colspan="2">Anmeldung ist geschlossen, die maximale Teilnehmerzahl
                                                ist
                                                erreicht.
                                            </td>
                                        </tr>
                                        <?php
                                    }

                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="2">Die Anmeldung steht nur Mitgliedern zur Verfügung.</td>
                                    </tr>
                                    <?php

                                }
                            } else {
                                ?>
                                <tr>
                                    <td colspan="2">Anmeldefrist ist abgelaufen.</td>
                                </tr>
                                <?php
                            }
                        ?>
                    </table>

                    <?php if ($lwr->getCheckboxStateBool('lwr_signin_for_users') == true || is_user_logged_in()) { ?>

                        <table class="lwr-event-detail-user-table">
                            <tr class="lwr-event-detail-user-table-first">
                                <td class="lwr-event-detail-user-table-number"
                                    id="tblja"><?php echo($lwr->getSigninCountForEventAndStatus($post->ID, 2)); ?></td>
                                <td class="lwr-event-detail-user-table-title">Ich bin dabei</td>
                                <td id="tbluserja"><?php echo($lwr->getSigninUsersForEventAndStatus($post->ID, 2)); ?></td>
                            </tr>
                            <tr class="lwr-event-detail-user-table-second">
                                <td class="lwr-event-detail-user-table-number"
                                    id="tblevtl"><?php echo($lwr->getSigninCountForEventAndStatus($post->ID, 1)); ?></td>
                                <td class="lwr-event-detail-user-table-title">Ich bin nicht sicher</td>
                                <td id="tbluserevtl"><?php echo($lwr->getSigninUsersForEventAndStatus($post->ID, 1)); ?></td>
                            </tr>
                            <tr class="lwr-event-detail-user-table-third">
                                <td class="lwr-event-detail-user-table-number"
                                    id="tblnein"><?php echo($lwr->getSigninCountForEventAndStatus($post->ID, 0)); ?></td>
                                <td class="lwr-event-detail-user-table-title">Ich kann nicht teilnehmen</td>
                                <td id="tblusernein"><?php echo($lwr->getSigninUsersForEventAndStatus($post->ID, 0)); ?></td>
                            </tr>
                        </table>

                        <p style="padding-left: 4px">
                            <a href="#" data-postid="<?php echo $post->ID; ?>" id="lwrCalendarLink">
                                <i class="fa fa-calendar" aria-hidden="true"></i>
                                &nbsp;Zum Kalender hinzufügen
                                <i id="lwrCalendarLoader" class="fa fa-cog fa-spin fa-fw"></i>
                            </a>
                        </p>
                    <?php } ?>
                </div>
            </article>
            <script type="text/javascript">

                jQuery(document).ready(function () {

                    jQuery('#lwrSignInLink').click(function (e) {
                        // Dem Link nicht folgen, so dass es keinen Sprung in der Seite gibt
                        e.preventDefault();

                        jQuery('#lwrSignInLink').html("wird gespeichert ....");

                        var state = jQuery('.signInVal:checked').val();

                        jQuery.post(ajaxurl, {
                            action: 'user_sign_event',
                            signInState: state,
                            userID: <?php print get_current_user_id(); ?>,
                            eventID: <?php print $post->ID; ?>,
                        }, function (response) {
                            jQuery('#lwrSignInLink').html('Änderungen speichern.');
                            console.log(JSON.stringify(response));
                            updateSignTable();
                        });

                        function updateSignTable() {
                            jQuery.post(ajaxurl, {
                                action: 'update_sign_table',
                                eventID: <?php print $post->ID; ?>,
                                dataType: 'json',
                            }, function (response) {

                                //Abfüllen der Daten in die Tabelle
                                var data = JSON.parse(response);

                                jQuery('#tblja').html(data.ja.count);
                                jQuery('#tblevtl').html(data.evtl.count);
                                jQuery('#tblnein').html(data.nein.count);

                                jQuery('#tblusernein').html(data.nein.users);
                                jQuery('#tbluserevtl').html(data.evtl.users);
                                jQuery('#tbluserja').html(data.ja.users);
                            });
                        }
                    });

                    jQuery("#lwrCalendarLoader").hide();
                    jQuery('#lwrCalendarLink').click(function (e) {

                        e.preventDefault();
                        var postid = jQuery('#lwrCalendarLink').attr('data-postid');

                        jQuery.get(ajaxurl, {
                            beforeSend: function () {
                                jQuery("#lwrCalendarLoader").show();
                            },
                            action: 'generate_calendar',
                            eventID: postid,
                        }, function (response) {
                            console.log("generate calendar ics for event " + postid);
                            console.log(response);

                            var data = JSON.parse(response);

                            var subject = data.postinfo.post_title;
                            var description = data.postinfo.post_content;
                            var location = data.postmeta.lwrOrt;
                            var begin = data.postmeta.lwrDatumVonSQL + " " + data.postmeta.lwrZeitVon;
                            var end = data.postmeta.lwrDatumBisSQL + " " + data.postmeta.lwrZeitBis;

                            if (location == null) {
                                location = "";
                            }

                            console.log(subject);
                            console.log(description);
                            console.log(location);
                            console.log(begin);
                            console.log(end);

                            var cal = ics();
                            cal.addEvent(subject, description, location, begin, end);
                            cal.download(subject + " " + data.postmeta.lwrDatumVon);
                            jQuery("#lwrCalendarLoader").hide();

                        });


                    });
                });


            </script>
        <?php
        endwhile;
        endif; ?>
        <?php comments_template(); ?>
    </div>
    
<?php get_footer(); ?>
