<?php
/**
 * Plugin Name: Filtr dle atributů produktů pro ja-eshop.cz
 * Plugin URI: https://github.com/ondrejd/simple-woocommerce-plugins
 * Description: Přidává standartní <em>WP</em> widget s filtrem dle atributů produktů pro stránku obchodu. Plugin je dělán na míru e-shopu <a href="http://ja-eshop.cz/obchod" target="_blank">ja-eshop.cz</a> a <strong>WooCommerce</strong> verze <strong>3.2</strong> a vyšší a na jiných e-shopech či konfiguracích nemusí fungovat správně. Widget můžete přidat v administraci - stránka <code>Vzhled &gt; Widgety</code> kde do sekce <strong>Obchod</strong> přidejte widget <strong>Filtr dle atributů</strong>.
 * Version: 0.2.0
 * Author: Ondřej Doněk
 * Author URI: https://ondrejd.com/
 * License: GPLv3
 * Requires at least: 4.7
 * Tested up to: 4.8.2
 * Tags: woocommerce,product,custom product fields
 * Donate link: https://www.paypal.me/ondrejd
 * Text Domain: odwp-wc-product_attributes_filter
 *
 * @author Ondřej Doněk, <ondrejd@gmail.com>
 * @link https://github.com/ondrejd/simple-woocommerce-plugins for the canonical source repository
 * @license https://www.gnu.org/licenses/gpl-3.0.en.html GNU General Public License 3.0
 * @package odwp-wc-product_properties
 * @since 0.1.0
 *
 * @link https://codex.wordpress.org/Widgets_API for documentation on WordPress Widgets API.
 * @link https://developer.wordpress.org/reference/classes/wp_query/#taxonomy-parameters for documentation on {@see WP_Query} and filtering by taxonomy parameters.
 *
 * @todo Initialize localization!
 */

if( ! defined( 'ABSPATH' ) ) {
	exit;
}


