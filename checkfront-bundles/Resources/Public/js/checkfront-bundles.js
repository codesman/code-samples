jQuery(document).ready(function ($) {

	// This code is to enable the functionality to book a Tour Bundle that the CheckFront
	// hosted booking app is not capable of.

	// A bundle will contain either 2 or 3 Tours, never just 1, never more than 3.

	// Bundle Form HTML is generated in the WordPress shortcode.

	// Bundle pricing from API calls are discounted.  We need to know the
	// regular price as well so we can calculate the savings and display to customer.
	// The CheckFront API makes this a pretty complicated task and pricing
	// rarely changes, so we're just going to hardcode the regular pricing.
	// It is hardcoded on the server and passed through wp_localize_script by the shortcode
	var regularPricing = pricing.regular;

	// Fetch initial availability and store in localStorage
	var availabilityLoaded = false;
	
	/**
	 * Datepicker init/config
	 *
	 * @return {undefined}
	 */
	$('.display-date').each(function () {

		$(this).datepicker({
			inline: true,
			altField: "#" + $(this).data('target-id'),
			altFormat: 'm/d/yy',
			minDate: 2,
			maxDate: 365,
			beforeShowDay: determineAvailability,
			onSelect: updateBookingDetails,
			onChangeMonthYear: changeMonthYear
		});
	});

	getInitialAvailability();

	// Show pricing on form
	updatePricing();

	/**
	 * First part of booking form
	 * Validates fields & calls AJAX to create booking session
	 *
	 * AJAX success returns HTML form to gather customer info
	 *
	 * @return {boolean}
	 */
	$(document).on("submit", "form#booking_details", function () {

		if (validBookingDetails()) {

			var form = $('#booking_details').serialize();

			$('#step_one').replaceWith("<p><em>Processing, Please wait...</em></p>");

			var data = {
				'action': 'create_booking_session',
				'params': form
			};

			$.get(wordpress.ajaxurl, data, function (response) {
				$('#bundle_booking_form').html(response.data);
				requiredStars();
			});
		}
		return false;
	});

	/**
	 * Second part of booking form
	 * Relies on HTML5 browser validation & calls AJAX to create a booking
	 *
	 * AJAX success returns Payment URL for the booking & redirects
	 *
	 * @return {boolean}
	 */
	$(document).on("submit", "form#booking_form", function () {

		var form = $('#booking_form').serialize();
		// console.log(form);
		$('#step_two').replaceWith("<p><em>Processing, Please wait...</em></p>");

		var data = {
			'action': 'create_booking',
			'form': form
		};

		jQuery.post(wordpress.ajaxurl, data, function (response) {

			if (response.success) {
				top.location.replace(response.data);
			} else {
				$('#bundle_booking_form').html("<p>We're very sorry, an error has occurred. Please call us at (888)594-2329 to complete this booking.</p>");
			}
		});

		return false;
	});

	/**
	 * Update pricing when quantity changes
	 *
	 * @return {undefined}
	 */
	$('input[type="number"]').change(function () {
		
		updatePricing();
		$(".display-date").datepicker('refresh');

	});

	/**
	 * Fetches initial availability for all datepickers
	 *
	 * Updates the datepicker after AJAX success
	 *
	 * @return {undefined}
	 */
	function getInitialAvailability() {
		
		var $picker = $(".display-date:first");
		var $spans = $("td:not(.ui-datepicker-other-month) span");
		
		var dates = $picker.find($spans);
		var month = $picker.find($(".ui-datepicker-month"));
		var year = $picker.find($(".ui-datepicker-year"));
		
		// This is totally hacky, but I just need this done!
		var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

		var monthIndex = months.indexOf(month[0].innerHTML) + 1;
		if (monthIndex < 10) monthIndex = '0' + monthIndex;
		
		var firstDate = dates[0].innerHTML;
		if (firstDate < 10) firstDate = '0' + firstDate;

		var lastDate = dates[[dates.length - 1]].innerHTML;
		if (lastDate < 10) lastDate = '0' + lastDate;
		
		var startDate = "" + year[0].innerHTML
			+ monthIndex
			+ firstDate;
		
		var endDate = "" + year[0].innerHTML
			+ monthIndex
			+ lastDate;

		getTourAvailablility(startDate, endDate);
	}

	/**
	 * Validates booking details form step
	 *
	 * @return {boolean}
	 */
	function validBookingDetails() {

		var dates = $('#booking_details h3');
		var innerHTML = [];
		var selected = true; // All dates have been selected

		dates.each(function (i, el) {

			var html = $(this)[0].innerHTML;

			if (html == 'Please select a date') {
				selected = false
			}

			if (!selected) {
				alert("Please make sure you have selected a date for all Tours");
				return false;

			}

			innerHTML.push($(this)[0].innerHTML);
		});

		if (selected) {

			// Make sure that a different date is selected for each tour
			if ((innerHTML[0] == innerHTML[1] || innerHTML[0] == innerHTML[2]) ||
				innerHTML[1] == innerHTML[2]) {
				alert("Please choose a different date for each Tour.");
				return false;
			}
		} else {
			return false;
		}

		return true;
	}

	/**
	 * Updates Pricing to show Discounted Total, Regular Total and Savings
	 *
	 * @param bundle {string}
	 *
	 * @return {undefined}
	 */
	function updatePricing() {

		var prices = calculatePricing();

		$('#bundle_discount').html("$" + prices.discount);
		$('#bundle_regular').html("<em><strike>$" + prices.regular + "</strike></em>");
		$('#bundle_savings').html("$" + prices.savings + "!");
	}

	/**
	 * Calculates the Regular Total, Discounted Total and Savings
	 *
	 * @return {{regular: string, discount: string, savings: string}}
	 */
	function calculatePricing() {

		var bundle_id = $("input[name=bundle_id]").val();
		var adult = Number($('#num_adults').val());
		var child = Number($('#num_children').val());
		var adultSubtotal = regularPricing[bundle_id].adult * adult;
		var childSubtotal = regularPricing[bundle_id].child * child;
		
		switch (bundle_id){
			case '2856':
				discount = .85;
				break;
			default:
				discount = .8;
		}
		
		var _regular = ( adultSubtotal + childSubtotal ).toFixed(2);
		var _discount = (( adultSubtotal + childSubtotal ) * discount).toFixed(2);
		var _taxSavings = (_regular - _discount) * .047;
		var _savings = Number((( adultSubtotal + childSubtotal ) - (( adultSubtotal + childSubtotal ) * discount)) + _taxSavings).toFixed(2);

		return {
            'regular': _regular,
            'discount': _discount,
            'savings': _savings
        };
	}

	/**
	 * Called by the datepicker's onChangeMonthYear method.
	 * Gets the datepicker's current info and fetches availability based on that.
	 *
	 * @param year {string}
	 * @param month {string}
	 * @param instance {object}
	 *
	 * @return {undefined}
	 */
	function changeMonthYear(year, month, instance) {

		var nextMonth = new Date();
		nextMonth.setFullYear(instance.selectedYear, instance.selectedMonth, 1);

		var endMonth = new Date();
		endMonth.setFullYear(instance.selectedYear, instance.selectedMonth + 1, 0);

		var startDate = $.datepicker.formatDate('yymmdd', nextMonth);
		var endDate = $.datepicker.formatDate('yymmdd', endMonth);

		getTourAvailablility(startDate, endDate, instance);
	}

	/**
	 * Calls AJAX to get availability, stores in localStorage and refreshes datepicker
	 *
	 * @param startDate {string}
	 * @param endDate {string}
	 * @param instance {object}
	 *
	 * @return {undefined}
	 */
	function getTourAvailablility(startDate, endDate, instance) {

		var data = {
			'action': 'get_tour_availability',
			'start_date': startDate,
			'end_date': endDate
		};

		$.post({
			url: wordpress.ajaxurl,
			data: data,
			success: success
		});


		function success(response) {

			if ('undefined' == typeof instance) {

				// No data has been previously loaded so store all of it.
				for (var id in response.data) {

					localStorage.setItem('availableDates_' + id, JSON.stringify(response.data[id]));
				}

			} else {

				// Availability has already been loaded, just update the one that changed.
				var _id = instance.id.replace('datepicker_div_', '');
				localStorage.setItem('availableDates_' + _id, JSON.stringify(response.data[_id]));

			}
			availabilityLoaded = true;
			$(".display-date").datepicker('refresh');
		}

	}

	/**
	 * Called by the datepicker's beforeShowDay method to determine if a date is available or not
	 *
	 * @param date {object}
	 *
	 * @return {[bool, string, string]}
	 */
	function determineAvailability(date) {

		var quantity = requestedQuantity();
		var monthPadded = (date.getMonth() + 1) < 10 ? '0' + (date.getMonth() + 1) : (date.getMonth() + 1);
		var datePadded = date.getDate() < 10 ? '0' + date.getDate() : date.getDate();

		var dateString = monthPadded + '/' + datePadded + '/' + date.getFullYear();

		var id = this.id.replace('datepicker_div_', '');
		var available = JSON.parse(localStorage.getItem('availableDates_' + id));

		if (availabilityLoaded) {

			if (available.hasOwnProperty(dateString)) {

				if (available[dateString] > 0 && available[dateString] >= quantity) {

					return [true, 'ui-datepicker-selectable', available[dateString] + ' seats available on this date'];
				}
			}

			return [false, 'unavailable', 'Sorry, there are not enough seats available for your party on this date'];
		} else {

			return [false, '', ''];
		}
	}

	/**
	 * Calculates how many seats are needed
	 *
	 * @return {number}
	 */
	function requestedQuantity() {

		var adult = Number($('#num_adults').val());
		var child = Number($('#num_children').val());

		return adult + child;
	}

	/**
	 * Called by the datepicker's onSelect method
	 * Shows the Selected Date and Quantity of available seats for that Date
	 *
	 * @param dateText {string}
	 * @param instance {object}
	 *
	 * @return {undefined}
	 */
	function updateBookingDetails(dateText, instance) {

		var dateString = formattedDateText(dateText);

		var id = this.id.replace('datepicker_div_', '');

		var available = JSON.parse(localStorage.getItem('availableDates_' + id));

		$('#date_description_' + id).html(dateString);
		$('#availability_' + id).html(available[dateText] + " seats available");
	}

	/**
	 * Creates a formatted date string from the string passed by
	 * the datepicker's onSelect method
	 *
	 * @param dateText {string}
	 *
	 * @return {string}
	 */
	function formattedDateText(dateText) {

		var dateComponents = dateText.split('/');
		var date = new Date();
		date.setFullYear(dateComponents[2], dateComponents[0] - 1, dateComponents[1]);

		var months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
		var days = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];

		var year = date.getFullYear();
		var month = date.getMonth();
		var weekday = date.getDay();
		var day = date.getDate();

		return days[weekday] + ', ' + months[month] + ' ' + day + ', ' + year;
	}

	/**
	 * Appends a red asterisk to required fields
	 *
	 * @return {undefined}
	 */
	function requiredStars() {
		$('.form-required').append(' <span style="color: red;">*</span>');
	}
});
