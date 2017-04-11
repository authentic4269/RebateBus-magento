/*
 * Rebate Bus Client API demo
 *
 * Note that the AJAX should be run server side in production applications. It only works on demo.rebatebus.com because the 
 * Rebate Bus server sets the Access-Control-Allow-Origin header on requests from that domain. Including it here so that the 
 * entire API process will be visible in this demo.
 *
 * Also note that stealing this API key and UID won't do you much good - they're tied to the products in the inventory managed by user 1
 * Feel free to use this API key and UID with these products to develop and debug your own apps. 
 *
 * Mitch Vogel, 9/30/16
 */

function doRebateApp(products, UID, PUB_API_KEY) {
	MidstreamWidget.configure({
		"uid": UID,
		"apikey": PUB_API_KEY,
		"products": [products],
		"verified": function(data) {
			var postAction = "/magento_one/index.php/checkout/cart/rebatesPost";
			var finished = data.length;
			for (var i = 0; i < data.length; i++)
			{
				jQuery.post(postAction, data[i], function(response) {
					if (--finished <= 0)
						location.reload();
				});
			}
		}
	});	
	
}

