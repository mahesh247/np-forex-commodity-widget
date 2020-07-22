<?php
class NFCW_ExRates_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'ex-rates-widget',  // Base ID
			'NFCW Exchange Rates Widget'   // Name
		);

		add_action(
			'widgets_init',
			function() {
				register_widget( 'NFCW_ExRates_Widget' );
			}
		);

		add_action( 'wp_enqueue_scripts', array( $this, 'forex_enqueue_styles' ) );
	}

	function forex_enqueue_styles() {
		wp_enqueue_style( 'forex-flag-css', NPFC_URL . 'css/flag-icon.min.css', array(), NPFC_VERSION, 'all' );
	}

	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="nfcw-forex-widget widget-wrap">',
		'after_widget'  => '</div></div>',
	);

	public function widget( $args, $instance ) {
		$json = isset( $GLOBALS['npfc_json']['forex'] ) ? $GLOBALS['npfc_json']['forex'] : '';

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		if ( ! empty( $json ) ) {
			echo '<p>As of ' . gmdate( 'M d, Y', strtotime( $json['date'] ) ) . '</p>';
			echo '<table class="nfcw-exrates widefat fixed" cellspacing="0">';
				echo '<tr><th>Currency</th>';
				echo '<th>Unit</th>';
				echo '<th>Buying</th>';
				echo '<th>Selling</th>';
				echo '</tr>';

			foreach ( $json['data'] as $rate ) {
				$icon_selling = '';
				if ( isset( $rate['selling_change'] ) && $rate['selling_change'] > 0 ) {
					$icon_selling = '<i class="dashicons dashicons-arrow-up"></i>';
				} elseif ( isset( $rate['selling_change'] ) && $rate['selling_change'] < 0 ) {
					$icon_selling = '<i class="dashicons dashicons-arrow-down"></i>';
				} else {
					$icon_selling = '<i class="dashicons dashicons-leftright"></i>';
				}

				$icon_buying = '';
				if ( isset( $rate['buying_change'] ) && $rate['buying_change'] > 0 ) {
					$icon_buying = '<i class="dashicons dashicons-arrow-up"></i>';
				} elseif ( isset( $rate['buying_change'] ) && $rate['buying_change'] < 0 ) {
					$icon_buying = '<i class="dashicons dashicons-arrow-down"></i>';
				} else {
					$icon_buying = '<i class="dashicons dashicons-leftright"></i>';
				}

				echo '<tr><td>';
				if ( isset( $rate['flag'] ) ) {
					echo '<span class="flag-icon flag-icon-' . $rate['flag'] . '"></span> ';
				}
				echo $rate['currency'] . '</td>';
				echo '<td>' . $rate['unit'] . '</td>';
				echo "<td>{$rate['buying/rs.']} {$icon_buying} ({$rate['buying_change']})</td>";
				//echo '<td>' . $rate['buying_change'] . ' ' . $icon_buying . '</td>';
				echo "<td>{$rate['selling/rs.']} {$icon_selling} ({$rate['selling_change']})</td>";
				//echo '<td>' . $rate['selling_change'] . ' ' . $icon_selling . '</td></tr>';
			}
			echo '</table>';
			echo NPFC\Main::source( NPFC_FOREX_SRC_URL, NPFC_FOREX_SRC );
			echo $args['after_widget'];
		} else {
			echo 'Failed to retrieve data';
		}

	}

	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'NFCW: Nepal Foreign Exchange Rates', 'nfcw-widget' );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php esc_attr_e( 'Title:', 'nfcw-widget' ); ?></label>
			<input class="widefat" id="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>">
		</p>
		<?php

	}

	public function update( $new_instance, $old_instance ) {

		// Delete transient on update.
		delete_transient( 'npfc_json' );

		$instance = array();

		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}

}

$exrates_widget = new NFCW_ExRates_Widget();
