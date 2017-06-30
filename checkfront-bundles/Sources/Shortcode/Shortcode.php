<?php
namespace Checkfront_Bundles;

final class Shortcode {
	
	/**
	 * Shortcode constructor.
	 */
	public function __construct() {

		add_shortcode( 'bundle', [ $this, 'shortcode' ] );

		add_action( 'wp_ajax_tour_starting_availability', [ $this, 'tour_starting_availability' ] );
		add_action( 'wp_ajax_nopriv_tour_starting_availability', [ $this, 'tour_starting_availability' ] );

		add_action( 'wp_ajax_get_tour_availability', [ $this, 'get_tour_availability' ] );
		add_action( 'wp_ajax_nopriv_get_tour_availability', [ $this, 'get_tour_availability' ] );

		add_action( 'wp_ajax_create_booking_session', [ $this, 'create_booking_session' ] );
		add_action( 'wp_ajax_nopriv_create_booking_session', [ $this, 'create_booking_session' ] );

		add_action( 'wp_ajax_create_booking', [ $this, 'create_booking' ] );
		add_action( 'wp_ajax_nopriv_create_booking', [ $this, 'create_booking' ] );
	}

	/**
	 * Bootstraps the shortcode
	 * 
	 * @param array $atts
	 *
	 * @return string
	 */
	public function shortcode( array $atts ) {

		wp_enqueue_script( 'jquery-ui-datepicker', 'jquery-ui-core' );
		wp_enqueue_script( 'checkfront-bundles' );
		wp_enqueue_style( 'jquery-ui-css' );
		wp_enqueue_style( 'cangas-datepicker-css' );

		$name           = '' !== $atts['name'] ? $atts['name'] : "";
		$regularPricing = Pricing::prices( );

		// Localize the script with regular pricing info
		$pricing = array(
			'regular' => $regularPricing,
		);
		wp_localize_script( 'checkfront-bundles', 'pricing', $pricing );

		// Return the form HTML
		return call_user_func( [$this, $name], $name );
	}

	/**
	 * Oahu & Kauai Bundle HTML
	 * 
	 * @return string HTML
	 */
	public function oahu_kauai() {

		$details = call_user_func( [$this, 'quantity_and_pricing'] );
		$details .= Item_Fields::html( 1 ); // Oahu Circle Island
		$details .= Item_Fields::html( 32 ); // Kauai Circle Island
		
		return Bundle_Details::html( $details);
	}

	/**
	 * Oahu/Pearl Harbor Bundle HTML
	 *
	 * @return string HTML
	 */
	public function oahu_pearl_harbor() {

		$details = call_user_func( [$this, 'quantity_and_pricing'] );
		$details .= Item_Fields::html( 1 ); // Oahu Circle Island
		$details .= Item_Fields::html( 21 ); // 4-in-1

		return Bundle_Details::html( $details);
	}

	/**
	 * Triple Island Bundle HTML
	 * 
	 * @return string HTML
	 */
	public function triple_island_1() {

		$details = call_user_func( [$this, 'quantity_and_pricing'] );
		$details .= Item_Fields::html( 1 ); // Oahu Circle Island
		$details .= Item_Fields::html( 32 ); // Kauai Circle Island
		$details .= Item_Fields::html( 43 ); // Big Island Off Roading Tour

		return Bundle_Details::html( $details);
	}

	/**
	 * Quantity Fields & Pricing info HTML
	 * 
	 * @return string HTML
	 */
	public function quantity_and_pricing() {
		return Quantity_Fields::html() . Pricing::html();
	}

	/**
	 * Calculates a Date that is 2 days from today
	 * 
	 * @return string HTML
	 */
	public function two_days_from_today() {

		$date = new \DateTime();
		$date->setTimezone( new \DateTimeZone( 'Pacific/Honolulu' ) );
		$date->add( new \DateInterval( 'P2D' ) );

		return $date->format( 'Ymd' );
	}

	/**
	 * Calculates a date that is one month from 2 days after today!
	 * 
	 * @return string HTML
	 */
	public function thirty_one_days_from_start() {

		$start = $this->two_days_from_today();

		$end = new \DateTime( $start, new \DateTimeZone( 'Pacific/Honolulu' ) );
		$end->add( new \DateInterval( 'P1M' ) );

		return $end->format( 'Ymd' );
	}

	/**
	 * Calls the CheckFront API to create a booking
	 * 
	 * @return \WP_Ajax_Response
	 */
	public function create_booking() {

		if ( '' == $_POST['form'] ) {
			wp_send_json_error( [ 'message' => 'No Form Data Sent' ] );
		}

		parse_str( $_POST["form"], $form );

		$bundle = new CheckFront_Bundles();

		$base_url = $bundle->api_url() . "booking/create";

		$data = [
			'headers' => $bundle->auth_header(),
			'body'    => [
				'session_id' => get_option( 'checkfront_booking_session_id' ),
				'form'       => $form,
			],
		];

		$booking = json_decode( wp_remote_retrieve_body( wp_remote_post( $base_url, $data ) ) );
		$error   = $booking->request->status;

		if ( "ERROR" == $error ) {

			wp_send_json_error();

		}

		$booking_id = $booking->booking->id;

		$data = [
			'headers' => $bundle->auth_header(),
		];

		$mobile_url = "https://jeeptourshawaii.checkfront.com/api/3.0/mobile/booking/$booking_id";

		$mobile_request = json_decode( wp_remote_retrieve_body( wp_remote_get( $mobile_url, $data ) ) );

		$booking_token = $mobile_request->token;

		$payment_url = "https://jeeptourshawaii.checkfront.com/reserve/booking/$booking_id?CFX=$booking_token&view=pay";

		wp_send_json_success( $payment_url );
	}

