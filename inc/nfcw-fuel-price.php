<?php

class NFCW_Fuel_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'fuel-widget',  // Base ID
			'NFCW Fuel Price Widget'   // Name
		);

		add_action(
			'widgets_init',
			function() {
				register_widget( 'NFCW_Fuel_Widget' );
			}
		);
	}

	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="nfcw-fuel-widget widget-wrap">',
		'after_widget'  => '</div></div>',
	);

	public function widget( $args, $instance ) {
		$json = isset( $GLOBALS['npfc_json']['fuel'] ) ? $GLOBALS['npfc_json']['fuel'] : '';

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo '<div class="fuel-widget">';
		if ( ! empty( $json ) ) {
			echo '<p>As of ' . gmdate( 'M d, Y', strtotime( $json['date'] ) ) . '</p>';
			echo '<table class="nfcw-fuel-price">';
				echo '<tbody>';
					echo '<tr>';
						echo '<th>Name</th>';
						echo '<th>Quantity</th>';
						echo '<th>Price</th>';
					echo '</tr>';
				$i = 1;
			foreach ( $json['data'] as $fuel ) {
				$icon = '';
				if ( isset( $fuel['change'] ) && $fuel['change'] > 0 ) {
					$icon = '<i class="dashicons dashicons-arrow-up"></i>';
				} elseif ( isset( $fuel['change'] ) && $fuel['change'] < 0 ) {
					$icon = '<i class="dashicons dashicons-arrow-down"></i>';
				} else {
					$icon = '<i class="dashicons dashicons-leftright"></i>';
				}
				echo '<tr>';
					echo "<td>{$fuel['fuel type']}</td>";
					echo "<td>{$fuel['quantity']}</td>";
				echo "<td>Rs {$fuel['price nrs.']}/- {$icon}({$fuel['change']})</td>";
				//echo "<td>{$icon}</td>";
				echo '</tr>';
					$i++;
			}
				echo '</tbody>';
			echo '</table>';
			if ( isset( $json['change'] ) ) {
				echo "{$json['data']['location']}</br>";
			}
			if ( isset( $json['change'] ) ) {
				echo "{$json['data']['revised']}</br>";
			}
			if ( isset( $json['date'] ) ) {
				$json['date'] = gmdate( 'M d, Y', strtotime( $json['date'] ) );
				echo "Last updated on: {$json['applied_from']}</br></br>";
			}
		} else {
			echo 'Failed to retrieve data';
		}

		echo '</div>';
		echo NPFC\Main::source( NPFC_FUEL_SRC_URL, NPFC_FUEL_SRC );

		echo $args['after_widget'];

	}

	public function form( $instance ) {

		$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'NFCW: Fuel Price', 'nfcw-widget' );
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

$fuel_widget = new NFCW_Fuel_Widget();
