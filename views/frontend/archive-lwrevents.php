<?php
    /**
     * Template Name: LWREvents
     * File:          archive-lwrevents.php
     * Project:       LWREvents
     * Version:       1.8.0.6
     * Description:   Displays a LWR Event Archive for Category
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
<?php the_archive_title( '<h1 class="entry-title post-title">', '</h1>' ); ?>
<div id="post-lwr-archive" class="post-2 page type-page status-publish">
    <div class="entry clearfix">
        <?php
        $qobj = get_queried_object();
        echo $lwr->lwrGetArchiveForCategory($qobj->slug);
        ?>
    </div>
</div>
<?php get_footer(); ?>