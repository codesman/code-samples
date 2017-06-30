<?php

namespace HawaiiAloha\PostType\HawaiiHotels\Expedia;

use HawaiiAloha\PostType\HawaiiHotels\HawaiiHotels;
use Timber\Timber;

final class HotelData extends \stdClass {

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
		add_action( 'cmb2_before_post_form_expedia_hotel_data_metabox', [ $this, 'HTML' ], 10, 2 );
	}

	/**
	 * Define the metabox and field configurations.
	 */
	public function metabox() {

		/**
		 * Initialize the metabox
		 */
		$cmb = new_cmb2_box( [
			'id'           => 'expedia_hotel_data_metabox',
			'title'        => __( 'Expedia Hotel Data', 'cmb2' ),
			'object_types' => [ 'hawaii-hotels' ], // Post type
			'context'      => 'normal',
			'priority'     => 'low',
			'show_names'   => false, // Show field names on the left
			'closed'       => true, // Metabox is closed by default
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
		
		if(is_numeric($hotelId)) {
			
			$query   = sprintf( "SELECT * FROM HawaiiPropertyList WHERE EANHotelID=%s", $hotelId );
			$hotel   = $this->db->get_row( $query );

			// Then we render it!
			Timber::render( 'hotel-form-data.twig', [ 'hotel' => $hotel ] );
			
		} else {

			Timber::render( 'no-hotel-data.twig' );
		}
	}

	/**
	 * Timber will call this to add our templates dir to it's locations array
	 * 
	 * @return string
	 */
	public static function templateDir() {
		
		return __DIR__ . '/templates';
	}


	/**
	 * We don't want to show our metabox on pages that are Hotel Island Archives
	 * 
	 * @param \CMB2 $cmb
	 *
	 * @return bool
	 */
	public function excludeFromPages( \CMB2 $cmb )
	{
		$hotelIslands = HawaiiHotels::islandPages();

		return  ! in_array( $cmb->object_id(), $hotelIslands );
	}
}
