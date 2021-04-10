Description
WooCommerce out of stock custom message is an official plugin maintained by the Coderstime team that adds an extra feature on the 'WooCommerce inventory settings” option on the woocommerce.
WooCommerce out of stock custom message is an official Coders Time plugin, and will be fully supported and maintained until at least 2022, or as long as is necessary.
FEATURES
* Allows individual out of stock note from product editor.
* Allows global note from WooCommerce inventory setting.
Why does this plugin?
This plugin allows you to supply a literal message for out of stock products. You can also add links and/or contact info which visitors can get information from. So, you’ll never lose your potential customers.
Installation
1. Upload this plugin to the /wp-content/plugins/ directory.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to WooCommerce > Settings > Products > Inventory. Then type your note on "Out of Stock Note" field. Then Save your setting.
4. Go to Add/Edit product panel. Open Inventory setting of product data, select "Out of Stock" on "Stock Status" field. Then check global note or set individual note in "Out of Stock Note" field.

Default "Out of Stock" Message
1. Go to WooCommerce > Settings > Products > Inventory
2. Type your note on "Out of Stock Note" field
3. Save Changes Individual "Out of Stock" Message
1. Go to Add/Edit product panel
2. Open Inventory setting of product data
3. On Stock Status, check Out of Stock
4. The Out-of-Stock Note field is displayed. Type your note in there.
5. Click Publish or Update
Developer
By default, you don't have to modify any code of template file. Because the plugin automatically displays out of stock note right after product title in single product page (as seen above).
If you want to display the out of stock note at other places, use the codes below.
Getting individual note value: get_post_meta($post->ID, '_out_of_stock_note', true);
Getting global note value: get_option('woocommerce_out_of_stock_note');
MORE INFO
Send me your question to my contacts below:
Mail: 
