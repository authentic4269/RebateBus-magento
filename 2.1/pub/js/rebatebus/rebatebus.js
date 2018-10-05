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
	var price = 0;
	if (incentive.cap) {
		var maxAmount = (incentive.cap / 100.0) * base;
		if (maxAmount < incentive.rebateAmount)
			capped = maxAmount.toFixed(2);
    }
	if (incentive.mincustomercontribution && incentive.mincustomercontribution > capped) {
        price = base - incentive.mincustomercontribution;
    } else {
		price = base - capped;
	}
	return price.toFixed(2);
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
	var priceName = "#product-price-" + target.attr("data-product-id");
	var itemname = "Rebate";
	var attempts, maxAttempts = 40;

	var rebateAmount = "";
//	var rebateText = "";
	var rebateWrapper = document.createElement("p");
//	var rebateTextWrapper = document.createElement("span");
	
	if (incentive.useincentivename) {
		itemname = "Incentive";
	}

	if (incentive.useutilitylogos) {
		programimg.src = server + "assets/utilityimages/" + incentive.program + "/" + incentive.utilities[i] + ".png";
		desctxt = "$" + incentive.rebateAmount + captxt + " " + itemname + " from " + incentive.utilityname + ' available for qualified customers';
		programimg.style['max-width'] = (10 - Math.min(6, incentive.utilities.length)) + "em";
		imgdiv.style['max-width'] = (11 - Math.min(6, incentive.utilities.length)) + "em";
	}
	else {
		programimg.src = server + "assets/programimages/" + incentive.program + ".png";
		desctxt = "$" + incentive.rebateAmount + captxt + " " + itemname + " from " + incentive.program + ' available for qualified customers';
		programimg.style['max-width'] = "10em";
		imgdiv.style['max-width'] = '11em';
	}
	programimg.style['max-height'] = '8em';
	programimg.style['display'] = 'inline';
	desc.style['padding-left'] = '1em';
	desc.style['padding-right'] = '1em';	
	desc.style['display'] = 'table-cell';
	desc.style['vertical-align'] = 'top';
	desc.style['background'] = '#efe';
	jQuery(desc).append(document.createTextNode(desctxt));
	imgdiv.style['display'] = 'table-cell';
	imgdiv.style['vertical-align'] = 'middle';
	imgdiv.style['border'] = '1px solid #efe';

	rebateAmount = 'Price from $' + calculateFinalPrice(price.attr("data-price-amount"), incentive) + ' ea. after '+itemname;
	//rebateText = ' ea. after '+itemname;
	rebateWrapper.style['text-align'] = 'center';
	rebateWrapper.style['font-size'] = '0.8em';
	rebateWrapper.style['font-weight'] = 'bold';
	//jQuery(rebateTextWrapper).append(rebateText);
	jQuery(rebateWrapper).append(rebateAmount);
	//jQuery(rebateWrapper).append(rebateTextWrapper);
	jQuery(desc).append(rebateWrapper);
	jQuery(imgdiv).append(programimg);
	jQuery(target).append(imgdiv);
	jQuery(target).append(desc);
	jQuery(target).css('border', '2px solid green');
//	for (attempts = 0; attempts < maxAttempts; attempts++) {
//		setTimeout(function() {
//			fixSlashedPrice(price, priceName, incentive, itemname)
//		}, 1000 * (attempts + 1));
//	}
/*
	setTimeout(function() {
		if (price.length) {
			finalPrice = calculateFinalPrice(price.attr("data-price-amount"), incentive);
			price.html("<span class='price'><del>$" + parseFloat(price.attr("data-price-amount")).toFixed(2) + "</del>&nbsp;</span><span class='price'>$" + finalPrice + " after " + itemname + "</span>");
		}
	}, 1000);
*/
}

function fixSlashedPrice(price, priceName, incentive, itemname) {
	if (!jQuery(priceName+" #rebate-price-after").length) {
		finalPrice = calculateFinalPrice(price.attr("data-price-amount"), incentive);
		price.html('<span id="rebate-price-after" class="price">$' + parseFloat(price.attr("data-price-amount")).toFixed(2) + '</span>&nbsp;&nbsp;<span style="color: darkgreen;">$' + finalPrice + '<span style="color: darkgreen; font-weight: normal; font-size: 0.7em;"> after '+itemname+'</span></span>');
	}
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
				jQuery(imgdiv).append(programming);
			}
		}
		else {
			var programimg = document.createElement("img");
			programimg.style['max-width'] = "11em";
			programimg.style['margin'] = "0 auto";
			jQuery(imgdiv).append(programming);
		}
		if (incentive.useincentivename)
			desc.appendChild(document.createTextNode("Incentives Available From " + incentive.utilities[0]));
		else
			desc.appendChild(document.createTextNode("Rebates Available From " + incentive.program));
		jQuery(rebatewrapper).append(desc);
		jQuery(rebatewrapper).append(imgdiv);
		var eligibleTxt = document.createElement("p");
		eligibleTxt.appendChild(document.createTextNode("Utility Qualified Eligible Option Configurations: "));
		var eligibleOptions = document.createElement("div");	
		eligibleOptions.id = "eligible-rebate-options";
		jQuery(rebatewrapper).append(eligibleTxt);
		jQuery(rebatewrapper).append(eligibleOptions);
		jQuery(wrapper).append(rebatewrapper);
		
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
	var wrapper = jQuery("#discount-rebates-wrapper");
	var programimg = jQuery("#discount-rebates-programimg");
	if (incentive.useutilitylogos) {
		programimg.attr('src', server + "assets/utilityimages/" + incentive.program + "/" + incentive.utilityname + ".png");
	}
	else {
		programimg.attr('src', server + "assets/programimages/" + incentive.program + ".png");
	}
	
	jQuery(wrapper).append();
	jQuery("#discount-rebates-button").click(function() {
		doRebateApp(applyProducts, UID, PUB_API_KEY);
	});
};