if ( ! class_exists( 'ODWP_WC_Product_Attributes_Filter_Widget' ) ) :
	/**
	 * Class with the widget self.
	 * @author Ondřej Doněk, <ondrejd@gmail.com>
	 * @since 0.1.0
	 */
	class ODWP_WC_Product_Attributes_Filter_Widget extends WP_Widget {
	    /**
	     * @const string
	     */
	    const SLUG = 'odwp-wc-product_attributes_filter_widget';

		/**
		 * @var array
		 */
	    private static $filter;

		/**
		 * Constructor - sets up the widget
		 * @see WP_Plugin::__construct()
		 * @since 0.1.0
		 */
		public function __construct() {
			$widget_opts = [
				'classname'   => self::SLUG,
				'description' => $this->get_description(),
				'title'       => $this->get_title(),
			];

			parent::__construct(
				self::SLUG,
				$widget_opts['title'],
				$widget_opts
			);
		}

        /**
         * @return string Returns localized and escaped title of the widget.
         * @since 0.2.0
         */
	    protected function get_title() {
	        return esc_html__( 'Filtr dle atributů', 'odwpwcpaf' );
        }

        /**
         * @return string Returns localized and escaped description of the widget.
         * @since 0.2.0
         */
        protected function get_description() {
            return esc_html__( 'Filtr dle dodatečných atributů produktů.', 'odwpwcpaf' );
        }

		/**
		 * @global WPDB $wpdb
         * @return array Returns taxonomies (product attributes).
         * $since 0.1.0
		 */
		protected function get_attr_taxonomies() {
			global $wpdb;

			$table_name = "{$wpdb->prefix}woocommerce_attribute_taxonomies";
			$attr_taxonomies = $wpdb->get_results(
				"SELECT * FROM {$table_name} " .
				"WHERE attribute_name != '' " .
				"ORDER BY attribute_name ASC ;"
            );

			set_transient( 'wc_attribute_taxonomies', $attr_taxonomies );
			$attr_taxonomies = array_filter( $attr_taxonomies  );

            return $attr_taxonomies;
        }

		/**
         * @global WP $wp
		 * @return string Returns URL of the filter's form.
		 */
		protected function get_form_url() {
			global $wp;
			return add_query_arg( $_SERVER['QUERY_STRING'], '', home_url( $wp->request ) );
		}

		/**
		 * @return array Returns currently used filter (parsed from GET).
		 */
		public static function get_filter() {
		    if ( is_array( self::$filter ) ) {
		        return self::$filter;
            }

			self::$filter = filter_input( INPUT_GET, 'odwpwcpaf', FILTER_DEFAULT , FILTER_REQUIRE_ARRAY );

		    if ( ! is_array( self::$filter ) ) {
			    self::$filter = [];
			}

			return self::$filter;
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
				echo $args['before_title'] . esc_html__( $instance['title'] ) . $args['after_title'];
			}
			else if ( ! empty( $args['widget_name' ] ) ) {
				echo $args['before_title'] . esc_html__( $args['widget_name' ] ) . $args['after_title'];
            }

			$attr_taxonomies = $this->get_attr_taxonomies( $attr_taxonomies );

            echo '<div class="odwpwcpaf-product-sorting-form-cont">';
			echo '<form action="' . $this->get_form_url() . '" method="get" name="odwpwcpaf-product-sorting-form">';
            echo '<ul id="odwpwcpaf-product-sorting" class="odwpwcpaf-product-sorting">';

			foreach ( $attr_taxonomies as $taxonomy ) {
				$taxonomy_name = 'pa_' . $taxonomy->attribute_name;
				$terms = get_terms( [ 'taxonomy' => $taxonomy_name, 'hide_empty' => false ] );

				if ( ! is_array( $terms ) ) {
					continue;
				}

				if ( count( $terms ) <= 0 ) {
					continue;
				}

			    $this->widget_print_item( $taxonomy, $terms );
			}

			echo '</ul>';

			$filter = self::get_filter();
			$disabled = ( count( $filter ) == 0 ) ? ' disabled="disabled"' : '';

			echo '<div class="row odwpwcpaf-submit_row">' .
                    '<input type="submit" value="' . __( 'Filtrovat', 'odwpwcpaf' ) . '" name="odwpwcpaf-submit"' . $disabled . '>' .
                    '<input type="reset" value="' . __( 'Zrušit', 'odwpwcpaf' ) . '" name="odwpwcpaf-reset"' . $disabled . '>' .
                 '</div>';
			echo '</form>';
			echo '</div>';

			echo $args['after_widget'];
		}

		/**
         * @internal Prints single taxonomy filter.
		 * @param Object $taxonomy
		 */
		private function widget_print_item( $taxonomy, $terms ) {
			$taxonomy_name = 'pa_' . $taxonomy->attribute_name;
			$filter = self::get_filter();

			$classes = 'odwpwcpaf-product-sorting-item';
			if ( array_key_exists( $taxonomy_name, $filter ) ) {
				$classes .= ' open';
			}

			echo '<li class="' . $classes . '">' .
			     '<span>' . esc_html__( $taxonomy->attribute_label ) . '</span>' .
			     '<ul class="odwpwcpaf-product-sorting-sub">';

			foreach ( $terms as $term ) {
				$this->widget_print_subitem( $taxonomy, $term );
			}

			echo    '</ul>' .
                '</li>';
        }

		/**
         * @internal Prints single term of given taxonomy for the filter.
		 * @param Object $taxonomy
		 * @param WP_Term $term
		 */
		private function widget_print_subitem( $taxonomy, $term ) {
			if ( ! ( $term instanceof WP_Term ) ) {
				continue;
			}

			$taxonomy_name = 'pa_' . $taxonomy->attribute_name;
			$filter        = self::get_filter();
			$input_id      = 'odwpwcpaf-' . $taxonomy->attribute_name . '-' . $term->term_id;
			$input_name    = 'odwpwcpaf[' . $taxonomy_name . '][' . $term->term_id . ']';
			$checked       = '';

			if ( array_key_exists( $taxonomy_name, $filter ) ) {
				$checked = array_key_exists( $term->term_id, $filter[$taxonomy_name] ) ? ' checked="checked"' : '';
			}

			echo '<li class="odwpwcpaf-product-sorting-sub-item">' .
			     '<label for="' . $input_id . '">' .
			     '<input id="' . $input_id . '" name="' . $input_name . '" type="checkbox"' . $checked . '>' .
			     '<span>' . $term->name . '</span>' .
			     '</label>' .
			     '</li>';
		}

		/**
		 * Outputs the options form on admin.
		 * @param array $instance The widget options.
		 * @see WP_Plugin::form()
		 * @since 0.1.0
		 */
		public function form( $instance ) {
			$title = ! empty( $instance['title'] ) ? $instance['title'] : $this->get_title();
?>
			<p>
				<label for="<?=esc_attr( $this->get_field_id( 'title' ) )?>">
					<?php esc_attr_e( 'Název:', 'odwpwcpaf' ) ?>
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


add_action( 'widgets_init',
    /**
     * Registers our widget with filter.
     * @since 0.1.0
     */
    function() {
	    register_widget( 'ODWP_WC_Product_Attributes_Filter_Widget' );
    }
);


add_action( 'wp_head',
    /**
     * Updates WordPress head.
     * @since 0.1.0
     */
    function() {
?>
<style type="text/css">
/*
 * Filter by product attributes.
 */
.odwpwcpaf-product-sorting-item li, .odwpwccp-product-sorting-item li li { line-height: 1.25 !important; margin-bottom: 0 !important; }
.odwpwcpaf-product-sorting-item > span { color: #777777; cursor: pointer; font-weight: bold; }
.odwpwcpaf-product-sorting-item > span:hover { color: #282828; }
.odwpwcpaf-product-sorting-item > span::before { content: "– "; font-weight: bold; }
.odwpwcpaf-product-sorting-item > span:hover::before { content: "+ "; }
/*.odwpwcpaf-product-sorting-item.open > span::before { content: "+ "; }
.odwpwcpaf-product-sorting-item.open > span:hover::before { content: "– "; }*/
.odwpwcpaf-product-sorting-sub { padding-left: 10px; padding-top: 3px; display: none; }
.odwpwcpaf-product-sorting-item.open .odwpwcpaf-product-sorting-sub { display: block; }
.odwpwcpaf-product-sorting-sub-item label input[type="checkbox"] { position: relative; top: 2px; }
.odwpwcpaf-product-sorting-sub-item label span { display: inline-block; padding-left: 4px; }
.odwpwcpaf-submit_row input[type="submit"] { margin-top: 10px; }
.odwpwcpaf-submit_row input[name="odwpwcpaf-reset"] { background-color: transparent; border-radius: 0 none; border: 0 none; }
/*
 * Hide "Out of stock" message
 */
.products li.outofstock .nm-shop-loop-thumbnail > a::after { content: "" ! important; }
</style>
<?php
    }, 99
);


add_action( 'wp_footer',
    /**
     * Updates WordPress footer.
     * @since 0.1.0
     */
    function() {
?>
<script type="text/javascript">
jQuery( document ).ready( function( $ ) {
    $( ".odwpwcpaf-product-sorting-item > span" ).click( function( e ) {
        $( this ).next().slideToggle( function() {
            $( this ).parent().toggleClass( "open" );
        } );
    } );
    $( ".odwpwcpaf-product-sorting-item input:checkbox" ).change( function( e ) {
        $( ".odwpwcpaf-submit_row input" ).prop( "disabled", false );
    } );
} );
</script>
<?php
    }, 99
);


add_action( 'woocommerce_product_query',
    /**
     * Apply our product attributes filter on WooCommerce products query.
     * @param WP_Query $q
     * @since 0.1.0
     */
    function( WP_Query $q ) {
        $filter = ODWP_WC_Product_Attributes_Filter_Widget::get_filter();
        if ( count( $filter ) < 1 ) {
            return;
        }

        $tax_query = [];

        if ( count( $filter ) > 1 ) {
            $tax_query['relation'] = 'AND';
        }

        //echo '<!-- [woocommerce_product_query]::filter' . PHP_EOL . print_r( $filter, true ) . ' -->' . PHP_EOL;

        foreach ( $filter as $taxonomy => $terms ) {
	        $tax_query[] = [
	            'taxonomy'         => $taxonomy,
	            'terms'            => array_keys( $terms ),
	            'field'            => 'term_taxonomy_id',//name,slug,term_id,term_taxonomy_id
	            'operator'         => 'IN',
	            'include_children' => false,
	        ];
        }

        //echo '<!-- [woocommerce_product_query]::tax_query' . PHP_EOL . print_r( $tax_query, true ) . ' -->' . PHP_EOL;

        $q->set( 'tax_query', $tax_query );

    }, 99, 1
);


/**
 * Remove weight from list of a product's attributes (on product's detail page).
 */
add_filter( 'woocommerce_product_get_weight' , '__return_false' );
