<?php

class NFCW_Commodity_Widget extends WP_Widget {

	function __construct() {

		parent::__construct(
			'commodity-widget',  // Base ID
			'NFCW Commodity Price Widget'   // Name
		);

		add_action(
			'widgets_init',
			function() {
				register_widget( 'NFCW_Commodity_Widget' );
			}
		);

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
		wp_enqueue_script( 'nfcw-script', NPFC_URL . 'js/script.js', array( 'jquery' ), NPFC_VERSION, false );
	}

	function enqueue_styles() {
		// Enqueue styles goes here
		wp_enqueue_style( 'nfcw-css', NPFC_URL . 'css/tabs.css', array(), NPFC_VERSION, 'all' );
	}

	public $args = array(
		'before_title'  => '<h4 class="widgettitle">',
		'after_title'   => '</h4>',
		'before_widget' => '<div class="nfcw-commodity-widget widget-wrap">',
		'after_widget'  => '</div></div>',
	);

	public function widget( $args, $instance ) {
		$json = isset( $GLOBALS['npfc_json']['commodity'] ) ? $GLOBALS['npfc_json']['commodity'] : '';

		echo $args['before_widget'];

		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
		}

		echo '<div class="commodity-widget ' . $instance['layout'] . '">';
		if ( ! empty( $json ) ) {
			echo '<p>As of ' . $json['date'] . '</p>';

			if ( ! isset( $instance['layout'] ) ) {
				$instance['layout'] = 'tabs';
			}

			$json_data = $json['data'];
			$newarray  = array();
			for ( $i = 0;$i < count( $json_data );$i++ ) {
					$newarray[ str_replace( ' ', '_', $json_data[ $i ]['measure'] ) ][] = array(
						'name'   => $json_data[ $i ]['name'],
						'price'  => $json_data[ $i ]['price'],
						'change' => $json_data[ $i ]['change'],
					);
			}

			if ( 'tabs' === $instance['layout'] ) {
				self::tabs_display( $newarray );
			} else {
				self::table_display( $newarray );
			}
		} else {
			echo 'Failed to retrieve data';
		}

		echo '</div>';
		echo NPFC\Main::source( NPFC_COMMODITY_SRC_URL, NPFC_COMMODITY_SRC );
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
						'table' => esc_html__( 'Table', 'nfcw-widget' ),
					);

					foreach ( $post_type_choices as $key => $value ) {
						echo '<option value="' . $key . '" ' . selected( $key, $instance['layout'], false ) . '>' . $value . '</option>';
					}
					?>
			</select>
		</p>
		<?php

	}

	public function update( $new_instance, $old_instance ) {

		// Delete transient on update.
		delete_transient( 'npfc_json' );

		$instance = array();

		$instance['title']  = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['layout'] = ( ! empty( $new_instance['layout'] ) ) ? sanitize_key( $new_instance['layout'] ) : '';

		return $instance;
	}

	public function table_display( $json ) {
		foreach ( $json as $key => $value ) :
			if ( 'source' === $key ) {
				continue;
			}
			echo '<h2 class="center">' . ucfirst( str_replace( '_', ' ', $key ) ) . '</h2>';
			echo '<table class="nfcw-commodity-price widefat fixed" cellspacing="0">';
			echo '<tr><th>Name</th>';
			echo '<th>Price</th>';
			echo '<th>Change</th>';
			echo '</tr>';
			foreach ( $value as $commodity ) {
				$icon = '';
				if ( isset( $commodity['change'] ) && $commodity['change'] > 0 ) {
					$icon = '<i class="dashicons dashicons-arrow-up"></i>';
				} elseif ( isset( $commodity['change'] ) && $commodity['change'] < 0 ) {
					$icon = '<i class="dashicons dashicons-arrow-down"></i>';
				} else {
					$icon = '<i class="dashicons dashicons-leftright"></i>';
				}
				echo '<tr><td>' . $commodity['name'] . '</td>';
				echo '<td>' . $commodity['price'] . '</td>';
				if ( isset( $commodity['change'] ) ) {
					echo '<td>' . $icon . '(' . $commodity['change'] . ')</td>';
				}
			}
			echo '</table>';
		endforeach;
	}

	public function tabs_display( $json ) {
		$i = 1;
		$j = 1;
		?>

		<div id="tabs" class="tabs">
			<div class="tabs-nav">
				<ul class="ui-tabs-nav">
					<?php
					foreach ( $json as $key => $value ) :
						$active = '';
						if ( 'per_1_tola' === $key ) {
							$active = 'ui-state-active';
						}
						?>
						<li class="ui-tabs-tab <?php echo $active; ?>"><a href="#tab-<?php echo $i; ?>" class="ui-tabs-anchor"><?php echo str_replace( '_', ' ', $key ); ?></a></li>
						<?php
						$i++;
					endforeach;
					?>
				</ul>
			</div><!-- .tabs-nav -->
			<?php
			foreach ( $json as $key => $value ) :
				$active = '';
				if ( 'per_1_tola' === $key ) {
					$active = 'active-tab';
				}
				?>
				<div class="ui-tabs-panel-wrap">

					<div id="tab-<?php echo $j; ?>" class="ui-tabs-panel <?php echo $active; ?>">
						<table class="nfcw-commodity-price widefat fixed">
							<tr>
								<th><?php echo 'Name'; ?></th>
								<th colspan="2"><?php echo 'Price'; ?></th>
							</tr>
							<?php
							foreach ( $value as $commodity ) :
								$icon = '';
								if ( isset( $commodity['change'] ) && $commodity['change'] > 0 ) {
									$icon = '<i class="dashicons dashicons-arrow-up"></i>';
								} elseif ( isset( $commodity['change'] ) && $commodity['change'] < 0 ) {
									$icon = '<i class="dashicons dashicons-arrow-down"></i>';
								} else {
									$icon = '<i class="dashicons dashicons-leftright"></i>';
								}
								?>
							<tr>
								<td><?php echo $commodity['name']; ?></td>
								<td><?php echo $commodity['price']; ?></td>
								<td>
								<?php
								if ( '' !== $icon ) {
									echo $icon . '(' . $commodity['change'] . ')';}
								?>
								</td>
							</tr>
							<?php endforeach; ?>
						</table>
					</div><!-- #tab-1 -->
				</div><!-- .ui-tabs-panel-wrap -->
				<?php
				$j++;
			endforeach;
			?>
		</div>
		<?php
	}
}

$commodity_widget = new NFCW_Commodity_Widget();
