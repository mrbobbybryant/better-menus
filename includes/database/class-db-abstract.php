<?php
/**
 * File defines the base DB class that all better menu object extend.
 *
 * @package namespace BETTER_MENUS;
 */

namespace BETTER_MENUS;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Base DB Class.
 */
abstract class Base_DB {
	/**
	 * The name of our database table
	 *
	 * @var [string]
	 */
	public $table_name;

	/**
	 * The version of our database table
	 *
	 * @var [string]
	 */
	public $version;

	/**
	 * The name of the primary column
	 *
	 * @var [string]
	 */
	public $primary_key;

	/**
	 * Get things started
	 */
	public function __construct() {}

	/**
	 * Whitelist of columns
	 */
	public function get_columns() {
		return array();
	}

	/**
	 * Default column values
	 */
	public function get_column_defaults() {
		return array();
	}

	/**
	 * Retrieve a row by the primary key
	 *
	 * @param [int] $row_id DB Row Primary Key.
	 * @return object
	 */
	public function get( $row_id ) {
		global $wpdb;
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}
	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @param [string] $column Column Name.
	 * @param [int]    $row_id Row ID.
	 * @return object
	 */
	public function get_by( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a row by a specific column / value
	 *
	 * @param [string] $column Column Name.
	 * @param [array]  $row_ids An array of IDs.
	 * @return array
	 */
	public function get_all( $column, $row_ids ) {
		global $wpdb;
		$column = esc_sql( $column );
		$ids    = implode( ', ', $row_ids );
		$menus = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE $column in (%s);", $ids ) );

		if ( null === $menus ) {
			return false;
		}

		return ( is_array( $menus ) ) ? $menus : [ $menus ];
	}

	/**
	 * Retrieve a specific column's value by the primary key
	 *
	 * @param [string] $column Column Name.
	 * @param [type]   $row_id Row ID.
	 * @return object
	 */
	public function get_column( $column, $row_id ) {
		global $wpdb;
		$column = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $this->primary_key = %s LIMIT 1;", $row_id ) );
	}

	/**
	 * Retrieve a specific column's value by the the specified column / value
	 *
	 * @param [string] $column
	 * @param [string] $column_where
	 * @param [mixed]  $column_value
	 * @return string
	 */
	public function get_column_by( $column, $column_where, $column_value ) {
		global $wpdb;
		$column_where = esc_sql( $column_where );
		$column       = esc_sql( $column );
		return $wpdb->get_var( $wpdb->prepare( "SELECT $column FROM $this->table_name WHERE $column_where = %s LIMIT 1;", $column_value ) );
	}

	/**
	 * Insert a new row
	 *
	 * @param [array] $data An Array of row data.
	 * @return int
	 */
	public function insert( $data ) {
		global $wpdb;

		// Set default values.
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		// Initialise column format array.
		$column_formats = $this->get_columns();

		// Force fields to lower case.
		$data = array_change_key_case( $data );
		// White list columns.
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data.

		$data_keys      = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );
		return $wpdb->insert_id;
	}

	/**
	 * Update a row
	 *
	 * @access  public
	 * @since   2.1
	 * @return  bool
	 */
	public function update( $row_id, $data = array(), $where = '' ) {
		global $wpdb;
		// Row ID must be positive integer
		$row_id = absint( $row_id );
		if( empty( $row_id ) ) {
			return false;
		}
		if( empty( $where ) ) {
			$where = $this->primary_key;
		}
		// Initialise column format array
		$column_formats = $this->get_columns();
		// Force fields to lower case
		$data = array_change_key_case( $data );
		// White list columns
		$data = array_intersect_key( $data, $column_formats );
		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );
		if ( false === $wpdb->update( $this->table_name, $data, array( $where => $row_id ), $column_formats ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Delete a row identified by the primary key
	 *
	 * @access  public
	 * @since   2.1
	 * @return  bool
	 */
	public function delete( $row_id = 0 ) {
		global $wpdb;
		// Row ID must be positive integer
		$row_id = absint( $row_id );
		if( empty( $row_id ) ) {
			return false;
		}
		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Check if the given table exists
	 *
	 * @since  2.4
	 * @param  string $table The table name
	 * @return bool          If the table name exists
	 */
	public function table_exists() {
		global $wpdb;
		$table = sanitize_text_field( $this->table_name );
		return $wpdb->get_var( $wpdb->prepare( "SHOW TABLES LIKE '%s'", $table ) ) === $this->table_name;
	}

	public function get_menus() {
		global $wpdb;
		$table = sanitize_text_field( $this->table_name );
		return $wpdb->get_results( "SELECT * FROM $table;" );
	}
}
