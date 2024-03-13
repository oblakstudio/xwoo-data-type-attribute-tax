<?php
/**
 * Attribute Taxonomy Data Type utility functions.
 *
 * @package eXtended WooCommerce
 * @subpackage Attribute Taxonomy
 */

use XWC\Attribute_Tax;

/**
 * Get a product attribute ID by label.
 *
 * @param  string $label Attribute label.
 * @return int
 */
function xwc_attribute_tax_id_by_label( ?string $label ): int {
    if ( ! $label ) {
        return 0;
    }

    return ( new XWC\Data_Query(
        array(
            'att_label' => $label,
            'data_type' => 'attribute_tax',
            'fields'    => 'ids',
            'per_page'  => 1,
        ),
    ) )->get_object();
}

/**
 * Get an Attribute Taxonomy object.
 *
 * @param  mixed          $id  Attribute ID.
 * @param  int|false|null $def Default value.
 * @return Attribute_Tax|int|false|null
 */
function xwc_get_attribute_tax( mixed $id, int|false|null $def = false ): Attribute_Tax|int|false|null {
    return xwc_get_data( $id, 'attribute_tax', $def );
}

/**
 * Get an Attribute Taxonomy object by ID.
 *
 * @param  int $id Attribute ID.
 * @return Attribute_Tax
 */
function xwc_get_attribute_tax_object( int $id ): Attribute_Tax {
    return xwc_get_data_instance( $id, 'attribute_tax' );
}
