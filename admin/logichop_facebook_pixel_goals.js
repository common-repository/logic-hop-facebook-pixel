jQuery(function($) {
	
	var fbp_events =  {
			"ViewContent": {
				"value": "ViewContent",
				"params": [
					"value","currency","content_name","content_type","content_ids"
				],
				"required": [],
				"dynamic_ad_required": ["content_type","content_ids"],
				"description": "When a key page is viewed such as a product page, e.g. landing on a product detail page"
			},
			"Search": {
				"value": "Search",
				"params": [
					"value","currency","content_category","content_ids","search_string"
				],
				"required": [],
				"dynamic_ad_required": [],
				"description": "When a search is made, e.g. when a product search query is made"
			},
			"AddToCart": {
				"value": "AddToCart",
				"params": [
					"value","currency","content_name","content_type","content_ids"
				],
				"required": [],
				"dynamic_ad_required": ["content_type","content_ids"],
				"description": "When a product is added to the shopping cart, e.g. click on add to cart button"
			},
			"AddToWishlist": {
				"value": "AddToWishlist",
				"params": [
					"value","currency","content_name","content_category","content_ids"
				],
				"required": [],
				"dynamic_ad_required": [],
				"description": "When a product is added to a wishlist, e.g. click on add to wishlist button"
			},
			"InitiateCheckout": {
				"value": "InitiateCheckout",
				"params": [
					"value","currency","content_name","content_category","content_ids","num_items"
				],
				"required": [],
				"dynamic_ad_required": [],
				"description": "When a person enters the checkout flow prior to completing the checkout flow, e.g. click on checkout button"
			},
			"AddPaymentInfo": {
				"value": "AddPaymentInfo",
				"params": [
					"value","currency","content_category","content_ids"
				],
				"required": [],
				"dynamic_ad_required": [],
				"description": "When a payment information is added in the checkout flow, e.g. click / LP on save billing info button"
			},
			"Purchase": {
				"value": "Purchase",
				"params": [
					"value","currency","content_name","content_type","content_ids","num_items"
				],
				"required": ["value","currency"],
				"dynamic_ad_required": ["content_type","content_ids"],
				"description": "When a purchase is made or checkout flow is completed, e.g. landing on thank you/confirmation page"
			},
			"Lead": {
				"value": "Lead",
				"params": [
					"value","currency","content_name","content_category"
				],
				"required": [],
				"dynamic_ad_required": [],
				"description": "When a sign up is completed, e.g. click on pricing, signup for trial"
			},
			"CompleteRegistration": {
				"value": "CompleteRegistration",
				"params": [
					"value","currency","content_name","status"
				],
				"required": [],
				"dynamic_ad_required": [],
				"description": "When a registration form is completed, e.g. complete subscription/signup for a service"
			}
		};

	var fbp_params =  {
			"value": {
				"label": "Value",
				"id": "fbp_value",
				"type": "number",
				"atts": "min='0.00' step='any' placeholder='0.00'",
				"default": "",
				"description": "Value of a user performing this event to the business"
			},
			"currency": {
				"label": "Currency",
				"id": "fbp_currency",
				"type": "text",
				"atts": "",
				"default": "USD",
				"description": "Currency for the value specified"
			},
			"content_name": {
				"label": "Content Name",
				"id": "fbp_content_name",
				"type": "text",
				"atts": "",
				"default": "",
				"description": "Name of the page/product"
			},
			"content_category": {
				"label": "Content Category",
				"id": "fbp_content_category",
				"type": "text",
				"atts": "",
				"default": "",
				"description": "Category of the page/product"
			},
			"content_ids": {
				"label": "Content IDs",
				"id": "fbp_content_ids",
				"type": "text",
				"atts": "placeholder='Comma-separated IDs'",
				"default": "",
				"description": "Product ids associated with the event. e.g. SKUs of products for AddToCart event: ABC123, XYZ789"
			},
			"content_type": {
				"label": "Content Type",
				"id": "fbp_content_type",
				"type": "text",
				"atts": "",
				"default": "product",
				"description": "Either 'product' or 'product_group' based on the content_ids being passed.<br>If the ids being passed in content_ids parameter are ids of products then the value should be 'product'.<br>If product group ids are being passed, then the value should be 'product_group'."
			},
			"num_items": {
				"label": "Number of Items",
				"id": "fbp_num_items",
				"type": "number",
				"atts": "min='1'",
				"default": "",
				"description": "The number of items that checkout was initiated for"
			},
			"search": {
				"label": "Search",
				"id": "fbp_search",
				"type": "text",
				"atts": "",
				"default": "",
				"description": "The string entered by the user for the search"
			},
			"status": {
				"label": "Status",
				"id": "fbp_status",
				"type": "text",
				"atts": "",
				"default": "",
				"description": "The status of the registration."
			}
		};
		
	$('.logichop_fbp_clear').click(function (e) {
		$('#logichop_goal_fbp_event, #logichop_goal_fbp_data').val('');
		$('#logichop_goal_fbp_track').val($('#logichop_goal_fbp_track option:first').val());
		fbp_init();
		e.preventDefault();
	});
	
	$('body').on('change', '#logichop_goal_fbp_track', function () {
  		$('#logichop_goal_fbp_event, #logichop_goal_fbp_data').val('');
  		fbp_init();
	});
	
	$('body').on('change', '#logichop_fbp_event', function () {
  		$('#logichop_goal_fbp_event').val($(this).val());
	});
	
	$('body').on('change', '#logichop_fbp_data', function () {
  		$('#logichop_goal_fbp_data').val($(this).val());
	});
	
	$('body').on('change', '#logichop_fbp_event_select', function () {
		var event = $(this).val();
  		$('#logichop_fbp_event_desc').html(fbp_events[event].description);
  		$('#logichop_goal_fbp_event').val(event);
  		fbp_event_params(event);
	});
	
	$('body').on('change', '.logichop_fbp_param', function () {
		var data = {};
		
		$('#logichop_fbp_fields input').each(function () {
			var name = $(this).attr('data-var');
			var value = $(this).val().replace(/([\\"'])/g, '');
			if (name != 'content_ids') {
				data[name] = value;
			} else {
				data[name] = value.split(',');
			}
		});
		
		$('#logichop_goal_fbp_data').val(JSON.stringify(data));
	});
	
	function fbp_clear () {
		$('#logichop_goal_fbp_event, #logichop_goal_fbp_data').val('');
		$('#logichop_goal_fbp_track').val($('#logichop_goal_fbp_track option:first').val());
	}
	
	function fbp_init () {
		var track 	= $('#logichop_goal_fbp_track').val();
		var event 	= $('#logichop_goal_fbp_event').val();
		var data 	= $('#logichop_goal_fbp_data').val();
		
		if (track == 'trackCustom') {
			$('#logichop_fbp_events').html('<p><label for="logichop_fbp_event">Custom Event Name</label><br><input id="logichop_fbp_event" name="logichop_fbp_event"></p>');
			$('#logichop_fbp_fields').html('<p><label for="logichop_fbp_event">Custom Event Data</label><br><textarea id="logichop_fbp_data" name="logichop_fbp_data"></textarea><br><small>Enter valid JSON object. All values will be hashed.</small></p>');
			if (event) $('#logichop_fbp_event').val(event);
			if (data) $('#logichop_fbp_data').val(data);
		} else if (track == 'track') {
			$('#logichop_fbp_events').html('<p><label for="logichop_fbp_event_select">Event Name</label><br><select id="logichop_fbp_event_select" name="logichop_fbp_event_select"></select><br><small id="logichop_fbp_event_desc"></small></p>');
			
			$.each(fbp_events, function (key, option) {
				$('#logichop_fbp_event_select').append('<option value="' + option.value + '">' + option.value + '</option>');
			});
			if (event) $('#logichop_fbp_event_select').val(event);
			$('#logichop_goal_fbp_event').val($('#logichop_fbp_event_select').val());
			fbp_event_params($('#logichop_fbp_event_select').val());
			$('#logichop_fbp_event_desc').html(fbp_events[$('#logichop_fbp_event_select').val()].description);
		} else {
			$('#logichop_fbp_events').html('');
			$('#logichop_fbp_fields').html('');
		}
	}
	
	function fbp_event_params (event) {
		$('#logichop_fbp_fields').html('');
		$.each(fbp_events[event].params, function (i) {
			var name = fbp_events[event].params[i];
			var input = fbp_params[name];
			if (input) {
				var description = input.description;
				if (fbp_events[event].required.indexOf(name) >= 0) description = '<strong>Required</strong><br>' + description;
				if (fbp_events[event].dynamic_ad_required.indexOf(name) >= 0) description = '<strong>Required for Dynamic Product Ads</strong><br>' + description;
				$('#logichop_fbp_fields').append('<p><label for="' + input.id + '">' + input.label + '</label><br><input id="fbp_' + name + '" data-var="' + name + '" type="' + input.type + '" ' + input.atts + ' value="' + input.default + '" class="logichop_fbp_param"><br><small>' + description + '</small></p>');
			}
		});
		
		var data = (data != '') ? JSON.parse($('#logichop_goal_fbp_data').val()) : false;
		if (data) {
			$.each(data, function (name, value) {
				if (value == 'content_ids') value = value.join(',');
				$('#fbp_' + name).val(value);
			});
		}
	}
	
	fbp_init();
});
