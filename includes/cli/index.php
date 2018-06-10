<?php
/**
 * File contains code for all WP CLI scripts included in better custom fields.
 *
 * @package BETTER_MENUS;
 */

namespace BETTER_MENUS;

if ( true === class_exists( 'WP_CLI_Command' ) ) {
	/**
	 * Class defines a WPCLI command used to import user data from EPIServer.
	 */
	class WP_CLI_Better_Menus_Command extends \WP_CLI_Command {

		/**
		 * Command Version.
		 *
		 * @var [string]
		 */
		private $version = ARCHSYSTEMS_VERSION;

		/**
		 * Function imports all users and their various metadata.
		 *
		 * ## OPTIONS
		 *
		 * [--flags=<flags>]
		 * : additional commandline flags
		 *
		 * ## EXAMPLES
		 *
		 * wp better-menus sync
		 *
		 * @since 0.0.1
		 * @when before_wp_load
		 * @synopsis [--flags=<flags>]
		 */
		public function sync() {
			$menus = get_terms( 'nav_menu', [
				'hide_empty' => false,
			] );

			$menus = array_reduce( $menus, function( $acc, $menu ) {
				$args = [
					'post_type' => 'nav_menu_item',
					'tax_query' => [
						[
							'taxonomy' => 'nav_menu',
							'field'    => 'term_taxonomy_id',
							'terms'    => $menu->term_taxonomy_id,
						],
					],
				];

				$menu_items = new \WP_Query( $args );

				if ( is_wp_error( $menu_items ) || ! $menu_items->have_posts() ) {
					return $acc;
				}

				$menu->items = array_map( function( $menu_item ) {
					$meta = get_post_meta( $menu_item->ID );
					$test = 0;
					return [
						'url'        => $meta['_menu_item_url'][0],
						'label'      => $menu_item->post_title,
						'type'       => $meta['_menu_item_type'][0],
						'object_id'  => intval( $meta['_menu_item_object_id'][0] ),
						'post_id'    => $menu_item->ID,
						'parent'     => intval( $meta['_menu_item_menu_item_parent'][0] ),
						'menu_order' => intval( $menu_item->menu_order ),
					];
				}, $menu_items->posts );

				$locations = get_theme_mod( 'nav_menu_locations' );

				foreach ( $locations as $name => $menu_id ) {
					if ( $menu_id !== $menu->term_id ) {
						continue;
					}

					$menu->location = $name;
				}

				$menu->menu_id = $menu->term_id;

				$acc[] = $menu;
				return $acc;
			}, [] );

			$better_menus      = new Better_Menus();
			$better_menu_items = new Better_Menu_Items();

			foreach ( $menus as $menu ) {
				$menu_id = $better_menus->insert( $menu );

				foreach ( $menu->items as $item ) {
					$item['menu_id'] = $menu_id;
					$better_menu_items->insert( $item );
				}
				stop_the_insanity();
			}

			\WP_CLI::success( 'Better Menus: Sync Complete.' );
		}
	}

	\WP_CLI::add_command( 'better-menus', __NAMESPACE__ . '\WP_CLI_Better_Menus_Command' );
}

/**
 * Helper Function used by WPCLI commands to make long running intense queries more managable for
 * WordPress.
 *
 * @return void
 */
function stop_the_insanity() {
	global $wpdb, $wp_object_cache, $wp_actions;

	$wpdb->queries = array();

	$wp_actions = array();

	if ( is_object( $wp_object_cache ) ) {
		$wp_object_cache->group_ops      = array();
		$wp_object_cache->stats          = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache          = array();

		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset();
		}
	}

	\WP_CLI\Utils\wp_clear_object_cache();
}
