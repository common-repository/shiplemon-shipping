=== Shiplemon Shipping for WooComerce ===
Contributors: shiplemon, spyrosvl
Tags: shipping calculator, shipping plugin, multi-carrier shipping, WooCommerce shipping
Requires at least: 5.3
Tested up to: 6.0.1
Requires PHP: 7.0
Stable tag: 1.0.0
WC requires at least: 4.7
WC tested up to: 6.8
License: GPLV3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

A platform that connects all courier companies in one system giving the possibility to compare shipping costs, create voucher, tracking numbers etc.

== Description ==
Shiplemon is an advanced shipping platform integrated with WooCommerce stores, allowing you to calculate the shipping costs on the checkout based on weight and/or cart total. The platform allows you to combine dynamic and fixed pricing together as well as putting specific rules giving you the flexibility to handle shipping differently when the total amount of the cart is high.


= Shiplemon supports the following shipping methods and scenarios =


* Integration with your own carrier contracts or using Shiplemon contracts to get rates and ship items all over the world
* Checkout plugin that calculates the shipping costs based on cart weight (additionally the estimated delivery days can be calculated)
* Ability to show multiple carriers at the same time based on rules
* Selection of specific carrier services to be shown based on the carrier settings (i.e show only DHL express, and UPS Saver)
* Adding handling fee or an insurance cost after reaching a certain order value
* Disable/hide or change shipping method and cost if a defined rule has been matched in the cart (based on the total amount of the cart) 
 
= FEATURES =

* Unlimited shipping methods and costs calculation rules
* Possibility to add unique shipping method titles such us “Free shipping” or any phrase that will be shown based on the total amount of the cart
* Shipping cost based on cart total and/or weight
* Minimum and maximum values for cart total and/or weight
* Summing up the costs of e.g. two different rules at the same time e.g. one based on cart total and the second based on weight
* Free shipping over amount override
* Integration with multiple shipping carriers
* Default package weight and dimensions to ensure that even if there are products with missing information the plugin will be always operational.
 
- Supported carriers for integration, voucher creation, tracking and shipping calculation in the Shiplemon platform:

International carriers:

* DHL 
* UPS
* TNT
* Fedex
 
Greek carriers:

* ACS
* ELTA
* Speedex
* Comet Courier
* Geniki Taxidromiki
* City Courier
* Courier center
* Tas Courier

== Installation ==
1. Upload the entire `shiplemon-shipping` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the \'Plugins\' menu in WordPress
3. Set your Shiplemon API KEY and default values via WooCommerce > Settings > Shipping > Shiplemon

== Frequently Asked Questions ==
= How to configure the plugin? =

To start using the Shiplemon checkout plugging you have to first create an account in https://app.shiplemon.com/
Go to your settings >  profile > Integration with API
There you can copy the api key and then use it in the plugin in the following tab: WooCommerce > Settings > Shipping > Shiplemon
Paste the API key there 
Also fill in the default values for the height, weight, length
Then click on the enable button and you are ready to go. Check the next question on how to configure the  shipping rules

= How to configure shipping rules for the checkout? =

Go to https://app.shiplemon.com/ and click on setting > checkout in order to configure the rules for your checkout shipping options

== Screenshots ==
1. Shiplemon settings page

== Changelog ==
= 1.0.0 =

* Initial version.

== Upgrade Notice ==
= 1.0.0 =

* Initial version.