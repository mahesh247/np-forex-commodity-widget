<?php
class NFCW_ExRates_Widget extends WP_Widget {
 
    function __construct() {
 
        parent::__construct(
            'ex-rates-widget',  // Base ID
            'NFCW Exchange Rates Widget'   // Name
        );
 
        add_action( 'widgets_init', function() {
            register_widget( 'NFCW_ExRates_Widget' );
        });

        add_action('wp_enqueue_scripts', array( $this, 'forex_enqueue_styles' ) );
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
    function forex_enqueue_scripts() {
        //wp_register_script( $handle, $src, $deps = array, $ver = false, $in_footer = false )
    }

    function forex_enqueue_styles() {
        wp_enqueue_style( 'forex-flag-css', plugin_dir_url( __FILE__ ) . 'css/flag-icon.min.css', array(), '1.0', 'all' );
    }


 
    public $args = array(
        'before_title'  => '<h4 class="widgettitle">',
        'after_title'   => '</h4>',
        'before_widget' => '<div class="nfcw-forex-widget widget-wrap">',
        'after_widget'  => '</div></div>'
    );
 
    public function widget( $args, $instance ) {
        if ( false === ( $rates = get_transient( 'rates' ) ) ) {
            // It wasn't there, so regenerate the data and save the transient
            $url      = 'https://mahesh-maharjan.com.np/npfc/forex-json';
            $get      = wp_remote_get( $url );
            $response = wp_remote_retrieve_body( $get );
            $rates    = json_decode( $response, true );
            if( ! empty( $rates ) ) {
                set_transient( 'rates', $rates, 1*60*60 );
            }
        }
        	
        echo $args['before_widget'];
 
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }

        if( ! empty( $rates ) ) {
            echo '<div><h5>Nepal Exchange Rates</h5> <span>As of ' . $rates[0]['Date'] . '</span></div>';
            echo '<table class="nfcw-exrates widefat fixed" cellspacing="0">';
                echo '<tr><th>Currency</th>';
                echo '<th>Unit</th>';
                echo '<th>Buying</th>';
                echo '<th>Selling</th></tr>';
                foreach( $rates as $rate ) {
                    if ( '' == $rate['TargetBuy'] && 'CNY' == $rate['BaseCurrency'] ){
                        $rate['TargetBuy'] = '16.48';
                    }
                    $flag = $rate['CountryFlag'];
                    $flag = explode(' ', $flag);
                    $flag = $flag[2];
                    echo '<tr><td><span class="flag-icon flag-icon-' . $flag . '"></span> ' . $rate['BaseCurrency'] . '</td>';
                    echo '<td>' . $rate['BaseValue'] . '</td>';
                    echo '<td>' . $rate['TargetBuy'] . '</td>';
                    echo '<td>' . $rate['TargetSell'] . '</td></tr>';
                }
            echo '</table>';
            echo '<span class="source">Source: <a href="//nrb.org.np" target="_blank">Nepal Rastra Bank</a></span>';
            echo $args['after_widget'];
        }
        else {
            echo 'Failed to retrieve data';
        }
 
    }
 
    public function form( $instance ) {
 
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'NFCW FOREX Table', 'nfcw-widget' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'nfcw-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>
        <?php
 
    }
 
    public function update( $new_instance, $old_instance ) {
        delete_transient( 'rates' );
        $instance = array();
 
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        return $instance;
    }
 
}

$exrates_widget = new NFCW_ExRates_Widget();