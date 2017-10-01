#!/usr/bin/php
<?php
/**
 * Build script for the plugins. It creates bunch of Zip files named by
 * the plugin with version suffixed.
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/simple-woocommerce-plugins for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-small_plugins
 * @todo Enable script parameters in order to build just specified plugin instead of all!
 * @version 1.0.0
 */

if( ! class_exists( 'ODWP_WC_Plugins_Builder' ) ) :
    /**
     * Simple builder class.
     *
     * @since 1.0.0
     */
    class ODWP_WC_Plugins_Builder {
        /**
         * @var array $plugins
         * @since 1.0.0
         */
        protected $plugins;

        /**
         * Constructor.
         * @param array $plugins
         * @return void
         * @since 1.0.0
         */
        public function __construct( array $plugins ) {
            $this->plugins = $plugins;
        }

        /**
         * Builds all the plugins.
         * @return array Array with errors.
         * @since 1.0.0
         */
        public function build_all() {
            $errors = [];

            foreach( $this->plugins as $plugin => $version ) {
                print( 'Building plugin "' . $plugin . '".' . PHP_EOL );
            }

            return $errors;
        }

        /**
         * Builds specified plugin.
         * @param string $plugin Name of the plugin
         * @return array Array with errors.
         * @since 1.0.0
         */
        public function build_plugin( $plugin ) {
            $errors = [];

            if( empty( $plugin ) ) {
                $errors[] = 'Name of the plugin was not specified!';
                return $errors;
            }

            //...
            return $errors;
        }
    }
endif;

// Initialize builder
/**
 * @var ODWP_WC_Plugins_Builder $builder
 */
$builder = new ODWP_WC_Plugins_Builder( [
    'odwp-wc-krx_child_price'     => '1.0.0',
    'odwp-wc-krx_custom_fields_1' => '1.0.0',
    'odwp-wc-krx_custom_fields_2' => '1.0.0',
    'odwp-wc-krx_trip_duration'   => '1.0.0',
] );

// Build plugins and print errors
foreach( $builder->build_all() as $err ) {
    print( $err . PHP_EOL );
}
