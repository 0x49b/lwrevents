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


						<?php
						global $wpdb;
						$title_raw = get_the_archive_title();
						$title     = explode( ': ', $title_raw );
						$term_name = $title[1];

						$term_id = $wpdb->get_var( "SELECT " . $wpdb->prefix . "terms.term_id FROM " . $wpdb->prefix . "terms where " . $wpdb->prefix . "terms.name = '$term_name'" );
						// Use the new tax_query WP_Query argument (as of 3.1)
						$taxonomy_query = new WP_Query( array(
							'tax_query' => array(
								array(
									'taxonomy' => 'Sportart',
									'field'    => 'id',
									'terms'    => array( $term_id ),

								),
							),
							'order'     => $lwr->getSettingsFromDB( 'lwr_sort_list_archive' )
						) );
						?>


						<?php if ( $taxonomy_query->have_posts() ) :
						while ( $taxonomy_query->have_posts() ) : $taxonomy_query->the_post(); ?>


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
