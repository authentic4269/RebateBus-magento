/*
 * Rebate Bus Client API demo
 *
 * Demo script illustrating usage of the Rebate Bus Search Widget.
 *
 * The script calls SearchWidget.configure with the user's zip and property type, as well as the products on the page (productid_list) and public API credentials.
 * 
 * The callback ('callback') provided to the SearchWidget adds rebate markup
 * 
 * Markup is customized based on whether we're on a single product page or catalog page (updateRebatePriceQuotes), or checkout page (updateRebateApplySection)
 *
 * Mitch Vogel, 7/4/2018
 */

var UID;
var PUB_API_KEY;
var server;
var initial_price;
var applyProducts;
var first = 1;

UID = YOUR_UID;
PUB_API_KEY = "YOUR_PUB_API_KEY";
initial_price = 15.99;
server = "https://www.rebatebus.com/"
products = [];
applyProducts = [];

function calculateFinalPrice(base, incentive) {
	var capped = parseFloat(incentive.rebateAmount);
	if (incentive.cap) {
            var maxAmount = (incentive.cap / 100.0) * base;
            if (maxAmount < incentive.rebateAmount)
                capped = maxAmount.toFixed(2);
        } if (incentive.mincustomercontribution && incentive.mincustomercontribution > capped) {
            capped = amount - incentive.mincustomercontribution;
        }	
	return capped.toFixed(2);
}

/*
 * We've found a rebate that applies to productid in the program we're localizing to - update the DOM to reflect the discount
 */
function updateRebatePriceQuotes(productid, incentive) {
	var target = jQuery("#" + productid);
	var imgdiv = document.createElement("div");
	var programimg = document.createElement("img");
	var desc = document.createElement("p");
	var captxt = "";
	var desctxt;
	var finalPrice;
	var price = jQuery("#product-price-" + target.attr("data-product-id"));
	var itemname = "Rebate";
	if (incentive.useincentivename) {
		itemname = "Incentive";
	}
		
	if (incentive.useutilitylogos) {
		programimg.src = server + "assets/utilityimages/" + incentive.program + "/" + incentive.utilities[i] + ".png";
		desctxt = "$" + incentive.rebateAmount + captxt + " " + itemname + " from " + incentive.utilityname + " available for qualified customers";
		programimg.style['max-width'] = (14 - Math.min(6, incentive.utilities.length)) + "em";
		imgdiv.style['max-width'] = (14 - Math.min(6, incentive.utilities.length)) + "em";
	}
	else {
		programimg.src = server + "assets/programimages/" + incentive.program + ".png";
		desctxt = "$" + incentive.rebateAmount + captxt + " " + itemname + " from " + incentive.program + " available for qualified customers";
		programimg.style['max-width'] = "14em";
		imgdiv.style['max-width'] = '14em';
	}
	programimg.style['max-height'] = '8em';
	programimg.style['display'] = 'inline';
	desc.style['padding-bottom'] = '2em';
	desc.append(document.createTextNode(desctxt));
	imgdiv.append(programimg);
	target.append(imgdiv);
	target.append(desc);
	setTimeout(function() {
		if (price.length) {
			finalPrice = calculateFinalPrice(price.attr("data-price-amount"), incentive);
			price.html("<span class='price'><del>$" + parseFloat(price.attr("data-price-amount")).toFixed(2) + "</del>&nbsp;</span><span class='price'>$" + finalPrice + "</span>");
		}
	}, 1000);
}

function clearRebatePriceQuotes() {
	if (!first) 
		location.reload();
	else
		first = 0;
	for (var i = 0; i < products.length; i++) {
		jQuery("#" + products[i] + "-rebate-target").empty();
	}

}
/*
 * We've found a rebate that applies to productid in the program we're localizing to - update the DOM to reflect the discount
 * Configurable product handler: this page contains a configrable product, so list out the eligible configurations. 
 * if the eligible-rebate-options list has any members, we need to add the current incentive. else, we need to actually
 * create the rebates div
 */
