<?php
/**
 * File Contains code to define the Better_Menu_Items class.
 *
 * @package BETTER_MENUS
 */

namespace BETTER_MENUS;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class defines the table and queries used by the Better Menu Items Object type.
 */
class Better_Menu_Items extends Base_DB {
	/**
	 * Get things started
	 */
	public function __construct() {
		global $wpdb;
		$this->table_name  = $wpdb->prefix . 'menu_items';
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
			url varchar(255),
			label varchar(30),
			type varchar(30),
			object_id bigint(20),
			menu_id bigint(20),
			post_id bigint(20) NOT NULL,
			parent bigint(20),
			menu_order bigint(20),
			PRIMARY KEY (id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;';

			$tets = dbDelta( $sql );

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
			'id'         => '%d',
			'url'        => '%s',
			'label'      => '%s',
			'type'       => '%s',
			'object_id'  => '%d',
			'menu_id'    => '%d',
			'post_id'    => '%d',
			'parent'     => '%d',
			'menu_order' => '%d',
		);
	}

	/**
	 * Get default column values
	 *
	 * @return array
	 */
	public function get_column_defaults() {
		return array(
			'parent'     => 0,
			'url'        => '',
			'label'      => '',
			'type'       => '',
			'object_id'  => '',
			'menu_id'    => '',
			'post_id'    => '',
			'menu_order' => 0,
		);
	}

	/**
	 * Get menu items by the WordPress Menu ID.
	 *
	 * @param [int] $post_id WP Term ID.
	 * @return object
	 */
	public function get_menu_item_by_wp_id( $post_id ) {
		return $this->get_by( 'post_id', $post_id );
	}

	/**
	 * Update Better Menu item by Wp Post ID.
	 *
	 * @param [int]   $post_id WP Post ID.
	 * @param [array] $data An array of data to update.
	 * @return void
	 */
	public function update_better_menu_item( $post_id, $data ) {
		$menu_item = $this->get_menu_item_by_wp_id( intval( $post_id ) );

		if ( $menu_item && ! empty( $data ) ) {
			$this->update( intval( $menu_item->id ), $data );
		}
	}
}
