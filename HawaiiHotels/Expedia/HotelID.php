<?php
namespace HawaiiAloha\PostType\HawaiiHotels\Expedia;

use HawaiiAloha\PostType\HawaiiHotels\HawaiiHotels;

final class HotelID {

	/**
	 * HotelID constructor.
	 */
	public function __construct() {

		add_action( 'cmb2_admin_init', [ $this, 'metabox' ] );
	}

	/**
	 * Define the metabox and field configurations.
	 */
	public function metabox() {
		
		/**
		 * Initialize the metabox
		 */
		$cmb = new_cmb2_box( array(
			'id'           => 'expedia_hotel_id',
			'title'        => __( 'Expedia Hotel ID', 'cmb2' ),
			'object_types' => array( 'hawaii-hotels', ), // Post type
			'context'      => 'side',
			'priority'     => 'high',
			'closed'       => true,
			'show_names'   => false, // Show field names on the left
			'show_on_cb'    => [ $this, 'excludeFromPages' ],
		) );

		// Regular text field
		$cmb->add_field( array(
			'name'            => __( 'Expedia Hotel ID', 'cmb2' ),
			'id'              => '_expedia_hotel_id',
			'type'            => 'text_small',
			'sanitization_cb' => [ $this, 'sanitize' ], // custom sanitization callback parameter
			'on_front'        => false, // Optionally designate a field to wp-admin only
			'attributes'  => array(
				'placeholder' => 'Must be 6 numbers',
			),
			'render_row_cb' => [$this, 'renderRow'],
		) );
		
	}

	/**
	 * Overrides the default render field method
	 * Allows you to add custom HTML before and after a rendered field
	 *
	 * @param  array             $field_args Array of field parameters
	 * @param  \CMB2_Field object $field      Field object
	 */
	function renderRow( array $field_args, \CMB2_Field $field ) {

		// If field is requesting to not be shown on the front-end
		if ( ! is_admin() && ! $field->args( 'on_front' ) ) {
			return null;
		}

		// If field is requesting to be conditionally shown
		if ( ! $field->should_show() ) {
			return null;
		}

		$field->peform_param_callback( 'before_row' );

		// Remove the cmb-row class
		printf( '<div class="%s">', $field->row_classes() );

		if ( ! $field->args( 'show_names' ) ) {

			// If the field is NOT going to show a label output this
			echo '<div class="cmb-td">';
			$field->peform_param_callback( 'label_cb' );

		} else {

			// Otherwise output something different
			if ( $field->get_param_callback_result( 'label_cb', false ) ) {
				echo '<div class="cmb-th">', $field->peform_param_callback( 'label_cb' ), '</div>';
			}
			echo '<div class="cmb-td">';
		}

		$field->peform_param_callback( 'before' );

		// The next two lines are key. This is what actually renders the input field
		$field_type = new \CMB2_Types( $field );
		$field_type->render();

		$field->peform_param_callback( 'after' );

		echo '</div></div>';

		$field->peform_param_callback( 'after_row' );

		// For chaining
		return $field;
	}
	
	public function sanitize( $meta_value, array $args, \CMB2_Field $field ) {
		
		if ('Expedia Hotel ID' === $field->args["name"]){
			
			$value = trim($meta_value);
			
			if(!is_numeric($value) || 6 != strlen($value)){
				$meta_value = '';
			}
		}
		return $meta_value;
	}

	public function excludeFromPages( \CMB2 $cmb )
	{
		$hotelIslands = HawaiiHotels::islandPages();

		return  ! in_array( $cmb->object_id(), $hotelIslands );
	}
}
