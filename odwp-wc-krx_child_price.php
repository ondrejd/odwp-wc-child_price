<?php
/**
 * Plugin Name: Dětská sleva
 * Plugin URI: https://github.com/ondrejd/odwp-wc-child_price
 * Description: Přidá pro <strong>WooCommerce</strong> produkty ještě jednu cenu s možností slevy pro děti.
 * Version: 1.0.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.2
 * Tags: woocommerce,product,custom product fields
 * Donate link: https://www.paypal.me/ondrejd
 * Text Domain: odwp-wc-krx_child_price
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/simple-woocommerce-plugins for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-krx_child_price
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'ODWP_WC_Krx_Child_Price' ) ) :
    /**
     * Class that implements our plugin.
     *@since 1.0.0
     */
    class ODWP_WC_Krx_Child_Price {

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
                if( $val != 'odwp-wc-krx_child_price.php' ) {
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
<div id="odwpwckcf1_message" class="error notice is-dismissible">
    <p><?php _e( 'Plugin <b>Uživatelské hodnoty #1</b> vyžadují, aby byl nainstalován a aktivován plugin <b>WooCommerce</b> &ndash; plugin byl deaktivován.', 'odwp-wc-krx_child_price' ) ?></p>
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
            add_action( 'admin_head', array( $this, 'admin_head' ) );
            add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_general_panel_fields' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_product_meta' ) );
        }

        public function admin_head() {
            echo <<<HTML
<style type="text/css">
#odwpwcchp-child_price_custom {
    float: none;
    width: 78px !important;
}
</style>
HTML;
        }

        /**
         * @global WooCommerce $woocommerce
         * @global WP_Post $post
         * @internal Renders extra fields.
         * @return void
         * @since 1.0.0
         * @uses get_post_meta
         * @uses get_woocommerce_currency_symbol
         * @uses checked
         */
        public function add_general_panel_fields() {
            global $post;

            $child_price = get_post_meta( $post->ID, '_child_price', true );
            $child_price_custom = get_post_meta( $post->ID, '_child_price_custom', true );

            if( empty( $child_price ) ) {
                $child_price = 'none';
            }
?>
<div class="options_group">
    <p class="form-field custom_field_type">
        <label for="odwpwcchp-child_price1"><?php _e( 'Dětská sleva', 'odwp-wc-krx_child_price' ) ?></label>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-child_price1" name="_child_price" value="none" <?php echo checked( 'none', $child_price ) ?>>
            <?php _e( 'Žádná dětská sleva', 'odwp-wc-krx_child_price' ) ?>
        </span><br>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-child_price2" name="_child_price" value="fixed" <?php echo checked( 'fixed', $child_price ) ?>>
            <?php _e( 'Paušální dětská sleva (viz. <a href="#">Nastavení</a>)', 'odwp-wc-krx_child_price' ) ?>
        </span><br>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-child_price3" name="_child_price" value="custom" <?php echo checked( 'custom', $child_price ) ?>>
            <?php _e( 'Vlastní dětská cena: ', 'odwp-wc-krx_child_price' ) ?>
            <input class="short wc_input_price" type="text" id="odwpwcchp-child_price_custom" name="_child_price_custom" value="<?php echo $child_price_custom ?>"<?php if( $child_price != 'custom' ) { echo ' disabled="disabled"'; }; ?>>
            <?php echo get_woocommerce_currency_symbol() ?>
        </span>
    </p>
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
                '_child_price',
                '_child_price_custom',
            );

            foreach( $inputs as $idx => $name ) {
                $value = esc_attr( filter_input( INPUT_POST, $name ) );
                update_post_meta( $post_id, $name, $value );
            }
        }
    }
endif;

// Initialize our plugin
if( ! ODWP_WC_Krx_Child_Price::requirements_check() )
{
    ODWP_WC_Krx_Child_Price::deactivate_raw();

    if( is_admin() ) {
        add_action( 'admin_head', ['ODWP_WC_Krx_Child_Price', 'requirements_error'] );
    }

    exit();
}
else
    /**
     * @var ODWP_WC_Krx_Child_Price $ODWP_WC_Krx_Child_Price
     */
    $ODWP_WC_Krx_Child_Price = new ODWP_WC_Krx_Child_Price();
