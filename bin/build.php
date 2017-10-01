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
 * @todo Enable clear parameter.
 * @todo Enable verbose parameter.
 * @todo Check if php-zip extension is enabled.
 * @version 1.0.0
 */

if( ! class_exists( 'ZipArchive' ) ) {
    print(
        'This script needs Zip extension installed!' . PHP_EOL .
        'See http://php.net/manual/en/book.zip.php for more details.' . PHP_EOL
    );
}

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
                $err = $this->build_plugin( $plugin );
                if( ! empty( $err ) ) {
                    $errors[] = $err;
                }
            }

            return $errors;
        }

        /**
         * Builds specified plugin.
         * @param string $plugin Name of the plugin
         * @return string An error.
         * @since 1.0.0
         */
        public function build_plugin( $plugin ) {
            if( empty( $plugin ) ) {
                return 'Name of the plugin was not specified!';
            }

            if( ! array_key_exists( $plugin, $this->plugins ) ) {
                return 'Wrong name "' . $plugin . '" of the plugin was specified!';
            }

            $basepath = dirname( dirname( __FILE__ ) );
            $version  = $this->plugins[$plugin];
            $phpfile  = "{$basepath}/plugins/{$plugin}.php";
            $zipfile  = "{$basepath}/bin/{$plugin}-{$version}.zip";

            if( empty( $version ) ) {
                $version = '0.0.1';
            }

            if( ! file_exists( $phpfile ) ) {
                return 'Source PHP file "' . $phpfile . '" does not exists!';
            }

            if( $this->create_zip( $plugin, $phpfile, $zipfile ) !== true ) {
                return 'Can not create ZIP archive "' . $zipfile . '"!';
            }

            return '';
        }

        /**
         * @internal Creates ZIP package.
         * @param string $plugin
         * @param string $phpfile
         * @param string $zipfile
         * @return boolean
         * @since 1.0.0
         */
        protected function create_zip( $plugin, $phpfile, $zipfile ) {
            $zip = new ZipArchive();

            if( $zip->open( $zipfile, ZipArchive::CREATE ) !== true ) {
                return false;
            }

            $zip->addFile( $phpfile );
            $zip->close();

            return true;
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
/**
 * @var array $errors
 */
$errors = $builder->build_all();
if( count( $errors ) > 0 ) {
    foreach( $errors as $err ) {
        print( $err . PHP_EOL );
    }
} else {
    print( 'No errors...' . PHP_EOL );
}
