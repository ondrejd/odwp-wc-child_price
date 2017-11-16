<?php
/**
 * Plugin Name: Vlastnosti produktů pro ja-eshop.cz
 * Plugin URI: https://github.com/ondrejd/simple-woocommerce-plugins
 * Description: Přidává pro produkty <strong>WooCommerce</strong> widget s filtrem dle vlastností produktu.
 * Version: 0.1.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.2
 * Tags: woocommerce,product,custom product fields
 * Donate link: https://www.paypal.me/ondrejd
 * Text Domain: odwp-wc-products_properties
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/simple-woocommerce-plugins for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-product_properties
 * @since 0.1.0
 *
 * @link https://codex.wordpress.org/Widgets_API for documentation on WordPress Widgets API.
 */

if( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'ODWP_WC_Products_Properties_Widget' ) ) :
	/**
	 * Class with the widget self.
	 * @author Ondřej Doněk, <ondrejd@gmail.com>
	 * @since 0.1.0
	 */
	class ODWP_WC_Products_Properties_Widget extends WP_Widget {
		/**
		 * Constructor - sets up the widget
		 * @see WP_Plugin::__construct()
		 * @since 0.1.0
		 */
		public function __construct() {
			$widget_opts = [
				'classname'   => 'odwp-wc-products_properties_widget',
				'description' => esc_html__( 'Filtr dle dodatečných vlastností produktů.', 'odwpwcpp' ),
				'title'       => esc_html__( 'Vlastnosti produktů', 'odwpwcpp' ),
			];

			parent::__construct(
				'odwp-wc-products_properties_widget',
				$widget_opts['title'],
				$widget_opts
			);
		}

		/**
		 * Outputs the content of the widget
		 * @param array $args
		 * @param array $instance The widget options
		 * @see WP_Plugin::widget()
		 * @since 0.1.0
		 */
		public function widget( $args, $instance ) {
			echo $args['before_widget'];

			if ( ! empty( $instance['title'] ) ) {
				echo $args['before_title'] . apply_filters( 'widget_title', $instance['title'] ) . $args['after_title'];
			}

			//...
			echo esc_html__( $args['after_widget'] );
		}

		/**
		 * Outputs the options form on admin.
		 * @param array $instance The widget options.
		 * @see WP_Plugin::form()
		 * @since 0.1.0
		 */
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : esc_html__( 'Vlastnosti produktů', 'odwpwcpp' );
?>
			<p>
				<label for="<?=esc_attr( $this->get_field_id( 'title' ) )?>">
					<?php esc_attr_e( 'Název:', 'odwpwcpp' ) ?>
					<input class="widefat" id="<?=esc_attr( $this->get_field_id( 'title' ) )?>" type="text" value="<?=esc_attr( $title )?>">
				</label>
			</p>
<?php
		}

		/**
		 * Processing widget options on save
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 * @see WP_Plugin::update()
		 * @since 0.1.0
		 */
		public function update( $new_instance, $old_instance ) {
			$instance = [];
			$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';

			return $instance;
		}
	}
endif;

/**
 * @todo Initialize localization!
 */

add_action( 'widgets_init', function() {
	register_widget( 'ODWP_WC_Products_Properties_Widget' );
} );