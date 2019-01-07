<?php

class NFCW_Oil_Widget extends WP_Widget {
 
    function __construct() {
 
        parent::__construct(
            'oil-widget',  // Base ID
            'NFCW Oil Price Widget'   // Name
        );
 
        add_action( 'widgets_init', function() {
            register_widget( 'NFCW_Oil_Widget' );
        });

        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
    }

    /**
     * Enqueue scripts
     *
     * @param string $handle Script name
     * @param string $src Script url
     * @param array $deps (optional) Array of script names on which this script depends
     * @param string|bool $ver (optional) Script version (used for cache busting), set to null to disable
     * @param bool $in_footer (optional) Whether to enqueue the script before </head> or before </body>
     */
    function enqueue_scripts() {
        // Enqueue scripts goes here
        wp_enqueue_script( 'nfcw-script', plugin_dir_url( __FILE__ ) . 'js/script.js', array( 'jquery' ), '1.0', false );
    }

    function enqueue_styles() {
        // Enqueue styles goes here
        wp_enqueue_style( 'nfcw-css', plugin_dir_url( __FILE__ ) . 'css/tabs.css', array(), '1.0', 'all' );
    }

    public $args = array(
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="nfcw-oil-widget widget-wrap">',
        'after_widget'  => '</div></div>'
    );
 
    public function widget( $args, $instance ) {
        if ( false === ( $json = get_transient( 'oil_json' ) ) ) {
            // It wasn't there, so regenerate the data and save the transient
            $url      = 'https://mahesh-maharjan.com.np/npfc/oil-json';
            $get      = wp_remote_get( $url );
            $response = wp_remote_retrieve_body( $get );
            $json     = array_reverse( json_decode( $response, true ) );
            if( ! empty( $json ) ) {
                set_transient( 'oil_json', $json, 1*60*60 );
            } 
        }
 
        echo $args['before_widget'];
 
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
 
        echo '<div class="oil-widget">';
        if( ! empty ( $json ) ) {
            echo '<p>As of ' . $json[0]['date'] . '</p>';
            echo $json[0]['text'];
        }
        else {
            echo 'Failed to retrieve data';
        }
       
        echo '</div>';
        echo '<span class="source">Source: <a href="//nepaloil.com.np" target="_blank">Nepal Oil Corporation Limited</a></span>';
 
        echo $args['after_widget'];
 
    }
 
    public function form( $instance ) {
 
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'NFCW Oil Price', 'nfcw-widget' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'nfcw-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
 
    }
 
    public function update( $new_instance, $old_instance ) {
        delete_transient( 'oil_json' );
        $instance = array();
 
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
 
        return $instance;
    }
}

$oil_widget = new NFCW_Oil_Widget();