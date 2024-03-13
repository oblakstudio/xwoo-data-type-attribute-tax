<?php
/**
 * Attribute_Tax_Hooks class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Attribute Taxonomy
 */

namespace XWC\Dependency;

use XWC\Data_Type_Dependency;

/**
 * Hooks needed for the attribute taxonomy.
 */
class Attribute_Tax_Hooks extends Data_Type_Dependency {
    /**
     * Initializes the dependencies.
     *
     * Does nothing in this case.
     */
    public function initialize(): void {
        // Do nothing.
    }

    /**
     * Hooks fired after registration
     */
    public function add_hooks(): void {
        \add_action( 'woocommerce_attribute_added', array( $this, 'register_attribute_taxonomy' ), 10, 2 );
    }

    /**
     * Remove hooks on deactivation
     */
    public function remove_hooks() {
        \remove_action( 'woocommerce_attribute_added', array( $this, 'register_attribute_taxonomy' ), 10 );
    }

    /**
     * Register the attribute taxonomy on attribute creation.
     *
     * @param  int                  $attribute_id          The attribute id.
     * @param  array<string, mixed> $data The attribute data.
     */
    public function register_attribute_taxonomy( $attribute_id, $data ) {
        $taxonomy = \wc_attribute_taxonomy_name( $data['attribute_name'] );
        $args     = array(
            array(
                'hierarchical' => true,
                'labels'       => array( 'name' => $data['attribute_label'] ),
                'query_var'    => true,
                'rewrite'      => false,
                'show_ui'      => false,
            ),
        );

        if ( \taxonomy_exists( $taxonomy ) ) {
            return;
        }

        \register_taxonomy(
			$taxonomy,
            // Documented in woocommerce.
			\apply_filters( 'woocommerce_taxonomy_objects_' . $taxonomy, array( 'product' ) ),
            // Documented in woocommerce.
			\apply_filters( 'woocommerce_taxonomy_args_' . $taxonomy, $args ),
		);
    }
}
