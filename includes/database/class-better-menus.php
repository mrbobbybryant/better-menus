<?php
/**
 * File contains code which defines the Better Menus Class.
 *
 * @package BETTER_MENUS;
 */

namespace BETTER_MENUS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class defines the table and interface with the Better Menus Object table.
 */
class Better_Menus extends Base_DB {
	/**
	 * Get things started
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name  = $wpdb->prefix . 'menus';
		$this->primary_key = 'id';
		$this->version     = '1.0';
	}

	/**
	 * Create the table
	 *
	 * @return void
	 */
	public function create_table() {

		if ( ! $this->table_exists() ) {
			global $wpdb;
			require_once ABSPATH . 'wp-admin/includes/upgrade.php';

			$sql = 'CREATE TABLE ' . $this->table_name . ' (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			name varchar(30) NOT NULL,
			slug varchar(30) NOT NULL,
			location varchar(30) NOT NULL,
			menu_id bigint(20),
			PRIMARY KEY  (id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;';

			dbDelta( $sql );

			update_option( $this->table_name . '_db_version', $this->version );
		}

	}

	/**
	 * Get columns and formats
	 *
	 * @return array
	 */
	public function get_columns() {
		return array(
			'id'       => '%d',
			'name'     => '%s',
			'slug'     => '%s',
			'location' => '%s',
			'menu_id'  => '%d',
		);
	}

	/**
	 * Get default column values
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'name'     => '',
			'slug'     => '',
			'location' => '',
			'menu_id'  => '',
		);
	}

	/**
	 * Function gets menu by WordPress Menu ID/
	 *
	 * @param [int] $wp_menu_id WP Term ID.
	 * @return object
	 */
	public function get_menu_by_wp_id( $wp_menu_id ) {
		return $this->get_by( 'menu_id', $wp_menu_id );
	}
}
