<?php
/**
 * Created by PhpStorm.
 * User: Florian Thiévent
 * Date: 14.02.2017
 * Time: 06:53
 */

$lwr = new LWREventsCore();

get_header(); ?>

<style>
    .type-post, .type-page, .type-attachment {
        margin: 0 0 1.5em 0;
        padding: 0.8em 1.5em 1.5em;
        box-shadow: none;
        border: none;
        background: #fff;
    }
</style>

<div id="wrap" class="container clearfix">

    <div id="content" class="primary" role="main">
        <?php the_archive_title( '<h1 class="entry-title post-title">', '</h1>' ); ?>


        <div role="main">

            <div id="post-2" class="post-2 page type-page status-publish">

                <div class="entry clearfix">

                    <?php
                    $qobj = get_queried_object();

                    echo $lwr->lwrGetArchiveForCategory($qobj->slug);

                    ?>

                    <!--p>
                        <a href="#" data-postid="<?php echo $qobj->slug; ?>" id="lwrCalendarLink"><i
                                    class="fa fa-calendar" aria-hidden="true"></i> Kalender herunterladen
                            <i id="lwrCalendarLoader" class="fa fa-cog fa-spin fa-fw"></i></a>
                    </p-->

                </div>

            </div>


        </div>



    </div>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>

<script>
    jQuery(document).ready(function(){
        jQuery("#lwrCalendarLoader").hide();
        jQuery('#lwrCalendarLink').click(function (e) {

            e.preventDefault();
            var category = jQuery('#lwrCalendarLink').attr('data-postid');

            jQuery.get(ajaxurl, {
                beforeSend: function () {
                    jQuery("#lwrCalendarLoader").show();
                },
                action: 'generate_calendar_category',
                category: category,
            }, function (response) {
                console.log("generate calendar ics for events " + category);
                console.log(response);
/*
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
                */

            });


        });
    });
</script>