function updateConfigQuotes(productid, incentive) {
	var amount = parseFloat(incentive.rebateAmount); // widget delivers rebateAmount in a string
	var line = document.getElementById("configproduct" + productid);	
	var wrapper = document.getElementById("product-options-wrapper");
	var i;
	var imgdiv = document.createElement("div");
	var desc = document.createElement("span");
	imgdiv.style['display'] = 'inline';
	if (!jQuery("#eligible-rebate-options").length) {
		var rebatewrapper = document.createElement("div");
		rebatewrapper.id = "config-rebates-wrapper";
		if (incentive.useutilitylogos) {
			for (i = 0; i < incentive.utilities.length; i++) {
				var programimg = document.createElement("img");
				programimg.src = server + "assets/utilityimages/" + incentive.program + "/" + incentive.utilities[i] + ".png";
				programimg.style['max-width'] = (9 - Math.min(6, incentive.utilities.length)) + "em";
				programimg.style['max-height'] = '8em';
				programimg.style['display'] = 'inline';
				imgdiv.append(programimg);
			}
		}
		else {
			var programimg = document.createElement("img");
			programimg.style['max-width'] = "11em";
			programimg.style['margin'] = "0 auto";
			imgdiv.append(programimg);
		}
		if (incentive.useincentivename)
			desc.appendChild(document.createTextNode("Incentives Available From " + incentive.utilities[0]));
		else
			desc.appendChild(document.createTextNode("Rebates Available From " + incentive.program));
		rebatewrapper.append(desc);
		rebatewrapper.append(imgdiv);
		var eligibleTxt = document.createElement("p");
		eligibleTxt.appendChild(document.createTextNode("Utility Qualified Eligible Option Configurations: "));
		var eligibleOptions = document.createElement("div");	
		eligibleOptions.id = "eligible-rebate-options";
		rebatewrapper.append(eligibleTxt);
		rebatewrapper.append(eligibleOptions);
		wrapper.append(rebatewrapper);
		
	}
	var opts = document.getElementById("eligible-rebate-options");
	var curOpt = document.createElement("p");
	curOpt.appendChild(document.createTextNode(line.innerHTML));
	opts.appendChild(curOpt);
}

function clearConfigQuotes() {
	var element = document.getElementById("config-rebates-wrapper");	
	if (element !== null) {
		element.parentNode.removeChild(element);
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

require.config({
            deps: [
                'jquery'
            ],
            callback: function (jQuery) {
		var updateFn = updateRebatePriceQuotes;
		var clearFn = clearRebatePriceQuotes;
		var showdownstream = 1;
		if (jQuery(".cart-rebate-target").length) {
			if (!document.getElementById("rebate-remove-submit")) {
			// if there is no remove button, we can set up the application
				updateFn = updateRebateApplySection;
				clearFn = clearRebateApplySection;
				jQuery(".cart-rebate-target").each(function(i) {
					// bundled products deliver skus in a hyphen-delimited format
					products.push(jQuery(".cart-rebate-target").get(i).id);
				});
			} else {
			// if they've already gotten an incentive, load the 'remove' button.
				jQuery("#discount-rebates-form").show();
				jQuery("#rebate-remove-submit").click(function() {
					jQuery("body").css("cursor", "progress");
					jQuery.post("/checkout/cart/rebatePost", {remove: 1}, function(response) {
						location.reload();
					});
				});
			}
		} 
		else if (jQuery('.config-product-associated').length) {
			updateFn = updateConfigQuotes;
			clearFn = clearConfigQuotes;
			jQuery(".config-product-associated").each(function(i) {
				products.push(this.getAttribute('id').replace("configproduct", ""));
			});
		}
		else if (jQuery(".rebate-target").length) {
			jQuery(".rebate-target").each(function(i) {
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
			"server": server,
			"productid_list": products,
			"showdownstream": showdownstream,
			"callback": updateFn,
			"viewingtype": "residential",
			"clear": clearFn

		});
	}
});
