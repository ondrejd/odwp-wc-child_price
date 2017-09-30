<?php
/**
 * Plugin Name: Dětské slevy
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
         * @static
         */
        public static function activate() {
            //...
        }

        /**
         * @internal Deactivates the plugin.
         * @return void
         * @since 1.0.0
         * @static
         */
        public static function deactivate() {
            //...
        }

        /**
         * @internal Deactivates plugin directly by updating WP option `active_plugins`.
         * @link https://developer.wordpress.org/reference/functions/deactivate_plugins/
         * @return void
         * @since 1.0.0
         * @static
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
         * @static
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
         * @static
         */
        public static function requirements_error() {
?>
<div id="odwpwckcf1_message" class="error notice is-dismissible">
    <p><?php _e( 'Plugin <b>Uživatelské hodnoty #1</b> vyžadují, aby byl nainstalován a aktivován plugin <b>WooCommerce</b> &ndash; plugin byl deaktivován.', 'odwp-wc-krx_child_price' ) ?></p>
</div>
<?php
        }

        /**
         * @internal Retrieves percentage discount.
         * @return int
         * @since 1.0.0
         * @static
         * @uses apply_filters
         * @uses get_option
         */
        public static function get_percentage_discount() {
            $key = 'child_price_percentage_discount';
			return ( int ) apply_filters( 'wc_option_' . $key, get_option( 'wc_settings_' . $key ) );
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
            add_filter( 'woocommerce_get_sections_products', array( $this, 'add_section' ), 98 );
            add_filter( 'woocommerce_get_settings_products', array( $this, 'add_section_settings' ), 10, 2 );
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
            $percentage_discount = self::get_percentage_discount();


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
            <?php printf(
                __( 'Paušální dětská sleva (<strong>%1$d %%</strong>, viz. <a href="%2$s">Nastavení</a>)', 'odwp-wc-krx_child_price' ),
                $percentage_discount,
                admin_url( 'admin.php?page=wc-settings&tab=products&section=child_price' )
            ) ?>
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

        /**
         * @internal Adds section into Products tab of WooCommerce settings page.
         * @param array $sections
         * @return array
         * @since 1.0.0
         */
        public function add_section( $sections ) {
            $sections['child_price'] = __( 'Dětské slevy', 'odwp-wc-krx_child_price' );
            return $sections;
        }

        /**
         * @internal Adds settings for our section of Products tab of WooCommerce settings page.
         * @param array $settings
         * @param string $current_section
         * @return array
         * @since 1.0.0
         */
        public function add_section_settings( $settings, $current_section ) {
            if( $current_section != 'child_price' ) {
                return $settings;
            }

            $section_settings = array();
            // Title and main description
        	$section_settings[] = array(
                'name' => __( 'Dětské slevy', 'odwp-wc-krx_child_price' ),
                'type' => 'title',
                'desc' => __( 'Následující volby jsou určeny pro rozšíření <strong>Dětské slevy</strong>', 'odwp-wc-krx_child_price' ),
                'id' => 'child_price'
            );
            // Percentage discount
        	$section_settings[] = array(
        		'name'     => __( 'Procentní sleva', 'odwp-wc-krx_child_price' ),
        		'desc_tip' => __( 'Procentní sleva, která bude použita pro výpočet dětských cen.', 'odwp-wc-krx_child_price' ),
        		'id'       => 'wc_settings_child_price_percentage_discount',
        		'type'     => 'text',
        		'desc'     => __( 'Zadejte celé číslo - např. 25.', 'odwp-wc-krx_child_price' ),
                'class'    => 'short',
        	);
        	// End of section
        	$section_settings[] = array(
                'type' => 'sectionend',
                'id' => 'child_price'
            );
        	return $section_settings;
        }
    }
endif;

// Initialize our plugin
if( ! ODWP_WC_Krx_Child_Price::requirements_check() )
{
    ODWP_WC_Krx_Child_Price::deactivate_raw();

    if( is_admin() ) {
        add_action( 'admin_head', array( 'ODWP_WC_Krx_Child_Price', 'requirements_error' ) );
    }

    exit();
}
else
    /**
     * @var ODWP_WC_Krx_Child_Price $ODWP_WC_Krx_Child_Price
     */
    $ODWP_WC_Krx_Child_Price = new ODWP_WC_Krx_Child_Price();
