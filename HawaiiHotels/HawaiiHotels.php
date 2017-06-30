<?php

namespace HawaiiAloha\PostType\HawaiiHotels;

use HawaiiAloha\PostType\AbstractPostType;
use HawaiiAloha\PostType\HawaiiHotels\Expedia\HotelList;
use HawaiiAloha\PostType\HawaiiHotels\Image\Attached;
use HawaiiAloha\PostType\HawaiiHotels\Image\Sideload;
use HawaiiAloha\PostType\HawaiiHotels\PageFormat;
use HawaiiAloha\PostType\HawaiiHotels\Expedia\HotelData;
use HawaiiAloha\PostType\HawaiiHotels\Expedia\HotelID;
use HawaiiAloha\PostType\HawaiiHotels\Expedia\HotelImages;

final class HawaiiHotels extends AbstractPostType {

	/**
	 * Hawaii Hotels Post Type constructor.
	 *
	 * @return void
	 */
	public function __construct() {

		$this->slug         = 'hawaii-hotels';
		$this->labels       = $this->customLabels();
		$this->capabilities = $this->defaultCapabilties();
		$this->args         = $this->hotelArgs();

		add_action( 'init', [ $this, 'register' ], 10, 0 );
		add_action( 'init', [ $this, 'permastruct' ], 10, 0 );
		add_action( 'init', [ $this, 'rewriteFilter' ], 10, 0 );

		add_filter( 'post_type_link', [ $this, 'unslashPermalink' ], 10, 2 );
		add_filter( 'redirect_canonical', [ $this, 'cancelCanonicalRedirect' ] );

		new AdminColumns();
		new HotelID();
		new HotelData();
		new HotelImages();
		new HotelList();
		new AttachedImage();
		new Attached();
		new Sideload();
	}

	private function customLabels() {

		$singular   = "Hotel";
		$textdomain = "hawaii-hotels-textdomain";

		return [
			'name'               => _x( "$singular Pages", 'Post Type General Name', $textdomain ),
			'singular_name'      => _x( "$singular Page", 'Post Type Singular Name', $textdomain ),
			'menu_name'          => __( "{$singular}s", $textdomain ),
			'parent_item_colon'  => __( "Parent $singular Page:", $textdomain ),
			'all_items'          => __( "All $singular Pages", $textdomain ),
			'view_item'          => __( "View $singular Page", $textdomain ),
			'add_new_item'       => __( "Add New $singular Page", $textdomain ),
			'add_new'            => __( "New $singular Page", $textdomain ),
			'edit_item'          => __( "Edit $singular Page", $textdomain ),
			'update_item'        => __( "Update $singular Page", $textdomain ),
			'search_items'       => __( "Search $singular Pages", $textdomain ),
			'not_found'          => __( "No {$singular}s found", $textdomain ),
			'not_found_in_trash' => __( "No {$singular}s found in Trash", $textdomain ),
		];
	}

	/**
	 * Defines the setup parameters for this Post Type and assigns to an instance variable
	 *
	 * @return array
	 */
	private function hotelArgs() {

		return [
			'label'               => __( 'Hotels', 'hotels-textdomain' ),
			'description'         => __( 'Hawai Hotels', 'hotels-textdomain' ),
			'labels'              => $this->labels,
			'supports'            => array(
				'title',
				'editor',
				'thumbnail',
			),
			'hierarchical'        => true,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_nav_menus'   => true,
			'show_in_admin_bar'   => true,
			'menu_position'       => 1,
			'menu_icon'           => 'dashicons-admin-multisite',
			'can_export'          => true,
			'has_archive'         => false,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'capability_type'     => 'hawaii-hotels',
			'capabilities'        => $this->capabilities->get(),
			'map_meta_cap'        => true,
			'rewrite'             => [
				'slug' => 'hawaii-hotels',
			],
		];
	}


	/**
	 * Defines a permastruct for hawaii-videos post type
	 *
	 * @var \WP_Rewrite $wp_rewrite
	 *
	 * @return void
	 */
	public function permastruct() {

		global $wp_rewrite;

		$hotels_structure = '/hawaii-hotels/%hawaii-hotels%.html';
		$wp_rewrite->add_permastruct( 'hawaii-hotels', $hotels_structure, false );
	}

	/**
	 * Adds a filter to just the hawaii-videos post type rewrite rules
	 *
	 * @return void
	 */
	public function rewriteFilter() {

		add_filter( 'hawaii-hotels_rewrite_rules', [ &$this, 'rewriteRules' ] );
	}

	/**
	 * Removes unnecessary default WordPress rewrite rules
	 *
	 * @param array $rules
	 *
	 * @return array
	 */
	public function rewriteRules( array $rules ) {

		$rules = [
			'hawaii-hotels/big-island.html$' => 'index.php?page_id=29191',
			'hawaii-hotels/oahu.html$'       => 'index.php?page_id=29189',
			'hawaii-hotels/kauai.html$'      => 'index.php?page_id=29193',
			'hawaii-hotels/maui.html$'       => 'index.php?page_id=29190',
			'hawaii-hotels/lanai.html$'      => 'index.php?page_id=29192',
			'hawaii-hotels/([^/]+)\.html$'   => 'hawaii-hotels=$matches[1]',
		];

		return $rules;
	}

	public function cancelCanonicalRedirect( $redirect ) {

		$hotels = get_query_var( 'hawaii-hotels' );

		if ( ! empty( $hotels ) ) {

			$redirect = untrailingslashit( $redirect );

			return $redirect;
		}
	}

	public function unslashPermalink( $url, $post ) {

		if ( 'hawaii-hotels' === get_post_type( $post ) ) {
			return untrailingslashit( $url );
		}

		return $url;
	}

	public static function islandPages(): array {
		return [
			29189,
			29190,
			29191,
			29192,
			29193,
			29194,
		];
	}

	public function excludeFromPages( \CMB2 $cmb ) {
		return ! in_array( $cmb->object_id(), self::islandPages() );
	}
}
