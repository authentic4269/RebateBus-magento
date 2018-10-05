/*
 * Rebate Bus Client API demo
 *
 * This script is an example usage of the MidstreamWidget
 *
 * The doRebateApp function is installed by rebatebus.js' callback from the SearchWidget. It loads the application, and 
 * performs the server-side rebatesPost action to add the rebate to the users cart if successful. 
 *
 * To use this code in your own applications, just write a rebatebus.js that performs the markup for your site,
 * then change the 'verified' callback here such that it hits the add-rebate-to-cart handler on your server.
 * 
 * I suggest looking at the data being passed to rebatesPost here, and writing your add-rebate handler to reflect this data
 * 
 * The fields are: product id, verification code, maximum quantity, amount per product, rebate program name, 
 * Rebate Bus cookie, and %-of-cost cap. Rebate amount may not exceed this % of the cost of this product.
 *
 * Note that stealing this API key and UID won't do you much good - they're tied to the products in the inventory managed by user 1
 * Feel free to use this API key and UID with these products to develop and debug your own apps. 
 *
 * Mitch Vogel, 8/10/2017
 */

function doRebateApp(products, UID, PUB_API_KEY) {
	MidstreamWidget.configure({
		"uid": UID,
		"apikey": PUB_API_KEY,
		"products": [products],
		"verified": function(data) {
			var postAction = "/checkout/cart/rebatePost";
			var finished = data.length;
			jQuery("body").css("cursor", "progress");
			for (var i = 0; i < data.length; i++)
			{
				jQuery.post(postAction, data[i], function(response) {
					if (--finished == 0) 
						location.reload();
				}).fail(function(r) {
					if (--finished == 0) 
						location.reload();
				});
			}
		}
	});	
	
}

