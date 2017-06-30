<?php
namespace Checkfront_Bundles;

final class Bundle_Details {

	/**
	 * Details section of Booking Form
	 * 
	 * @param $details
	 *
	 * @return string
	 */
	public static function html( $details ) {
		global $post;
		
		return "
		<div id='bundle_booking_form'>
		<form id='booking_details'>
		<input type='hidden' name='bundle_id' value='{$post->ID}'>
		<h2>How many in your party?</h2>
		$details
			<br>
			<div class=''></div>
			<input type='submit' id='step_one' value='Continue'/>
		</form>
		</div>";
	}
}
