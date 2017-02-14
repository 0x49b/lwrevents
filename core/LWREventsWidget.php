<?php

/**
 * User:        Florian Thiévent
 * File:        LWRCore.php
 * Version:     1.0
 * Description: LWR Widget to display next events in Sidebar
 */
class LWREventsWidget extends WP_Widget
{

    function __construct() {
        parent::__construct(// Base ID of your widget
            'LWREventsWidget',

            // Widget name will appear in UI
            __('LWR Events Widget', 'LWREventsWidget_domain'),

            // Widget description
            array('description' => __('Das LWR Events Widget stellt eine Komponente bereit, um eine Übersicht über kommende Events den Besuchern der Seite zu 
            geben.', 'LWREventsWidget_domain'),));
    }

    // Creating widget front-end
    // This is where the action happens
    public function widget($args, $instance) {
        $title = apply_filters('widget_title', $instance['title']);
        $numOfEvents = $instance['numEvents'];

        // before and after widget arguments are defined by themes
        echo $args['before_widget'];
        if (!empty($title)) {
            echo $args['before_title'] . $title . $args['after_title'];
        }

        ?>
        <ul class="lwr-event-widget-list" style="list-style-type: none;">
            <?php // Create and run custom loop
            $today = date( 'Y-m-d' );
            $args = array(
                'post_type'  => 'lwrevents',
                'meta_query' => array(
                    'relation' => 'AND',
                    'lwrZeitVon' => array(
                        'key'     => 'lwrZeitVon',
                        'compare' => 'EXISTS',
                    ),
                    'lwrDatumVon' => array(
                        'key'     => 'lwrDatumVonSQL',
                        'compare' => '>=',
                        'value'   => $today
                    ),
                ),
                'posts_per_page' => $numOfEvents,
                'orderby' => array(
                    'lwrDatumVon' => 'ASC',
                    'lwrZeitVon' => 'ASC',
                )
            );

            $custom_posts = new WP_Query($args);

            $lwr = new LWREventsCore();
            while ($custom_posts->have_posts()) : $custom_posts->the_post();
                ?>
                <li><i class="fa fa-bookmark-o"></i>
                    <?php

                    if ($lwr->getEventMeta(get_the_ID(), 'lwrDatumBis')) {
                        echo $lwr->getEventMeta(get_the_ID(), 'lwrDatumVon') . " &mdash; " . $lwr->getEventMeta(get_the_ID(), 'lwrDatumBis');
                    } else {
                        $lwr->eventMeta(get_the_ID(), 'lwrDatumVon');
                    }
                    ?><br/>

                    <a href="<?php the_permalink(); ?>" style="padding-left: 18px;">
                            <?php
                            $term = get_the_terms(get_the_ID(), 'Sportart');
                            echo $term[0]->name . ' : ' . get_the_title(); ?>
                    </a>

                </li>
            <?php endwhile;

            if( !$custom_posts->have_posts() ){
                print $lwr->getSettingsFromDB('lwr_empty_events');
            }

            ?>
        </ul>
        </aside>
        <?php
	echo $args['after_widget'];
    }

    // Widget Backend
    public function form($instance) {
        if (isset($instance['title'])) {
            $title = $instance['title'];
            $numOfEvents = $instance['numEvents'];
        } else {
            $title = __('Überschrift', 'LWREventsWidget_domain');
            $numOfEvents = __('5', 'LWREventsWidget_domain');
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <p>
            <label><label for="<?php echo $this->get_field_id('numEvents'); ?>"><?php _e('Anzahl Events:'); ?></label>
                <input class="widefat" id="<?php echo $this->get_field_id('numEvents'); ?>"
                       name="<?php echo $this->get_field_name('numEvents'); ?>"
                       type="text"
                       value="<?php echo esc_attr($numOfEvents); ?>"/></label>
        </p>
        <?php
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['numEvents'] = (!empty($new_instance['numEvents'])) ? strip_tags($new_instance['numEvents']) : '';

        return $instance;
    }

    function lwr_load_widget() {
        register_widget('LWREventsWidget');
    }
} // Class LWREventsWidget ends here