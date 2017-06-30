<?php

namespace Checkfront_Bundles;

final class Quantity_Fields {
	/**
	 * HTML Field elements for Adult & Child Pricing
	 * 
	 * @return string HTML
	 */
	public static function html() {

		return "<label for='num_adults'>Adults:</label>
			<input type='number' value='2' id='num_adults' name='adult_quantity' min='0'/>
			
			<label for='num_children'>Children:</label>
			<input type='number' value='0' id='num_children' name='child_quantity' min='0'/>";
	}
}
