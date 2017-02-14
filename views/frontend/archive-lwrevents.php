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

    <section id="content" class="primary" role="main">
		<?php the_archive_title( '<h1 class="entry-title post-title">', '</h1>' ); ?>


        <section role="main">

            <div id="post-2" class="post-2 page type-page status-publish">

                <div class="entry clearfix">

                    <table>
                        <tr>
                            <th>Datum</th>
                            <th>Anlass</th>
                            <th>Kommentare</th>
                        </tr>

						<?php if ( have_posts() ) :

						while ( have_posts() ) : the_post(); ?>


                            <tr>
                                <td>
									<?php echo $lwr->getEventMeta( get_the_ID(), 'lwrDatumVon' ); ?><br/>
									<?php echo $lwr->getEventMeta( get_the_ID(), 'lwrDatumBis' ); ?>
                                </td>
                                <td>
                                    <strong><a href="<?php echo get_the_permalink(); ?>"><?php echo get_the_title(); ?></a></strong><br/>
									<?php echo get_the_excerpt(); ?>
                                </td>
                                <td>
                                    <a href="<?php echo get_comments_link( get_the_ID() ); ?>"><?php echo get_comments_number(); ?></a>
                                </td>
                            </tr>
						<?php endwhile; ?>
                    </table>

                </div>

            </div>


        </section>


	<?php


	$lwr->getPagination();

	endif; ?>

    </section>

	<?php get_sidebar(); ?>
</div>

<?php get_footer(); ?>
