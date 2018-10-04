<?php
/**
 * File bootstraps the core plugin files.
 *
 * @package namespace BETTER_MENUS;
 */

namespace BETTER_MENUS;

require_once 'database/index.php';
require_once 'cli/index.php';

/**
 * Function bootstraps the whole thing.
 *
 * @return void
 */
function setup() {
	$better_menus = new Better_Menus();
	$better_menus->create_table();

	$better_menu_items = new Better_Menu_Items();
	$better_menu_items->create_table();

	add_action( 'wp_create_nav_menu', __NAMESPACE__ . '\create_better_menu', 10, 2 );
	add_action( 'wp_update_nav_menu', __NAMESPACE__ . '\update_better_menu' );
	add_action( 'wp_delete_nav_menu', __NAMESPACE__ . '\delete_better_menu', 10, 1 );
	add_filter( 'pre_set_theme_mod_nav_menu_locations', __NAMESPACE__ . '\update_better_menus_location', 10, 2 );
	add_action( 'wp_insert_post', __NAMESPACE__ . '\create_better_menu_item', 10, 3 );
	add_action( 'save_post', __NAMESPACE__ . '\update_better_menu_item', 10, 2 );
	add_action( 'after_delete_post', __NAMESPACE__ . '\delete_better_menu_item' );
	add_action( 'added_term_relationship', __NAMESPACE__ . '\add_item_to_better_menu', 10, 2 );
}

function update_better_menu( $menu_id ) {
	$menu_data    = get_term_by( 'id', $menu_id, 'nav_menu' );
	$better_menus = new Better_Menus();
	$menu         = $better_menus->get_menu_by_wp_id( $menu_id );

	if ( $menu && $menu_data ) {
		if ( $menu->name !== $menu_data->name ) {
			$better_menus->update(
				intval( $menu->id ),
				[
					'name' => $menu_data->name,
					'slug' => sanitize_title( $menu_data->slug ),
				]
			);
		}
	}
	$test = 0;
}

function create_better_menu( $menu_id, $menu_data ) {
	$better_menus = new Better_Menus();

	$better_menus->insert([
		'name'    => $menu_data['menu-name'],
		'slug'    => sanitize_title( $menu_data['menu-name'] ),
		'menu_id' => $menu_id,
	]);
}

function delete_better_menu( $menu_id ) {
	$better_menus = new Better_Menus();
	$menu         = $better_menus->get_menu_by_wp_id( $menu_id );

	if ( $menu ) {
		$better_menus->delete( $menu->id );
	}
}

function update_better_menus_location( $value, $old_value ) {
	if ( ! is_array( $value ) ) {
		return $value;
	}

	$menu_ids     = array_values( $value );
	$b_menus      = new Better_Menus();
	$better_menus = $b_menus->get_menus();

	if ( empty( $better_menus ) ) {
		return $values;
	}

	/**
	 * Remove existing menu locations.
	 */
	foreach ( $better_menus as $better_menu ) {
		if ( empty( $better_menu->location ) ) {
			continue;
		}

		$current_location = $value[ $better_menu->location ];

		if ( $current_location !== intval( $better_menu->location ) ) {
			$b_menus->update( intval( $better_menu->id ), [ 'location' => '' ] );
		}
	}

	/**
	 * Add new menu locations.
	 */
	foreach ( $value as $name => $menu_id ) {
		$menu = array_values( array_filter( $better_menus, function( $better_menu ) use ( $menu_id ) {
			return intval( $better_menu->menu_id ) === $menu_id;
		} ) );

		if ( ! empty( $menu ) ) {
			$b_menus->update( intval( $menu[0]->id ), [ 'location' => $name ] );
		}
	}

	return $value;
}

function create_better_menu_item( $post_id, $post, $update ) {
	if ( 'nav_menu_item' !== $post->post_type ) {
		return false;
	}

	if ( $update ) {
		return false;
	}

	$menu_data = array_values( $_POST['menu-item'] );

	if ( $menu_data ) {
		$better_menu_items = new Better_Menu_Items();

		$better_menu_items->insert([
			'url'        => $menu_data[0]['menu-item-url'],
			'label'      => $menu_data[0]['menu-item-title'],
			'type'       => $menu_data[0]['menu-item-type'],
			'object_id'  => intval( $menu_data[0]['menu-item-object-id'] ),
			'post_id'    => intval( $post_id ),
			'parent'     => intval( $menu_data[0]['menu-item-parent-id'] ),
			'menu_order' => intval( $post->menu_order ),
		]);
	}
}

function update_better_menu_item( $post_id, $post ) {
	if ( 'nav_menu_item' === $post->post_type ) {
		$better_menu_items = new Better_Menu_Items();
		$item_id           = $better_menu_items->get_menu_item_by_wp_id( intval( $post_id ) );

		if ( $item_id ) {
			$data = [
				'url'        => isset( $_POST['menu-item-url'][ $post_id ] ) ? $_POST['menu-item-url'][ $post_id ] : '',
				'label'      => isset( $_POST['menu-item-title'][ $post_id ] ) ? $_POST['menu-item-title'][ $post_id ] : '',
				'parent'     => isset( $_POST['menu-item-parent-id'][ $post_id ] ) ? intval( $_POST['menu-item-parent-id'][ $post_id ] ) : 0,
				'menu_order' => isset( $_POST['menu-item-position'][ $post_id ] ) ? intval( $_POST['menu-item-position'][ $post_id ] ) : 1,
			];

			$better_menu_items->update_better_menu_item( $post_id, $data );
		}
	}
}

function delete_better_menu_item($post_id) {
	$better_menu_items = new Better_Menu_Items();
	$item_id           = $better_menu_items->get_menu_item_by_wp_id( intval( $post_id ) );

	if ( $item_id ) {
		$better_menu_items->delete( intval( $item_id->id ) );
	}

}

function add_item_to_better_menu( $post_id, $tt_id ) {
	$better_menus      = new Better_Menus();
	$better_menu_items = new Better_Menu_Items();
	$menu_id           = $better_menus->get_menu_by_wp_id( intval( $tt_id ) );

	if ( $menu_id ) {
		$better_menu_items->update_better_menu_item( $post_id, [ 'menu_id' => intval( $menu_id->id ) ] );
	}
}
