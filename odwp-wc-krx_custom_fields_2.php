<?php
/**
 * Plugin Name: Uživatelská pole #2
 * Plugin URI: https://github.com/ondrejd/odwp-wc-child_price
 * Description: Přidá uživatelská pole jako <em>důležité informace</em>, <em>místo setkání</em> atp. pro <strong>WooCommerce</strong> produkty.
 * Version: 1.0.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.2
 * Tags: woocommerce,product,custom product fields
 * Donate link: https://www.paypal.me/ondrejd
 * Text Domain: odwp-wc-krx_custom_fields_2
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/simple-woocommerce-plugins for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-krx_custom_fields_2
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'ODWP_WC_Krx_Custom_Fields_2' ) ) :
    /**
     * Class that implements our plugin.
     *@since 1.0.0
     */
    class ODWP_WC_Krx_Custom_Fields_2 {

        /**
         * @internal Activates the plugin.
         * @return void
         * @since 1.0.0
         */
        public static function activate() {
            //...
        }

        /**
         * @internal Deactivates the plugin.
         * @return void
         * @since 1.0.0
         */
        public static function deactivate() {
            //...
        }

        /**
         * @internal Deactivates plugin directly by updating WP option `active_plugins`.
         * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
         * @return void
         * @since 1.0.0
         * @uses get_option
         * @uses update_option
         */
        public static function deactivate_raw() {
            $plugins = get_option( 'active_plugins' );
            $out = array();

            foreach( $plugins as $key => $val ) {
                if( $val != 'odwp-wc-krx_custom_fields_2.php' ) {
                    $out[$key] = $val;
                }
            }

            update_option( 'active_plugins', $out );
        }

        /**
         * @internal Uninstalls the plugin.
         * @return void
         * @since 1.0.0
         */
        public static function uninstall() {
            //...
        }

        /**
         * @internal  Checks if requirements are met.
         * @link https://developer.wordpress.org/reference/functions/is_plugin_active_for_network/#source-code
         * @return boolean Returns `true` if requirements are met.
         * @since 1.0.0
         * @uses get_option
         */
        public static function requirements_check() {
            if(
                in_array( 'woocommerce/woocommerce.php',
                ( array ) get_option( 'active_plugins', array() ) )
            ) {
                return true;
            }

            return false;
        }

        /**
         * @internal  Shows error in WP administration that minimum requirements were not met.
         * @return void
         * @since 1.0.0
         */
        public static function requirements_error() {
?>
<div id="odwpwckcf2_message" class="error notice is-dismissible">
    <p><?php _e( 'Plugin <b>Uživatelské hodnoty #2</b> vyžadují, aby byl nainstalován a aktivován plugin <b>WooCommerce</b> &ndash; plugin byl deaktivován.', 'odwp-wc-krx_custom_fields_2' ) ?></p>
</div>
<?php
        }

        /**
         * Constructor.
         * @return void
         * @since 1.0.0
         * @uses register_activation_hook
         * @uses register_deactivation_hook
         * @uses register_uninstall_hook
         * @uses add_action
         */
        public function __construct() {
            register_activation_hook( __FILE__, array( __CLASS__, 'activate' ) );
            register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivate' ) );
            register_uninstall_hook( __FILE__, array( __CLASS__, 'uninstall' ) );
            add_action( 'plugins_loaded', array( $this, 'init' ) );
        }

        /**
         * @internal Initializes the plugin.
         * @return void
         * @since 1.0.0
         * @uses add_action
         * @uses add_filter
         */
        public function init() {
            add_filter( 'woocommerce_product_data_tabs', array( $this, 'add_product_tab' ), 98 );
            add_action( 'woocommerce_product_data_panels', array( $this, 'add_product_panel' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ) );
        }

        /**
         * @internal Adds product tab.
         * @param array $tabs
         * @return array
         * @since 1.0.0
         */
        public function add_product_tab( $tabs ) {
            $tabs['seconduser'] = array(
                'label'    => __( 'Uživatelská pole #2', 'odwp-wc-krx_custom_fields_2' ),
                'target'   => 'second_user_data',
                'class'    => array( 'hide_if_grouped' ),
                'priority' => 99,
            );
            return $tabs;
        }

        /**
         * @internal Renders product tab pane.
         * @return void
         * @since 1.0.0
         * @uses woocommerce_wp_textarea_input
         */
        public function add_product_panel() {
?>
<div id="second_user_data" class="panel woocommerce_options_panel hidden" style="display: none;">
    <div class="options_group" style="display: block;">
<?php
        woocommerce_wp_textarea_input( array(
                'id'          => '_text_field_important_informations',
                'label'       => __( 'Důležité informace', 'odwp-wc-krx_custom_fields_2' ),
                'desc_tip'    => 'true',
                'description' => __( 'Zadejte delší textový řetězec.', 'odwp-wc-krx_custom_fields_2' ),
                'rows'        => 3,
        ) );
        woocommerce_wp_textarea_input( array(
                'id'          => '_text_field_gathering_point',
                'label'       => __( 'Místo setkání', 'odwp-wc-krx_custom_fields_2' ),
                'desc_tip'    => 'true',
                'description' => __( 'Zadejte delší textový řetězec.', 'odwp-wc-krx_custom_fields_2' ),
                'rows'        => 3,
        ) );
?>
    </div>
</div>
<?php
        }

        /**
         * @internal Saves product tab fields.
         * @param int $post_id
         * @return void
         * @since 1.0.0
         * @uses esc_attr
         * @uses update_post_meta
         */
        public function save_product_meta( $post_id ) {
            if( empty( $post_id ) ) {
                return;
            }

            $inputs = array(
                '_text_field_important_informations',
                '_text_field_gathering_point',
            );

            foreach( $inputs as $idx => $name ) {
                $value = esc_attr( filter_input( INPUT_POST, $name ) );
                update_post_meta( $post_id, $name, $value );
            }
        }
    }
endif;

// Initialize our plugin
if( ! ODWP_WC_Krx_Custom_Fields_2::requirements_check() )
{
    ODWP_WC_Krx_Custom_Fields_2::deactivate_raw();

    if( is_admin() ) {
        add_action( 'admin_head', ['ODWP_WC_Krx_Custom_Fields_2', 'requirements_error'] );
    }

    exit();
}
else
    /**
     * @var ODWP_WC_Krx_Custom_Fields_2 $ODWP_WC_Krx_Custom_Fields_2
     */
    $ODWP_WC_Krx_Custom_Fields_2 = new ODWP_WC_Krx_Custom_Fields_2();
