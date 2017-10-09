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

                </div>

            </div>


        </div>



    </div>

    <?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
