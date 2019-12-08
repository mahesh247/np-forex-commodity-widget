<?php

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
        wp_enqueue_style( 'dashicons' );
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
            $url      = API_URL . 'commodity-json';
            $get      = wp_remote_get( $url );
            $response = wp_remote_retrieve_body( $get );
            $json     = json_decode( $response, true );
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
            $utc_date = DateTime::createFromFormat(
                            'Y-m-d G:i', 
                            $json['modified'], 
                            new DateTimeZone('UTC')
            );

            echo '<p>As of ' . $json['date'] . '</p>';

            if( ! isset( $instance['layout'] ) ) {
                $instance['layout'] = 'tabs';
            }

            $json['data'] = array_reverse($json['data']);

            if( 'tabs' == $instance['layout'] ) {
                self::tabs_display($json['data']);
            }
            else {
                self::table_display($json['data']);
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
 
        $title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'NFCW: Commodity Price', 'nfcw-widget' );
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
        foreach($json as $key => $value):
            if( 'source' == $key ) {
                continue;
            }
            echo '<h2 class="center">' . ucfirst( str_replace( '_', ' ', $key ) ) . '</h2>' ;
            echo '<table class="nfcw-commodity-price widefat fixed" cellspacing="0">';
            echo '<tr><th>Name</th>';
            echo '<th>Price</th>';
            echo '<th>Change</th></tr>';
            foreach($value as $commodity){
                $icon = '';
                if( $commodity['change'] > 0 ) {
                    $icon = '<i class="dashicons dashicons-arrow-up"></i>';
                } elseif ( $commodity['change'] < 0 ) {
                    $icon = '<i class="dashicons dashicons-arrow-down"></i>';
                } else {
                    $icon = '<i class="dashicons dashicons-leftright"></i>';
                }
                echo '<tr><td>' . $commodity['name'] . '</td>';
                echo '<td>' . $commodity['price'] . '/-</td>';
                echo '<td>'. $icon . '(' . $commodity['change'] . ')</td>';
            }
            echo '</table>';
        endforeach;
    }

    public function tabs_display($json) {
        $i = $j = 1;
        ?>

        <div id="tabs" class="tabs">
            <div class="tabs-nav">
                <ul class="ui-tabs-nav">
                    <?php foreach($json as $key => $value):
                    $active = '';
                    if($key == 'per 1 tola'){
                       $active = 'ui-state-active'; 
                    }
                    ?>
                    <?php if( $key != 'source'): ?>
                        <li class="ui-tabs-tab <?php echo $active; ?>"><a href="#tab-<?php echo $i; ?>" class="ui-tabs-anchor"><?php echo str_replace( '_', ' ', $key ) ; ?></a></li>
                    <?php endif; ?>
                    <?php $i++; endforeach; ?>
                </ul>
            </div><!-- .tabs-nav -->
            <?php foreach($json as $key => $value): 
                if( $key != 'source'):
                    $active = '';
                    if($key == 'per 1 tola'){
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
                                    <?php foreach($value as $commodity): 
                                        $icon = '';
                                        if( $commodity['change'] > 0 ) {
                                            $icon = '<i class="dashicons dashicons-arrow-up"></i>';
                                        } elseif ( $commodity['change'] < 0 ) {
                                            $icon = '<i class="dashicons dashicons-arrow-down"></i>';
                                        } else {
                                            $icon = '<i class="dashicons dashicons-leftright"></i>';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo $commodity['name'] ; ?></td>
                                        <td><?php echo $commodity['price'] . '/-' ; ?></td>
                                        <!-- <td><?php //if( '' != $icon ) echo $icon . '(' . $commodity['change'] . ')' ; ?></td> -->
                                    </tr>
                                    <?php endforeach; ?>
                                 </table>
                            </div><!-- #tab-1 -->
                        </div><!-- .ui-tabs-panel-wrap -->
                <?php endif;?>
            <?php $j++; endforeach; ?>
        </div>
        <?php 
    }
}

$commodity_widget = new NFCW_Commodity_Widget();