<?php
namespace Checkfront_Bundles;

final class Item_Fields {

	/**
	 * HTML Output of a single tour item
	 * 
	 * @param int $id
	 *
	 * @return string HTML
	 */
	public static function html( $id ) {
		
		$title = Tour::title($id);
		
		return "<fieldset>
			<input type='hidden' name='ids[]' value='$id'>
			<h2><label for='datepicker_input_{$id}'>$title</label></h2>
			<h3 id='date_description_{$id}'>Please select a date</h3>
			<p id='availability_{$id}'></p>
			<input type='hidden' id='datepicker_input_{$id}' name='dates[{$id}]'/>
			<div id='datepicker_div_{$id}' data-target-id='datepicker_input_{$id}' class='display-date ll-skin-cangas'></div>
			</fieldset>";
		
	}
}
