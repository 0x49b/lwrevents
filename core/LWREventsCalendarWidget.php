<?php

/**
 * User:        Florian Thiévent
 * File:        LWRCore.php
 * Version:     1.0
 * Description: LWR Widget to display next events in Sidebar
 */
class LWREventsCalendarWidget extends WP_Widget {

	function __construct() {
		parent::__construct(// Base ID of your widget
			'LWREventsCalendarWidget',

			// Widget name will appear in UI
			__( 'LWR Events Kalender Widget', 'LWREventsCalendarWidget_domain' ),

			// Widget description
			array(
				'description' => __( 'LWR Events als Kalender ausgeben.', 'LWREventsCalendarWidget_domain' ),
			) );
	}

	// Creating widget front-end
	// This is where the action happens
	public function widget( $args, $instance ) {
		$title       = apply_filters( 'widget_title', $instance['title'] );
		$numOfEvents = $instance['numEvents'];

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

		?>
		<ul class="lwr-event-widget-list" style="list-style-type: none;">
			<?php // Create and run custom loop
			$custom_posts = new WP_Query();
			$custom_posts->query('post_type=lwrevents&posts_per_page='.$numOfEvents);
			while ($custom_posts->have_posts()) : $custom_posts->the_post();
         ?>
				<li><i class="fa fa-square-o"></i>&nbsp;&nbsp;<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></li>
			<?php endwhile; ?>
		</ul>
		<?php

		echo $args['after_widget'];
	}

	// Widget Backend
	public function form( $instance ) {
		if ( isset( $instance['title'] ) ) {
			$title       = $instance['title'];
			$numOfEvents = $instance['numEvents'];
		} else {
			$title       = __( 'Überschrift', 'LWREventsCalendarWidget_domain' );
			$numOfEvents = __( '5', 'LWREventsCalendarWidget_domain' );
		}
		// Widget admin form
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text"
			       value="<?php echo esc_attr( $title ); ?>"/>
		</p>
		<p>
			<label><label for="<?php echo $this->get_field_id( 'numEvents' ); ?>"><?php _e( 'Anzahl Events:' ); ?></label>
				<input class="widefat" id="<?php echo $this->get_field_id( 'numEvents' ); ?>" name="<?php echo $this->get_field_name( 'numEvents' ); ?>"
				       type="text"
				       value="<?php echo esc_attr( $numOfEvents ); ?>"/></label>
		</p>
		<?php
	}

	// Updating widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance              = array();
		$instance['title']     = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['numEvents'] = ( ! empty( $new_instance['numEvents'] ) ) ? strip_tags( $new_instance['numEvents'] ) : '';

		return $instance;
	}

	function lwr_load_cal_widget() {
		register_widget( 'LWREventsCalendarWidget' );
	}
} // Class LWREventsCalendarWidget ends here