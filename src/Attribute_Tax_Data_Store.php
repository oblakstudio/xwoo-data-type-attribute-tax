<?php //phpcs:disable Squiz.Commenting.VariableComment.MissingVar
/**
 * Attribute_Tax_Data_Store class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Attribute Taxonomy
 */

namespace XWC;

/**
 * Data store class for attribute taxonomies.
 */
class Attribute_Tax_Data_Store extends Data_Store_CT {
    /**
     * Get the data type.
     *
     * @return string The data type.
     */
    protected function get_data_type() {
        return 'attribute_tax';
    }

    /**
     * Reformats data for insert and update.
     *
     * Functions `wc_create_attribute` and `wc_update_attribute` expect data in a different format:
     * * `label` => `name`
     * * `name` => `slug`
     *
     * So we remap the keys, and remove the label key.
     *
     * @param  Attribute_Tax $data Data object.
     * @return array
     */
    protected function reformat_data( Attribute_Tax &$data ): array {
        $args = \array_merge(
            array( 'id' => $data->get_id() ),
            $data->get_core_data(),
        );

        $args['name'] = $args['label'];
        $args['slug'] = $args['name'];

        unset( $args['label'] );

        return \array_filter(
            $args,
            static fn( $v ) => '' !== $v,
        );
    }

    /**
     * Creates a new attribute.
     *
     * We override this method to handle the WooCommerce way of creating attributes.
     *
     * @param  Attribute_Tax $data The attribute to create.
     */
    public function create( &$data ) {
        $args = $this->reformat_data( $data );
        $id   = \wc_create_attribute( $args );

        if ( ! $id || \is_wp_error( $id ) ) {
            return;
        }

        $data->set_id( $id );

        $this->update_entity_meta( $data, true );
        $this->handle_updated_props( $data );
        $this->clear_caches( $data );

        $data->save_meta_data();
        $data->apply_changes();
    }

    /**
     * Updates an attribute.
     *
     * We override this method to handle the WooCommerce way of updating attributes.
     *
     * @param  Attribute_Tax $data The attribute to update.
     */
    public function update( &$data ) {
        $changes = $data->get_changes();
        $ch_keys = \array_intersect( \array_keys( $changes ), $data->get_core_data_keys() );

        if ( $ch_keys ) {
            $args = \wp_array_diff_assoc( $this->reformat_data( $data ), array( 'id' ) );
            $ret  = \wc_update_attribute( $data->get_id(), $args );

            if ( ! $ret || \is_wp_error( $ret ) ) {
                return;
            }
        }

        $this->update_entity_meta( $data );
        $this->handle_updated_props( $data );
        $this->clear_caches( $data );

        $data->save_meta_data();
        $data->apply_changes();
    }

    /**
     * Deletes an attribute.
     *
     * We override this method to handle the WooCommerce way of deleting attributes.
     *
     * @param  Attribute_Tax $data The attribute to delete.
     * @param  array         $args Additional arguments.
     */
    public function delete( &$data, $args = array() ) {
        if ( ! \wc_delete_attribute( $data->get_id() ) ) {
            return;
        }

        $this->delete_entity_meta( $data->get_id() );
    }

    /**
     * Checks if a value is unique.
     *
     * Each column in the table is prefixed with 'attribute_' so we need to prefix the keys in the where clause.
     *
     * @param  string $prop_or_column The property or column name.
     * @param  mixed  $value          The value to check.
     * @param  int    $current_id     The current ID.
     * @return bool                   Whether the value is unique.
     */
    public function is_value_unique( string $prop_or_column, $value, int $current_id ): bool {
        $prop_or_column = \str_starts_with(
            $prop_or_column,
            'attribute_',
        ) ? $prop_or_column : 'attribute_' . $prop_or_column;

        return parent::is_value_unique( $prop_or_column, $value, $current_id );
    }

    /**
     * Gets an attribute by its name.
     *
     * @param  string $taxonomy_name   The attribute name.
     * @param  string $ret             The return type.
     * @return int|Attribute_Tax|null
     */
    public function get_by_taxonomy_name( string $taxonomy_name, string $ret = 'object' ): int|Attribute_Tax|null {
        $att = ( new Data_Query(
            array(
                'att_name' => \str_replace( 'pa_', '', $taxonomy_name ),
                'per_page' => 1,
            ),
        ) )->objects[0] ?? null;

        return 'object' === $ret ? $att : $att?->get_id() ?? 0;
    }
}
