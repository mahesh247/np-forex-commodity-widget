<?php
/*
Plugin Name:  NP Forex Commodity Widget 1
Plugin URI:   https://wordpress.org/plugins/np-forex-commodity-widget/
Description:  NP Forex Commodity Widget is a simple and light weight plugin to that to add up a widget that shows current commodity prices and exchange rates.
Version:      1.2
Author:       maheshmaharjan, tikarambhandari, pratikshrestha
Author URI:   https://mahesh-maharjan.com.np
License:      GPL2
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  nfcw-widget
Domain Path:  /languages
*/

class NFCW_Commodity_Widget extends WP_Widget {
 
    function __construct() {
 
        parent::__construct(
            'commodity-widget',  // Base ID
            'NFCW Commodity Price Widget'   // Name
        );
 
        add_action( 'widgets_init', function() {
            register_widget( 'NFCW_Commodity_Widget' );
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
        'before_widget' => '<div class="nfcw-commodity-widget widget-wrap">',
        'after_widget'  => '</div></div>'
    );
 
    public function widget( $args, $instance ) {
        if ( false === ( $json = get_transient( 'commodity_json' ) ) ) {
            // It wasn't there, so regenerate the data and save the transient
            $url      = 'https://mahesh-maharjan.com.np/npfc/commodity-json';
            $get      = wp_remote_get( $url );
            $response = wp_remote_retrieve_body( $get );
            $json     = array_reverse( json_decode( $response, true ) );
            if( ! empty( $json ) ) {
                set_transient( 'commodity_json', $json, 1*60*60 );
            } 
        }
 
        echo $args['before_widget'];
 
        if ( ! empty( $instance['title'] ) ) {
            echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
        }
 
        echo '<div class="commodity-widget ' . $instance['layout'] . '">';
        if( ! empty ( $json ) ) {
            echo '<p>As of ' . $json[0]['date'] . '</p>';

            if( ! isset( $instance['layout'] ) ) {
                $instance['layout'] = 'tabs';
            }

            if( 'tabs' == $instance['layout'] ) {
                self::tabs_display($json);
            }
            else {
                self::table_display($json);
            }
        }
        else {
            echo 'Failed to retrieve data';
        }
        
        echo '</div>';
        echo '<span class="source">Source: <a href="//fenegosida.org" target="_blank">Federation of Nepal Gold & Silver Dealer\'s Association</a></span>';
 
        echo $args['after_widget'];
 
    }
 
    public function form( $instance ) {
 
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'NFCW Commodity Price', 'nfcw-widget' );
        ?>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'nfcw-widget' ); ?></label>
            <input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
        </p>

        <p>
            <label for="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>"><?php esc_html_e( 'Layout', 'nfcw-widget' ); ?>:</label>
            <select id="<?php echo esc_attr( $this->get_field_id( 'layout' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'layout' ) ); ?>" class="widefat">
                <?php
                    $post_type_choices = array(
                        'tabs'  => esc_html__( 'Tabs', 'nfcw-widget' ),
                        'table' => esc_html__( 'Table', 'nfcw-widget' )
                    );

                foreach ( $post_type_choices as $key => $value ) {
                    echo '<option value="' . $key . '" '. selected( $key, $instance['layout'], false ) .'>' . $value .'</option>';
                }
                ?>
            </select>
        </p>
        <?php
 
    }
 
    public function update( $new_instance, $old_instance ) {
        delete_transient( 'commodity_json' );
        $instance = array();
 
        $instance['title'] = ( !empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
        $instance['layout'] = ( !empty( $new_instance['layout'] ) ) ? sanitize_key( $new_instance['layout'] ) : '';
 
        return $instance;
    }

    public function table_display($json) {
        echo '<table class="nfcw-commodity-price widefat fixed" cellspacing="0">';
            echo '<tr><th>Name</th>';
            echo '<th>Price</th>';
            echo '<th>Measure</th></tr>';
            foreach($json as $commodity){
                echo '<tr><td>' . $commodity['name'] . '</td>';
                echo '<td>' . $commodity['price'] . '/-</td>';
                echo '<td>' . $commodity['measure'] . '</td></tr>';
            }
            echo '</table>';
    }

    public function tabs_display($json) {
        $i = $j = 1;
        foreach($json as $k=>$data) {
            $commodity[$data['measure']][] = array(
                'name'  => $data['name'],
                'price' => $data['price']
            );
        } 
        $commodity = array_reverse($commodity);
        ?>

        <div id="tabs" class="tabs">
            <div class="tabs-nav">
                <ul class="ui-tabs-nav">
                    <?php foreach($commodity as $measure=> $value):
                    $active = '';
                    if($i == 1){
                       $active = 'ui-state-active'; 
                    }
                    ?>
                    <li class="ui-tabs-tab <?php echo $active; ?>"><a href="#tab-<?php echo $i; ?>" class="ui-tabs-anchor"><?php echo $measure; ?></a></li>
                    <?php $i++; endforeach; ?>
                </ul>
            </div><!-- .tabs-nav -->
            <?php foreach($commodity as $measure=> $value): 
                $active = '';
                if($j == 1){
                   $active = 'active-tab'; 
                }
            ?>
                <div class="ui-tabs-panel-wrap">
                    <div id="tab-<?php echo $j; ?>" class="ui-tabs-panel <?php echo $active; ?>">
                    <table class="nfcw-commodity-price widefat fixed">
                    <tr>
                        <th><?php echo 'Name'; ?></th>
                        <th><?php echo 'Price'; ?></th>
                    </tr>
                    <?php 
                    foreach($value as $data=> $datam ): ?>
                        <tr>
                            <td><?php echo 'Rs. ' . $datam['name'] ; ?></td>
                            <td><?php echo 'Rs. ' . $datam['price'].'/-' ; ?></td>
                        </tr>
                    <?php
                    endforeach;
                    ?>
                     </table>
                    </div><!-- #tab-1 -->
                </div><!-- .ui-tabs-panel-wrap -->
            <?php $j++; endforeach; ?>
        </div>
        <?php 
    }
 
}

$commodity_widget = new NFCW_Commodity_Widget();

include_once( 'nfcw-ex-rates.php' );
include_once( 'nfcw-oil-price.php' );