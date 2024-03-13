<?php //phpcs:disable Squiz.Commenting.FunctionComment.MissingParamTag, Squiz.Commenting.VariableComment
/**
 * Attribute_Taxonomy class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Attribute Taxonomy
 */

namespace XWC;

use WC_Product_Attribute;
use XWC\Decorators\Data_Type_Config;
use XWC\Decorators\Data_Type_Definition;
use XWC\Decorators\Data_Type_Structure;

/**
 * Attribute Taxonomy class.
 *
 * Based on `Extended_Data` class.
 * Provides standardized methods for attribute taxonomy data.
 *
 * !Setters
 *
 * @method void set_label( string $label ) Set the label.
 * @method void set_name( string $name ) Set the name.
 * @method void set_orderby( string $orderby ) Set the orderby.
 * @method void set_public( int $public ) Set the attribute public flag
 * @method void set_type( string $type ) Set the attribute selector type
 *
 * !Getters
 *
 * @method string get_label( string $context='view' ) Get the label.
 * @method string get_name( string $context='view' ) Get the name.
 * @method string get_orderby( string $context='view' ) Get the orderby.
 * @method int    get_public( string $context='view' ) Get the public.
 * @method string get_type( string $context='view' ) Get the type.
 */
#[Data_Type_Definition(
    name: 'attribute_tax',
    config: array(
        'data_store'   => Attribute_Tax_Data_Store::class,
        'factory'      => Attribute_Tax_Factory::class,
        'object_type'  => 'attribute-tax',
        'query_prefix' => 'att',
    ),
    structure: array(
        'columns'       => array(
            'label'   => array( 'type' => 'string' ),
            'name'    => array( 'type' => 'string' ),
            'orderby' => array(
                'default' => 'menu_order',
                'type'    => 'string',
			),
            'public'  => array( 'type' => 'int' ),
            'type'    => array(
                'default' => 'select',
                'type'    => 'string',
			),
        ),
        'column_prefix' => 'attribute_',
        'id_field'      => 'attribute_id',
        'table'         => '{{PREFIX}}woocommerce_attribute_taxonomies',
        'table_prefix'  => 'woocommerce_',
	),
)]
class Attribute_Tax extends Data {
    /**
     * Data type
     *
     * @var string $data_type
     */
    protected string $data_type = 'attribute_tax';

    protected array $unique_keys = array(
        'name',
    );

    /**
     * Create an attribute taxonomy from a label.
     *
     * @param string $label Label.
     */
    public static function from_label( string $label ): static {
        $att = \xwc_get_attribute_tax( $label );

        if ( ! $att ) {
            $att = \xwc_get_attribute_tax_object( 0 );
            $att->set_label( $label );
            $att->save();
            $att->get_data_store()->read( $att );
        }

        return $att;
    }

    /**
     * {@inheritDoc}
     */
    public function __call( $name, $arguments ) {
        $name = \str_replace( 'attribute_', '', $name );

        return parent::__call( $name, $arguments );
    }

    /**
     * Backport for ID getter.
     *
     * @return int
     */
    public function get_attribute_id() {
        return $this->get_id();
    }

    /**
     * Backport for ID setter.
     *
     * @param  int $id ID.
     */
    public function set_attribute_id( $id ) {
        return $this->set_id( $id );
    }

    /**
     * If the context is db, we need to prefix the keys with 'attribute_'
     *
     * @param  string $context Context.
     * @return array           Core data.
     */
    public function get_core_data( string $context = 'view' ): array {
        if ( 'db' === $context ) {
            return \array_combine(
                \array_map(
                    static fn( $k ) => "attribute_$k",
                    $this->get_core_data_keys(),
                ),
                parent::get_core_data( $context ),
            );
        }

        return parent::get_core_data( $context );
    }

    /**
     * Get the taxonomy name.
     *
     * @return string
     */
    public function get_taxonomy_name() {
        return \wc_attribute_taxonomy_name( $this->get_name() );
    }

    /**
     * Get the `WC_Product_Attribute` object.
     *
     * @param  int                $position      Attribute position.
     * @param  array<int, string> $options       Attribute options.
     * @param  bool               $for_variation If is used for variations.
     * @param  bool               $visible       If is visible on Product's additional info tab.
     * @return WC_Product_Attribute
     */
    public function get_wc_attribute( int $position, array $options = array(), bool $for_variation = true, bool $visible = true ): WC_Product_Attribute {
        $att = new WC_Product_Attribute();

        $att->set_id( $this->get_id() );
        $att->set_name( $this->get_taxonomy_name() );
        $att->set_position( $position );
        $att->set_visible( $visible );
        $att->set_variation( $for_variation );
        $att->set_options( $options );

        // We use get_terms to get the term ids.
        $terms = \wp_list_pluck( $att->get_terms(), 'term_id' );
        $att->set_options( $terms );

        return $att;
    }
}
