<?php

namespace HawaiiAloha\PostType\HawaiiHotels;

/**
 * Class PageFormat
 * @package HawaiiAloha\PostType\HawaiiHotels
 *
 * Adds a Page Format option to Hotel Pages
 */
final class PageFormat {

	/**
	 * PageFormat constructor.
	 */
	public function __construct() {

		add_action( 'cmb2_admin_init', [ $this, 'metabox' ], 10 );
	}

	/**
	 * Bootstraps our Page Format metabox
	 * Hotel Pages can be either 'none' or 'island' format
	 *
	 * @return void
	 */
	public function metabox() {

		$metabox = new_cmb2_box( [
			'id'           => 'hotel-pageformat',
			'title'        => __( 'Page Format', 'cmb2' ),
			'object_types' => [ 'hawaii-hotels' ], // Post type
			'context'      => 'side',
			'priority'     => 'low',
			'show_names'   => false, // Show field names on the left
		] );

		$metabox->add_field( array(
			'name'          => 'Hotel Page Format',
			'id'            => '_hotel-pageformat',
			'type'          => 'radio_inline',
			'options'       => array(
				'none'   => __( 'Hotel', 'cmb2' ),
				'island' => __( 'Island', 'cmb2' ),
			),
			'default'       => 'none',
			'render_row_cb' => [ 'HawaiiAloha\CMB2\RenderCB', 'withoutRow' ],
		) );
	}
}
