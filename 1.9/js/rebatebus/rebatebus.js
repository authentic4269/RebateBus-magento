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

var UID = YOUR_UID;
var PUB_API_KEY = "YOUR_PUB_API_KEY";
var initial_price = 15.99;
var server = "https://www.rebatebus.com/"
var products = [];
var applyProducts = [];

/*
 * We've found a rebate that applies to productid in the program we're localizing to - update the DOM to reflect the discount
 */
function updateRebatePriceQuotes(productid, incentive) {
	var amount = parseFloat(incentive.rebateAmount); // widget delivers rebateAmount in a string
	var pric = jQuery("#" + productid + " .pric1");
	var i;
	var imgdiv = document.createElement("div");
	imgdiv.style['display'] = 'inline';
	if (incentive.useutilitylogos) {
		pric.text("");
		pric.append("<del>$" + incentive.msrp.toFixed(2) + "</del>");
		jQuery("#" + productid + " .pric2").text("$" + (incentive.msrp - amount).toFixed(2));
		for (i = 0; i < incentive.utilities.length; i++) {
			var programimg = document.createElement("img");
			programimg.src = server + "assets/utilityimages/" + incentive.program + "/" + incentive.utilities[i] + ".png";
			jQuery("#" + productid + " .disc").text("$" + incentive.rebateAmount + " rebate from " + incentive.utilityname);
			programimg.style['max-width'] = (9 - Math.min(6, incentive.utilities.length)) + "em";
			programimg.style['max-height'] = '4em';
			programimg.style['display'] = 'inline';
	//		programimg.style['margin'] = "0 auto";
			programimg.className = productid + "img";
	//		jQuery("#" + productid).append(programimg);
			imgdiv.append(programimg);
		}
		jQuery("#" + productid).append(imgdiv);
	}
	else {
		var programimg = document.createElement("img");
		jQuery("#" + productid + " .disc").text("$" + incentive.rebateAmount + " rebate from " + incentive.program);
		programimg.src = server + "assets/programimages/" + incentive.program + ".png";
		programimg.style['max-width'] = "9em";
		programimg.style['margin'] = "0 auto";
	//	programimg.setAttribute("id", productid + "img");
		programimg.className = productid + "img";
		pric.text("");
		pric.append("<del>$" + incentive.msrp.toFixed(2) + "</del>");
		jQuery("#" + productid + " .pric2").text("$" + (incentive.msrp - amount).toFixed(2));
		imgdiv.append(programimg);
		jQuery("#" + productid).append(imgdiv);
	}
}

function clearRebatePriceQuotes() {
	var i;
	for (i = 0; i < products.length; i++) {
		var pric2 = jQuery("#" + products[i] + " .pric2");
		var pric = jQuery("#" + products[i] + " .pric1");
		var disc = jQuery("#" + products[i] + " .disc");
		if (jQuery("." + products[i] + "img"))
			jQuery("." + products[i] + "img").remove();
		if (pric.text().length)
			pric2.text(pric.text());
		disc.text("");
		pric.text("");
	}
}

var clearRebateApplySection = function() {
	applyProducts = [];
	jQuery("#discount-rebates-form").hide();
}

var updateRebateApplySection = function(productid, incentive) {
	applyProducts.push(productid);
	jQuery("#discount-rebates-form").show();
	jQuery('#discount-rebates-button').off("click");
	var wrapper = jQuery("#discount-rebates-wrapper")
	var programimg = jQuery("#discount-rebates-programimg");
	if (incentive.useutilitylogos) {
		programimg.attr('src', server + "assets/utilityimages/" + incentive.program + "/" + incentive.utilityname + ".png");
	}
	else {
		programimg.attr('src', server + "assets/programimages/" + incentive.program + ".png");
	}
	
	wrapper.append();
	jQuery("#discount-rebates-button").click(function() {
		doRebateApp(applyProducts, UID, PUB_API_KEY);
	});
}

window.onload = function() {
// if .item, we're on a catalog or search page. if .price-box, we're on a single product page
	var updateFn = updateRebatePriceQuotes;
	var clearFn = clearRebatePriceQuotes;
	var showdownstream = 1;
	if (jQuery(".product-cart-info").length) {
		// if they've already gotten an incentive there's no need
		if (!document.getElementById("rebate-remove-submit")) {
			updateFn = updateRebateApplySection;
			clearFn = clearRebateApplySection;
			jQuery(".product-cart-sku").each(function(i) {
				products.push(this.textContent.replace(/\D/g, ''));
			});
		} else {
			jQuery("#rebate-remove-submit").click(function() {
				jQuery.post("/magento_one/index.php/checkout/cart/rebatesPost", {remove: 1}, function(response) {
					location.reload();
				});
			});
		}
	} 
	else if (jQuery(".item").length) {
		jQuery(".item .ref").each(function(i) {
			products.push(this.getAttribute('id'));
		});
	} 
	else if (jQuery(".price-box").length) {

		jQuery(".price-box").each(function(i) {
			products.push(this.getAttribute('id'));
		});

	} else {
		return;
	}
	SearchWidget.configure({
		"uid": UID,
		"apikey": PUB_API_KEY,
		"productid_list": products,
		"showdownstream": showdownstream,
		"callback": updateFn,
		"clear": clearFn

	});

}