	/**
	 * Calls the CheckFront API and creates a booking session
	 * 
	 * @return \WP_Ajax_Response
	 */
	public function create_booking_session() {

		if ( '' == $_GET['params'] ) {
			wp_send_json_error( [ 'message' => 'No Params Sent' ] );
		}

		parse_str( $_GET['params'], $params );

		$dates = is_array( $params["dates"] ) ? array_map( [ $this, 'convert_date' ], $params["dates"] ) : [];

		$adult_quantity = '' !== $params["adult_quantity"] ? $params["adult_quantity"] : '';
		$child_quantity = '' !== $params["child_quantity"] ? $params["child_quantity"] : '';

		$items = $this->format_items( $dates, $adult_quantity, $child_quantity );
		$bundle = new CheckFront_Bundles();
		$base_url = $bundle->api_url() . "booking/session";

		$args = [
			'headers' => $bundle->auth_header(),
			'body'    => $this->get_slips( $items ),
		];

		$session = json_decode( wp_remote_retrieve_body( wp_remote_post( $base_url, $args ) ) );

		update_option( 'checkfront_booking_session_id', $session->booking->session->id );

		$form = Booking_Form::html( $items, $session );

		wp_send_json_success( $form );
	}

	/**
	 * Creates an array of request parameters for CheckFront
	 * 
	 * @param array $dates
	 * @param int $adult_quantity
	 * @param int $child_quantity
	 *
	 * @return array
	 */
	private function format_items( array $dates, int $adult_quantity, int $child_quantity ) {

		$items = [];

		foreach ( $dates as $id => $date ) {

			$items[ $id ] = [
				'date'  => $date,
				'adult' => $adult_quantity,
				'child' => $child_quantity,
			];
		}

		return $items;
	}

	/**
	 * Converts a date string from 'm/d/Y' to 'Ymd'
	 * 
	 * @param string $date
	 *
	 * @return false|string
	 */
	private function convert_date( string $date ) {

		return date_format( date_create_from_format( 'm/d/Y', $date ), 'Ymd' );
	}

	/**
	 * Converts a date string from 'Ymd' to 'm/d/Y'
	 * 
	 * @param string $date
	 *
	 * @return false|string
	 */
	private function convert_date_for_picker( string $date ) {

		return date_format( date_create_from_format( 'Ymd', $date ), 'm/d/Y' );
	}

	/**
	 * Parses the CheckFront Booking Object for item Slips
	 * 
	 * @param array $items
	 *
	 * @return array
	 */
	private function get_slips( array $items ) {

		$rated = $this->get_rated_response( $items );

		$slips = [];

		foreach ( $rated as $id => $object ) {
			$slips[] = $object->items->{$id}->rate->slip;
		}

		return [ 'slip' => $slips ];
	}

	/**
	 * Calls the CheckFront API to get 'rated' info for Tour Items
	 * 
	 * @param array $items
	 *
	 * @return array
	 */
	public function get_rated_response( array $items ) {

		$rated = [];

		$bundle = new CheckFront_Bundles();

		$base_url = $bundle->api_url() . "item";

		foreach ( $items as $id => $item ) {
			
			switch ($id) {
				case 43:
					$discount = 'bundle10';
					break;
				case 1:
				case 21:
					$discount = 'bundle15';
					break;
				default:
					$discount = 'bundle20';
			}

			$params = [
				'start_date'    => $item["date"],
				'item_id'       => $id,
				'discount_code' => $discount,
				'param'         => [
					'ticket'   => $item["adult"],
					'children' => $item["child"],
				],

			];

			$url = $base_url . "?" . http_build_query( $params );

			$args = [
				'headers' => $bundle->auth_header(),
			];

			$rated[ $id ] = json_decode( wp_remote_retrieve_body( wp_remote_request( $url, $args ) ) );
		}

		set_transient( 'rated_response', $rated );

		return $rated;
	}
	
	/**
	 * Calls CheckFront API to get Tour Availability
	 * 
	 * @return \WP_Ajax_Response
	 */
	public function get_tour_availability() {

		$start_date = '' != $_POST['start_date'] ? $_POST['start_date'] : '';
		$end_date   = '' != $_POST['end_date'] ? $_POST['end_date'] : '';

		$bundle = new CheckFront_Bundles();

		$base_url = $bundle->api_url() . "item/cal";

		$params = [
			'start_date'  => $start_date,
			'end_date'    => $end_date,
			'category_id' => 1,
		];

		$url = $base_url . "?" . http_build_query( $params );

		$args = [
			'headers' => $bundle->auth_header(),
		];

		$response = json_decode( wp_remote_retrieve_body( wp_remote_request( $url, $args ) ) );

		$items = $this->convert_available_dates( $response->items );
		
		wp_send_json_success( $items );
	}

	/**
	 * Prepares an array of dates to be passed to jQuery Datepicker for availability
	 * 
	 * @param $dates
	 *
	 * @return object
	 */
	private function convert_available_dates( $dates ) {

		$result = [];

		foreach ( $dates as $k => $date ) {
			$dayObject = [];

			foreach ( $date as $day => $v ) {

				if ( is_numeric( $day ) ) {

					$convertedDay = $this->convert_date_for_picker( $day );

					$dayObject[ $convertedDay ] = $v;
				}
			}

			$result[ $k ] = (object) $dayObject;
		}

		return (object) $result;
	}
}

new Shortcode();
