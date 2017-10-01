<?php
/**
 * Plugin Name: Doba trvání
 * Plugin URI: https://github.com/ondrejd/simple-woocommerce-plugins
 * Description: Přidá pro <strong>WooCommerce</strong> produkty volitelnou vlastnost v podobě doby trvání (např. výletu).
 * Version: 1.0.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.2
 * Tags: woocommerce,product,custom product fields
 * Donate link: https://www.paypal.me/ondrejd
 * Text Domain: odwp-wc-krx_trip_duration
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/simple-woocommerce-plugins for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-krx_trip_duration
 * @since 1.0.0
 */

if( ! defined( 'ABSPATH' ) ) {
    exit;
}

if( ! class_exists( 'ODWP_WC_Krx_Trip_Duration' ) ) :
    /**
     * Class that implements our plugin.
     *@since 1.0.0
     */
    class ODWP_WC_Krx_Trip_Duration {

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
                if( $val != 'odwp-wc-krx_trip_duration.php' ) {
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
    <p><?php _e( 'Plugin <b>Doba trvnání</b> vyžaduje, aby byl nainstalován a aktivován plugin <b>WooCommerce</b> &ndash; plugin byl deaktivován.', 'odwp-wc-krx_trip_duration' ) ?></p>
</div>
<?php
        }

        /**
         * @internal Retrieves default trip duration.
         * @return int
         * @since 1.0.0
         * @static
         * @uses apply_filters
         * @uses get_option
         */
        public static function get_default_trip_duration() {
            $key = 'default_trip_duration';
            $val = get_option( 'wc_settings_' . $key );
			return apply_filters( 'wc_option_' . $key, $val );
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
#odwpwcchp-trip_duration_custom_days,
#odwpwcchp-trip_duration_custom_hours {
    float: none;
    width: 78px !important;
}
</style>
<script type="text/javascript">
</script>
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

            $trip_duration = get_post_meta( $post->ID, '_trip_duration', true );
            $duration_custom_hours = get_post_meta( $post->ID, '_trip_duration_custom_hours', true );
            $duration_custom_day = get_post_meta( $post->ID, '_trip_duration_custom_days', true );
            $default_duration = self::get_default_trip_duration();

            if( empty( $trip_duration ) ) {
                $trip_duration = 'none';
            }

            if( ! in_array( $trip_duration, array( 'none', 'fixed', 'custom_days', 'custom_hours' ) ) ) {
                $trip_duration = 'none';
            }
?>
<div class="options_group">
    <p class="form-field custom_field_type">
        <label for="odwpwcchp-trip_duration1"><?php _e( 'Trvání výletu', 'odwp-wc-krx_trip_duration' ) ?></label>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-trip_duration1" name="_trip_duration" value="none" <?php echo checked( 'none', $trip_duration ) ?>>
            <?php _e( 'Žádná doba trvání', 'odwp-wc-krx_trip_duration' ) ?>
        </span><br>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-trip_duration2" name="_trip_duration" value="fixed" <?php echo checked( 'fixed', $trip_duration ) ?>>
            <?php printf(
                __( 'Paušální doba trvání (<strong>%1$s</strong>, viz. <a href="%2$s">Nastavení</a>)', 'odwp-wc-krx_trip_duration' ),
                $default_duration,
                admin_url( 'admin.php?page=wc-settings&tab=products&section=trip_duration' )
            ) ?>
        </span><br>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-trip_duration3" name="_trip_duration" value="custom_hours" <?php echo checked( 'custom_hours', $trip_duration ) ?>>
            <?php _e( 'Vlastní doba v hodinách: ', 'odwp-wc-krx_trip_duration' ) ?>
            <input class="short wc_input_number" type="text" id="odwpwcchp-trip_duration_custom_hours" name="_trip_duration_custom_hours" value="<?php echo $duration_custom_hours ?>"<?php if( $trip_duration != 'custom' ) { echo ' disabled="disabled"'; }; ?>>
            <?php _e( 'hod.', 'odwp-wc-krx_trip_duration' ) ?>
        </span><br>
        <span class="wrap">
            <input type="radio" id="odwpwcchp-trip_duration4" name="_trip_duration" value="custom_days" <?php echo checked( 'custom_days', $trip_duration ) ?>>
            <?php _e( 'Vlastní doba ve dnech: ', 'odwp-wc-krx_trip_duration' ) ?>
            <input class="short wc_input_number" type="text" id="odwpwcchp-trip_duration_custom_days" name="_trip_duration_custom_days" value="<?php echo $duration_custom_days ?>"<?php if( $trip_duration != 'custom' ) { echo ' disabled="disabled"'; }; ?>>
            <?php _e( 'dní', 'odwp-wc-krx_trip_duration' ) ?>
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
                '_trip_duration',
                '_trip_duration_custom_days',
                '_trip_duration_custom_hours',
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
            $sections['trip_duration'] = __( 'Doba trvání', 'odwp-wc-krx_trip_duration' );
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
            if( $current_section != 'trip_duration' ) {
                return $settings;
            }

            $section_settings = array();
            // Title and main description
        	$section_settings[] = array(
                'name' => __( 'Doba trvání', 'odwp-wc-krx_trip_duration' ),
                'type' => 'title',
                'desc' => __( 'Následující volby jsou určeny pro rozšíření <strong>Doba trvání</strong>', 'odwp-wc-krx_trip_duration' ),
                'id' => 'trip_duration'
            );
            // Default trip duration
        	$section_settings[] = array(
        		'name'     => __( 'Defaultní doba trvání', 'odwp-wc-krx_trip_duration' ),
        		'desc_tip' => __( 'Zadejte defaultní dobu trvání, např. <strong>2 dny</strong>', 'odwp-wc-krx_trip_duration' ),
        		'id'       => 'wc_settings_default_trip_duration',
        		'type'     => 'text',
        		'desc'     => __( 'Zadejte krátký text - např. <strong>2 hodiny</strong>.', 'odwp-wc-krx_trip_duration' ),
                'class'    => 'short',
        	);
        	// End of section
        	$section_settings[] = array(
                'type' => 'sectionend',
                'id' => 'trip_duration'
            );
        	return $section_settings;
        }
    }
endif;

// Initialize our plugin
if( ! ODWP_WC_Krx_Trip_Duration::requirements_check() )
{
    ODWP_WC_Krx_Trip_Duration::deactivate_raw();

    if( is_admin() ) {
        add_action( 'admin_head', array( 'ODWP_WC_Krx_Trip_Duration', 'requirements_error' ) );
    }

    exit();
}
else
    /**
     * @var ODWP_WC_Krx_Trip_Duration $ODWP_WC_Krx_Trip_Duration
     */
    $ODWP_WC_Krx_Trip_Duration = new ODWP_WC_Krx_Trip_Duration();
