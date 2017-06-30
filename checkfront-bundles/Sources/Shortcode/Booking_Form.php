<?php
namespace Checkfront_Bundles;

final class Booking_Form {

	/**
	 * The booking form HTML
	 * 
	 * @param array $items
	 * @param \stdClass $session
	 *
	 * @return string HTML
	 */
	public static function html($items, \stdClass $session ) {

		if ( false === ( $booking_form = get_transient( 'checkfront_booking_form' ) ) ) {
			// It wasn't there, so regenerate the data and save the transient

			$bundle = new CheckFront_Bundles();

			$base_url = $bundle->api_url() . "booking/form";
	
			$args = [
				'headers' => $bundle->auth_header()
			];
			
			$booking_form = json_decode( wp_remote_retrieve_body( wp_remote_get( $base_url, $args ) ) );
			
			set_transient( 'checkfront_booking_form', $booking_form, 12 * HOUR_IN_SECONDS );
		}
		
		$form = $booking_form->booking_form_ui;
		$country_options = self::country_options($form->customer_country->define->layout->options);
		$guide_options = self::guide_options($form->guide_request->define->layout->options);
		$bundle_details = self::bundle_details($items, $session);
		$_session = $session->booking->session;
		$discount = number_format((float)$_session->discount, 2, '.', '');
		
		return "<h2>Bundle Details</h2>
				<div id='bundle_details'>
				<style>
					#bundle_details dt {
					font-size: 1.5em;
					font-weight: bold;
					}
					
					#bundle_details dt:not(:first-child) {
					margin-top: .5em;
					}
					
					#bundle_details dd {
					font-size: .8em;
					}
					
					#booking_form label {
						font-size: 1.3em!important;
					}
					
					#booking_form input,
					 #booking_form textarea {
						min-width: 75%;
						max-width: 100%;
					}
					
					.tip {
						font-size: .9em!important;
					}
				</style>
				<dl>$bundle_details</dl>
				<p>SubTotal: \${$_session->sub_total}<br>
				{$_session->tax->{1}->name}: \${$_session->tax_total}<br>
				<strong>Total: \${$_session->due}</strong> - You save more than <strong>\${$discount}</strong> with this bundle!</p>
				<p>When you are ready to proceed please fill out your details below to confirm your booking.</p>
				</div>
				<form id='booking_form'>
					{$form->avs->value}
					<span style=\"color: red;\">*</span> Fields are required
					<br><br>
					<label for='customer_name' class='form-required'>{$form->customer_name->define->layout->lbl}</label>
					<input type='text' name='customer_name' id='customer_name' required='required'>
					<label for='customer_email' class='form-required'>{$form->customer_email->define->layout->lbl}</label>
					<input type='email' name='customer_email' id='customer_email'>
					<label for='customer_phone'>{$form->customer_phone->define->layout->lbl}</label>
					<input type='text' name='customer_phone' id='customer_phone'>
					<label for='customer_address' class='form-required'>{$form->customer_address->define->layout->lbl}</label>
					<input type='text' name='customer_address' id='customer_address' required='required'>
					<label for='customer_city' class='form-required'>{$form->customer_city->define->layout->lbl}</label>
					<input type='text' name='customer_city' id='customer_city' required='required'>
					
					<label for='customer_country' class='form-required'>{$form->customer_country->define->layout->lbl}</label>
					<select name='customer_country' id='customer_country' required='required'>
						$country_options
					</select>
					<label for='customer_region'>State/Province</label>
					<input type='text' name='customer_region' id='customer_region'>
					<label for='customer_postal_zip' class='form-required'>{$form->customer_postal_zip->define->layout->lbl}</label>
					<input type='text' name='customer_postal_zip' id='customer_postal_zip' required='required'>
					
					<label for='ageweight' class='form-required'>{$form->ageweight->define->layout->lbl}</label>
					<textarea name='ageweight' id='ageweight' required='required'></textarea>
					<p class='tip'>{$form->ageweight->define->layout->tip}</p>
					
					<label for='hotel_pickup' class='form-required'>{$form->hotel_pickup->define->layout->lbl}</label>
					<textarea name='hotel_pickup' id='hotel_pickup' required='required'></textarea>
					<p class='tip'>{$form->hotel_pickup->define->layout->tip}</p>
					
					<label for='guide_request'>{$form->guide_request->define->layout->lbl}</label>
					<select name='guide_request' id='guide_request'>
						$guide_options
					</select>
					<p class='tip'>{$form->guide_request->define->layout->tip}</p>
					
					<label for='customer_email_optin'>
						<input type='checkbox' name='customer_email_optin' id='customer_email_optin' checked>
						<strong>{$form->customer_email_optin->define->layout->lbl}</strong>
					 </label>
					
					<input type='submit' id='step_two' value='Continue to Payment'>
				</form>";
	}

	/**
	 * Bundled tours itemization
	 * 
	 * @param array $items
	 * @param \stdClass $session
	 *
	 * @return string
	 */
	private static function bundle_details( array $items, \stdClass $session ) {
		
		ob_start();
		
		$_items = $session->booking->session->item;
		
		foreach ($_items as $item){

			if (array_key_exists($item->item_id, $items)){
				echo "<dt>{$item->name} - {$item->date->summary}</dt>
				<dd>{$item->rate->summary}</dd>";
			}
		}
		
		return ob_get_clean();
	}

	/**
	 * Select element of Countries
	 * 
	 * @param \stdClass $countries
	 *
	 * @return string
	 */
	private static function country_options( \stdClass $countries){

		ob_start();
		
		foreach ($countries as $id => $country) {
			
			$selected = 'US' === $id ? ' selected="selected"' : '';
			echo "<option value='$id'{$selected}>$country</option>";
		}
		
		return ob_get_clean();
	}

	/**
	 * Select element of available guides.
	 * 
	 * @param array $guides
	 *
	 * @return string
	 */
	public static function guide_options( array $guides ) {

		ob_start();

		foreach ($guides as $id => $guide) {

			$selected = '0' === $id ? ' selected="selected"' : '';
			echo "<option value='$id'{$selected}>$guide</option>";
		}

		return ob_get_clean();
	}
}
