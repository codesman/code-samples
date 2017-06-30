<?php
namespace HawaiiAloha\PostType\HawaiiHotels\Expedia;

use HawaiiAloha\PostType\HawaiiHotels\HawaiiHotels;
use Timber\Timber;

final class HotelImages {
	/**
	 * @var \wpdb
	 */
	private $db;

	/**
	 * HotelData constructor.
	 */
	public function __construct() {

		$this->db = Database::connection();

		add_action( 'cmb2_admin_init', [ $this, 'metabox' ] );
		add_action( 'cmb2_before_post_form_expedia_hotel_images_metabox', [ $this, 'HTML' ], 10, 2 );
	}

	/**
	 * Define the metabox and field configurations.
	 */
	public function metabox() {

		/**
		 * Initialize the metabox
		 */
		$cmb = new_cmb2_box( [
			'id'           => 'expedia_hotel_images_metabox',
			'title'        => __( 'Expedia Hotel Images', 'cmb2' ),
			'object_types' => [ 'hawaii-hotels' ], // Post type
			'context'      => 'normal',
			'priority'     => 'low',
			'show_names'   => false, // Show field names on the left
			'closed'       => false, // Metabox is closed by default
			'show_on_cb'    => [ $this, 'excludeFromPages' ],
		] );
	}

	/**
	 * Renders the HTML for the Metabox
	 *
	 * @param string $object_id
	 * @param \CMB2 $cmb
	 *
	 * @return Void
	 */
	public function HTML( string $object_id, \CMB2 $cmb ) {

		// We need to build an object that contains this Hotel's properties
		$hotelId = get_post_meta( $object_id, '_expedia_hotel_id', true );

		if ( is_numeric( $hotelId ) ) {

			global $post;
			$featuredSource = '';

			if ( has_post_thumbnail( $object_id ) ) {
				
				$thumbID = get_post_thumbnail_id($object_id);
				$thumb = get_post($thumbID);
				$featuredSource = $thumb->post_content;
			}
			
			$query = $this->db->prepare( "SELECT * FROM HawaiiHotelImageList WHERE EANHotelID=%s", $hotelId );
			$images = $this->db->get_results( $query, OBJECT );
			
			add_thickbox();

			$this->shiftDefaultImage( $images );
			$this->findAttached( $object_id, $images );
			
			// Then we render it!
			Timber::render( 'hotel-images.twig', [ 'images' => $images, 'post' => $post, 'featuredSource' => $featuredSource ] );

		} else {

			Timber::render( 'no-hotel-data.twig' );
		}
	}

	/**
	 * Finds the Expedia DefaultImage and moves it to the top of the array
	 *
	 * @param &array $images
	 *
	 * @return void
	 */
	private function shiftDefaultImage( array &$images ) {

		foreach ( $images as $key => $image ) {

			if ( '1' === $image->DefaultImage ) {

				unset( $images[ $key ] );
				array_unshift( $images, $image );
			}
		}
	}

	private function findAttached( $postID, array &$images ) {
		
		$attachedSourceURLs = array_flip( array_map(
			[ $this, 'extractSourceURL' ],
			get_attached_media( 'image', $postID )
		) ) ;
		
		array_walk( $images, [ $this, 'isAttached' ], $attachedSourceURLs );
	}

	private function extractSourceURL( \WP_Post $item ) {

		return $item->post_content;
	}

	private function isAttached( &$item, $key, $images ) {
				
		$obj = clone $item;
		$url = str_replace( ['http','b.jpg'], ['https','z.jpg'], $item->URL );

		if ( array_key_exists( $url, $images ) ) {

			$obj->isAttached = true;
		}

		$item = $obj;
	}

	public function excludeFromPages( \CMB2 $cmb )
	{
		$hotelIslands = HawaiiHotels::islandPages();

		return  ! in_array( $cmb->object_id(), $hotelIslands );
	}
}
