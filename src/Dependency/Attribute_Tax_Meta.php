<?php
/**
 * Attribute_Tax_Meta class file.
 *
 * @package eXtended WooCommerce
 * @subpackage Attribute Taxonomy
 */

namespace XWC\Dependency;

use XWC\Data_Type_Dependency;

/**
 * Adds the attribute taxonomy meta table.
 */
class Attribute_Tax_Meta extends Data_Type_Dependency {
    /**
     * Hooks fired before registration
     */
    public function initialize(): void {
        $this->define_tables();
        $this->maybe_create_tables();
    }

    /**
     * Maybe create the tables
     */
    private function maybe_create_tables() {
        if ( 'yes' === \get_option( 'woocommerce_atsd_tables_created', 'no' ) ) {
            return;
        }

        $this->create_tables();
        $this->verify_tables();
    }

    /**
     * Runs the table creation
     */
    private function create_tables() {
        global $wpdb;

        $wpdb->hide_errors();

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        \dbDelta( $this->get_schema() );
    }

    /**
     * Verifies if the database tables have been created.
     *
     * @param  bool $execute       Are we executing table creation.
     * @return string[]            List of missing tables.
     */
    private function verify_tables( $execute = false ) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        if ( $execute ) {
            $this->create_tables();
        }

        $queries        = \dbDelta( $this->get_schema(), false );
        $missing_tables = array();

        foreach ( $queries as $table_name => $result ) {
            if ( "Created table {$table_name}" !== $result ) {
                continue;
            }

            $missing_tables[] = $table_name;
        }

        if ( 0 === \count( $missing_tables ) ) {
            \update_option( 'woocommerce_atsd_tables_created', 'yes' );
        }

        return $missing_tables;
    }

    /**
     * Get the table schema.
     */
    protected function get_schema() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            $collate = $wpdb->get_charset_collate();
        }

        $tables =
        "CREATE TABLE {$wpdb->attribute_taxonomymeta} (
            meta_id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            attribute_taxonomy_id bigint(20) UNSIGNED NOT NULL,
            meta_key varchar(255) DEFAULT NULL,
            meta_value longtext,
            PRIMARY KEY  (meta_id)
        ) {$collate};";

        return $tables;
    }

    /**
     * Defines the tables
     */
    private function define_tables() {
        global $wpdb;

        $tables = array(
            'attribute_taxonomymeta' => 'woocommerce_attribute_taxonomymeta',
        );

        foreach ( $tables as $name => $table ) {
            $wpdb->$name    = $wpdb->prefix . $table;
            $wpdb->tables[] = $table;
        }
    }
}
