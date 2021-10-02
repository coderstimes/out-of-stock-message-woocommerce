=== Out of Stock Message for WooCommerce ===
Contributors: coderstime, lincolndu
Tags: woocommerce, wc, stock message, message, out of stock message, custom out of stock message, STOCKOUT, stock out message, Stock, Inventory Limit, Stock out Note, Out of Stock Note, Out of Stock product Message
Requires at least: 4.9 or higher
Tested up to: 5.8
Requires PHP: 5.6
Stable tag: 1.0.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html

Out of Stock Message for WooCommerce is an official plugin maintained by the Coderstime that add an extra feature on the “woocommerce inventory settings” option on the woocommerce.

== Description ==
Woocommerce out of stock custom message is an official Coderstime plugin, and will be fully supported and maintained until at least 2022, or as long as is necessary.

FEATURES
Allows individual out of stock message from product editor.
Allows global message from WooCommerce inventory setting.
Why does this plugin?
This plugin allows you to supply a literal message for out of stock products. You can also add links and/or contact info which visitors can get information from. So, you’ll never lose your potential customers.

Default \"Out of Stock\" Message
1. Go to WooCommerce > Settings > Products > Inventory
2. Type your message on \"Out of Stock Message\" field
3. Save Changes

Individual \"Out of Stock\" Message
1. Go to Add/Edit product panel
2. Open Inventory setting of product data
3. On Stock Status, check Out of Stock
4. The Out-of-Stock Note field is displayed. Type your note in there.
5. Click Publish or Update


For Developers
By default, you don\'t have to modify any code of template file. Because the plugin automatically displays out of stock note right after product title in single product page (as seen above).
If you want to display the out of stock note at other places, use the codes below.
Getting individual note value: get_post_meta($post->ID, \'_out_of_stock_note\', true);
Getting global note value: get_option(\'woocommerce_out_of_stock_note\');
== Installation ==
1. Upload this plugin to the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to WooCommerce > Settings > Products > Inventory. Then type your note on \"Out of Stock Note\" field. Then Save your setting.
4. Go to Add/Edit product panel. Open Inventory setting of product data, select \"Out of Stock\" on \"Stock Status\" field. Then check global note or set individual note in \"Out of Stock Note\" field.


== Frequently Asked Questions ==
When activated this plugin will allow the admin to make changes in the custom fields. These settings can be changed at the WC Out of Stock Message Plugin.

= What this plugin for? =

It's mainly for who want to show out of stock product message in single product page.

= Whats the facility? =

Admin can quickly type and set out of stock message. It can be set global message for all out of stock product and can custom message for special product.

= What is Out of Stock plugin ? =

Out of stock plugin is a quick solution for woocommerce product inventory system. When a product will be out of stock it will show a custom message which is one time set from woocommerce setting page. So it's totally hassle free and easy to use. 



== Screenshots ==
1. Image for the Plugin Position
2. Image for the Plugin Form
3. Result of the Plugin Action
4. Dashboard Metabox for Quick View

== Changelog ==

= 1.0.3 =
* bug fix for default data

= 1.0.2 =
* add customizer settings on woocommerce section
* add out of stock message widget 
* woocommerce default stock out recipient use for email notice 
* woocommerce plugin not install admin notice 
* fix class StockOut_Msg_CodersTime when not exist issue
 
= 1.0.1 =
* Admin Email alert when stock out
* Change message Background color
* Change message Text color
* Product page message showing area 


= 1.0.0 =
* Initial release.

