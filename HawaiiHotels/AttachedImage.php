<?php

namespace HawaiiAloha\PostType\HawaiiHotels;

use HawaiiAloha\PostType\HawaiiHotels\Expedia\HotelImages;
use Timber\Timber;

class AttachedImage {

	/**
	 * HotelData constructor.
	 */
	public function __construct() {

		add_action( 'cmb2_admin_init', [ $this, 'metabox' ] );
		add_action( 'cmb2_before_post_form_attached_hotel_images', [ $this, 'HTML' ], 10, 2 );
	}

	/**
	 * Define the metabox and field configurations.
	 */
	public function metabox() {

		/**
		 * Initialize the metabox
		 */
		$cmb = new_cmb2_box( [
			'id'           => 'attached_hotel_images',
			'title'        => __( 'Attached Images', 'cmb2' ),
			'object_types' => [ 'hawaii-hotels' ], // Post type
			'context'      => 'side',
			'priority'     => 'low',
			'show_names'   => false, // Show field names on the left
			'closed'       => false, // Metabox is closed by default
			'show_on_cb'   => [ 'HawaiiAloha\PostType\HawaiiHotels\HawaiiHotels', 'excludeFromPages' ],
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

		$images   = get_attached_media( 'image', $object_id );
		$featured = get_post_thumbnail_id( $object_id );

		$images = $this->addAttachmentURLs( $images );

		$this->shiftFeaturedImage( $images, $featured );

		if ( 0 !== count( $images ) ) {

			add_thickbox();

			// Then we render it!
			Timber::render( 'attached-images.twig', [ 'images' => $images, 'featured' => $featured ] );

		} else {

			echo "<p>No Attached Images</p>";
		}
	}

	private function shiftFeaturedImage( array &$images, $featured ) {

		foreach ( $images as $key => $image ) {

			if ( $featured == $image->ID ) {

				unset( $images[ $key ] );
				array_unshift( $images, $image );
			}
		}
	}

	private function addAttachmentURLs( array $images ) {

		foreach ( $images as $key => $image ) {

			$image          = (array) $image;
			$image['URL']   = str_replace(
				home_url( '/app/' ),
				'https://www.hawaii-aloha.com/wp-content/',
				wp_get_attachment_image_src( $image['ID'], 'full' )
			);
			$images[ $key ] = (object) $image;

		}

		return $images;
	}
}
