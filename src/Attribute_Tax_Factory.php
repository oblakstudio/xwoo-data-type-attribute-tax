<?php
/**
 * Attribute_Tax_Factory class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Attribute Taxonomy
 */

namespace XWC;

use WC_Product_Attribute;

/**
 * Attribute Taxonomy factory class.
 *
 * @method Attribute_Tax|false get_attribute_tax(mixed $attribute_id)  Get an attribute taxonomy by ID.
 * @method Attribute_Tax       get_attribute_tax_instance(int $id) Create a new instance of the attribute taxonomy.
 * @method static string get_attribute_tax_classname(int $attribute_id)  Get the attribute taxonomy class name.
 */
class Attribute_Tax_Factory extends Data_Object_Factory {
    /**
     * Determines the attribute ID
     *
     * @param  string|int|WC_Product_Attribute|false $att_id Attribute ID, Attribute object or Attribute name / slug.
     * @return int|false
     */
    protected function get_attribute_tax_id( mixed $att_id ): int|false {
        return match ( true ) {
            \is_string( $att_id )                   => $this->get_attribute_tax_by_string( $att_id ),
            \is_numeric( $att_id )                  => (int) $att_id,
            $att_id instanceof WC_Product_Attribute => $att_id->get_id(),
            $att_id instanceof Attribute_Tax        => $att_id->get_id(),
            default                                 => false,
        };
    }

    /**
     * Get the attribute ID by name or label
     *
     * @param  string $ident Attribute name / slug.
     * @return int
     */
    public function get_attribute_tax_by_string( string $ident ): int {
        $att_id = \wc_attribute_taxonomy_id_by_name( $ident );

        return $att_id ? $att_id : \xwc_attribute_tax_id_by_label( $ident );
    }
}