var getUrlParameter = function(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};

window.onload = function() {
	if (window.location.href.indexOf("led-rebates") > -1) {
		var prog = getUrlParameter('program');
		var zip = getCookie("busfrm-rebatezip");
		var viewingtype = getCookie("busfrm-propertytype");
		if (prog && prog.length) {
			document.getElementById("busiframe").src = "https://www.rebatebus.com/eled?apikey=" + PUB_API_KEY + "&uid=202" + "&program=" + prog;
		} else {
			document.getElementById("busiframe").src = "https://www.rebatebus.com/eled?apikey=" + PUB_API_KEY + "&uid=202";
		}
	}
}

window.addEventListener("message", function(event) {
	if (event.data.type == 'buynow') {
		window.location.href = event.data.target
	} else if (event.data.type == 'scroll') {
		jQuery("html").animate({scrollTop: event.data.target + jQuery("#eledframe iframe").offset().top - 100});
	}
}, false);

require.config({
            deps: [
                'jquery'
            ],
            callback: function (jQuery) {
		var updateFn = updateRebatePriceQuotes;
		var clearFn = clearRebatePriceQuotes;
		var showdownstream = 0;
		var addProgramLogo = function(programs) {
			if (window.location.href.indexOf("led-rebates") > -1)
				return;
			var bar = document.createElement('div');
			var span = document.createElement('span');
			var curBar = document.getElementById("programbar");
			var programimg = document.createElement('img');
			var programtxt = document.createElement('h2');
			var busbar = document.getElementById("busbar");
			if (curBar != null)
					document.body.removeChild(curBar);
			programimg.src = server + "assets/programimages/" + programs[0].name + ".png";
			bar.setAttribute('id', 'programbar'); // assign an id
			bar.style.padding = "5px 5px 5px 5px";
			bar.style['background-color'] = "#e4e4e4";
			bar.style['border-bottom-left-radius'] = "5px";
			bar.style['border-bottom-left-radius'] = "5px";
			bar.style['cursor'] = "pointer";
			bar.style['z-index'] = "99999999";
			if (screen.width < 800) {
				bar.style['max-width'] = "120px";
				programimg.style['max-width'] = '100%';
				span.style['font-size'] = "small";
				programtxt.style['font-size'] = "small";
				programtxt.style['margin-bottom'] = 0;
			} else {
				bar.style['max-width'] = "180px";
				programimg.style['max-width'] = '100%';
				span.style['font-size'] = "large";
				programtxt.style['font-size'] = "large";
			}
			programtxt.style['text-align'] = "center";
			programtxt.style['margin-top'] = "5px";
			programtxt.style['font-weight'] = "bold";
			if (programs[0].useincentivename) {
				programtxt.appendChild(document.createTextNode("Utility Incentives Available Here!"));
			} else {
				programtxt.appendChild(document.createTextNode("Utility Rebates Available Here!"));
			}
			span.appendChild(programimg);
			bar.appendChild(span);
			bar.appendChild(programtxt);
			bar.addEventListener("click",
				function() {
					window.location.assign("https://loren.mannsmt.com/led-rebates?program=" + programs[0].name);
				}
			);
			busbar.insertBefore(bar, busbar.childNodes[0]);
				//document.body.appendChild(bar); // to place at end of document
		};
		
		
		var createZipBar = function(zip, propertytype, options) {
			if (window.location.href.indexOf("led-rebates") > -1)
				return;
			var bar = document.createElement('div');
			var span = document.createElement('span');
			var mapicon = document.createElement('i');
			var pencilicon = document.createElement('i');
			var curBar = document.getElementById("busbar");
			if (curBar != null)
					document.body.removeChild(curBar);
			bar.setAttribute('id', 'busbar'); // assign an id
			bar.style.position = "fixed";
			bar.style.left = "0";
			bar.style.bottom = "12px";
			bar.style.padding = "5px 5px 5px 5px";
			bar.style['background-color'] = "#e4e4e4";
			bar.style['border-bottom-left-radius'] = "5px";
			bar.style['border-bottom-left-radius'] = "5px";
			bar.style['cursor'] = "pointer";
			bar.style['z-index'] = "99999999";
			if (screen.width < 800)
				span.style['font-size'] = "small";
			else 
				span.style['font-size'] = "large";
			mapicon.className = "fa fa-map-marker";
			pencilicon.className = "fa fa-pencil";
			span.appendChild(mapicon);
			span.appendChild(document.createTextNode(" Install Zip: " + zip + " "));
			span.appendChild(pencilicon);
			bar.appendChild(span);
		
			pencilicon.addEventListener("click",
				function() {
					gotzipFlag = 0;
					createWidget(options, propertytype, zip);
				}
			);
			document.body.appendChild(bar); // to place at end of document
		};
	



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
			addProgramLogo = function() {};
			createZipBar = function() {};
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
		}
		
		SearchWidget.configure({
			"uid": UID,
			"apikey": PUB_API_KEY,
			"server": server,
			"productid_list": products,
			"showdownstream": showdownstream,
			"callback": updateFn,
			"viewingtype": "commercial",
			"clear": clearFn,
			"createZipBar": createZipBar,
			"addProgramLogo": addProgramLogo
		});
	}
});
