# RebateBus-magento
Magento extension for working with the Rebate Bus API

-----------------------------------------------------

Magento 1 Bus Module Installation Instructions

First Step: SQL Setup

    - Import the rebatebus sql file, sales_flat_quote_item_rebate.sql, into your database.


Second Step: Install the Checkout Module

    - Place the Bus_Rebate module in your app/etc/modules folder. Add a /etc/modules/Bus_Rebate.xml file.

    - Place the cart and checkout rebates.phtml blocks somewhere in your design. For example, on my demo site its at template/rebate/cart/rebates.phtml and at template/rebate/cart/checkout-rebates.phtml.

    - Add the rebates block to your checkout cart layout. This can be done in two ways: the first, typical best-practice is to add it to your layout xml file - to accomplish this method, add the following line (with the appropriate file path):  <block type="rebate/cart_rebates" name="checkout.cart.rebates" as="rebates" template="rebate/cart/rebates.phtml"/>


Alternatively, you can add the reference from the checkout cart template file (on mine, its template/checkout/cart.xml) by putting the following line just after the line for adding coupons:

<?php echo $this->getLayout()->createBlock('rebatebus/cart')->setTemplate('rebate/cart/rebates.phtml')->toHtml(); ?>

 You can also use this method to add the rebates block to your checkout onepage layout. Use the same method as before, modifying the template/checkout/onepage/progress.phtml layout:

	<?php if ($this->getCheckout()->getStepData('payment', 'is_show')): ?>
		<div id="payment-progress-opcheckout">
			<?php echo $this->getChildHtml('payment.progress') ?>
		</div>
	<?php endif; ?>
	<?php echo $this->getLayout()->createBlock('rebatebus/cart')->setTemplate('bus/checkout-rebates.phtml')->toHtml(); ?>

Third Step: Adding the client scripts

     - Add the Javascript files. These will need some attention from a developer to handle things with your client layout. They go in the js/rebatebus directory. Example starter implementations can be found here: 

http://magento.rebatebus.com/js/rebatebus/rebatebus.js
http://magento.rebatebus.com/js/rebatebus/midstream.js

     - Essentially what needs to happen in these files is this: the update and clear functions need to be filled out for each place you want rebate quotes to appear. The update function should add the post-rebate price and utility logos wherever it finds eligible product ids. Change to zip code 82001 using the widget at the upper right hand side of magento.rebatebus.com with Commercial property type set. You'll then see some offers from our Test Program pop in to the page. Use the debugger tools on your browser to play around with rebatebus.js to help inform your own implementation of a client JS file.


    - Before testing the client, you'll need to get at least one product id added to your Rebate Bus.The product id must match up with what you've got client side - for example, the attached Javascript looks for the 'id' property of the 'item' CSS class.

    - One easy way to accomplish this is to add a 'rebate-target' div wherever you want rebates to load. Just declare a div with 'class="rebate-target"', and 'id="rebate-target-<id>"'. Then, your client script can load in the rebate offers very simply just by looking for such divs, parsing out the <id> to initialize the SearchWidget. On 1.9.7, the following files are the key ones to modify:

    app/design/frontend/your-layout-directory/default/template/catalog/product/view/type/grouped.phtml
    app/design/frontend/your-layout-directory/default/template/catalog/product/view.phtml
    app/design/frontend/your-layout-directory/default/template/catalog/product/widget/new/content/new_list.phtml

    - If you wish to list rebate-eligible configurations for configurable products, refer to the following file of the repository:

    1.9/app/design/frontend/base/default/template/catalog/product/view/options/wrapper.phtml:


    - References to rebatebus.js, midstream.js, and the Rebate Bus widgets that they make use of must be in your main page.xml (layout/page.xml). See below for an example:


                <!-- load the search widget from rebate bus-->
                <reference name="head">
                   <block type="core/text" name="searchwidget">
                      <action method="setText">
                        <text>
                           <![CDATA[<script type="text/javascript" src="https://www.rebatebus.com/js/searchwidget.js"></script><script type="text/javascript">jQuery.noConflict();</script>]]>
                        </text>
                      </action>
                   </block>
                </reference>
                <!-- load the instant midstream incentive widget from rebate bus-->
                <reference name="head">
                   <block type="core/text" name="midstreamwidget">
                      <action method="setText">
                        <text>
                           <![CDATA[<script type="text/javascript" src="https://www.rebatebus.com/js/widget.js"></script><script type="text/javascript">jQuery.noConflict();</script>]]>
                        </text>
                      </action>
                   </block>
                </reference>
                <reference name="head">
                   <block type="core/text" name="fontawesome">
                      <action method="setText">
                        <text>
                           <![CDATA[<link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" /><script type="text/javascript">jQuery.noConflict();</script>]]>
                        </text>
                      </action>
                   </block>
                </reference>

                <action method="addJs"><script>rebatebus/midstream.js</script></action>
                <action method="addJs"><script>rebatebus/rebatebus.js</script></action>



Fourth Step: Adding your Keys

    - Generate a public and private API key using Rebate Bus. I'd suggest you put your account in test mode on that same account settings page in order to test this module.

    - Add your private API key and UID to the file app/code/local/Bus/Rebate/controllers/OnepageController.php. Add the public key and UID to the file js/rebatebus/rebatebus.js as well.


-----------------------------------------------------

Magento 2 Bus Module Installation Instructions

First Step: Install the Module

Second Step: Modify Product Templates to Load IDs

     - In this step, we need to ensure that the product ids are being loaded on the frontend wherever incentive offers should appear.

     - Out of the box for this Magento 2 version, the ids were already loading everywhere except in the cart. I modified the following file - vendor/magento/module-checkout/view/frontend/templates/cart/item/default.phtml - to include the 'data-product-id' property. Adding this property allows the application script (the one that calls doRebateApp from rebatebus.js) to pass in the product IDs in the cart.

Third Step: Adding the client scripts

     - Add the Javascript files. These will need some attention from a developer to handle things with your client layout. They go in the pub/js directory. Example starter implementations can be found here: 

http://magento2.rebatebus.com/magento2_clean/pub/js/rebatebus.js
http://magento2.rebatebus.com/magento2_clean/pub/js/midstream.js

    - References to these files must be in global configuration. Go to your Admin Panel, then Content -> Configuration, then edit the configuration of the store. In the HTML Head section, add the following references:


     - Essentially what needs to happen in these files is this: the update and clear functions need to be filled out for each place you want rebate quotes to appear. The update function should add the post-rebate price and utility logos wherever it finds eligible product ids. Change to zip code 82001 using the widget at the upper right hand side of magento.rebatebus.com with Commercial property type set. You'll then see some offers from our Test Program pop in to the page. Use the debugger tools on your browser to play around with rebatebus.js to help inform your own implementation of a client JS file.

    - Before testing the client, you'll need to get at least one product id added to your Rebate Bus.The product id must match up with what you've got client side - for example, the attached Javascript looks for the 'id' property of the 'item' CSS class.




Fourth Step: Adding your Keys

    - Generate a public and private API key using Rebate Bus. I'd suggest you put your account in test mode on that same account settings page in order to test this module.

    - Add your private API key and UID to the file app/code/local/Bus/Rebate/controllers/OnepageController.php. Add the public key and UID to the file js/rebatebus/rebatebus.js as well.



