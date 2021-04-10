<?php
/*
 * Plugin Name: WC Out of Stock Message
 * Plugin URI: https://github.com/coderstimes/wc-out-of-stock-message
 * Description: WooCommerce out of stock custom Message plugin
 * Version: 1.0
 * Author: coderstime
 * Author URI: https://profiles.wordpress.org/coderstime/
 * Domain Path: /languages
 * License: GPLv2 or later
 * Text Domain: wc_sm
 */

if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Woocommerce Out Of Stock Message class
 *
 * The class that holds the entire Woocommerce Out Of Stock Message plugin
 *
 * @author Coders Time <coderstime@gmail.com>
 */

define( 'WP_WCSM_FILE', __FILE__ );
define( 'WP_WCSM_PLUGIN_PATH', __DIR__ );
define( 'WP_WCSM_BASENAME', plugin_basename( WP_WCSM_FILE ) );
define( 'WP_WCSM_DIR', plugin_dir_url( WP_WCSM_FILE ) );
define( 'WP_WCSM_PATH', plugin_dir_path( WP_WCSM_FILE ) );


class WC_Stock_Msg {

	/**
     *
     * construct method description
     *
     */

    public function __construct ( ) 
    {
    	add_action( 'plugins_loaded',[ $this,'loaded_plugin'] );
        register_activation_hook( WP_WCSM_FILE, [ $this, 'activate' ] ); /*plugin activation hook*/
        register_deactivation_hook( WP_WCSM_FILE, [ $this, 'deactivate' ] ); /*plugin deactivation hook*/
        
        add_action( 'init', [ $this, 'localization_setup' ] ); /*Localize our plugin*/
        add_filter( 'plugin_action_links_' . WP_WCSM_BASENAME, [ $this, 'action_links' ] );        
        add_action( 'admin_enqueue_scripts', [ $this, 'wc_sm_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'wc_sm_scripts_frontend' ] );

        add_action('woocommerce_product_options_inventory_product_data', [ $this,'wc_sm_textbox'], 11);
        add_action('woocommerce_process_product_meta', [ $this, 'wc_sm_product_save_data'], 10, 2);
        add_action('woocommerce_single_product_summary', [ $this,'wc_sm_display_outofstock_message'], 6);
        add_filter( 'woocommerce_inventory_settings', [ $this,'wc_sm_setting'], 1 );

   
    }

    /*trigger when plugin loaded*/

    public function loaded_plugin ( ) {
    	/*Include the main WooCommerce class.*/
		if ( ! class_exists( 'WooCommerce', false ) ) {
			die('please install woocommerce plugin first to use the plugin');
		}
    }

    /**
     *
     * run when plugin install
     * install time store on option table
     */
    

    public function activate ( ) 
    {
        add_option('wcsm_active',time());
    }

    /**
     *
     * run when deactivate the plugin
     * store deactivate time on database option table
     */
    

    public function deactivate ( ) 
    {
    	defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
        update_option('wcsm_deactive',time());
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() 
    {
        load_plugin_textdomain( 'wcsm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }


    /*
	 * Scripts
	 * Admin screen
	 */

	public function wc_sm_scripts()
	{
		wp_enqueue_style( 'wc-inventory-stock-msg', plugin_dir_url(plugin_basename(__FILE__)) . 'assets/wc-sm.css', '1.0' );
		wp_enqueue_script( 'wc-inventory-stock-msg', plugin_dir_url(plugin_basename(__FILE__)) . 'assets/wc-sm.js', array('jquery'), '1.0' );
	}

	/*
	* Scripts
	* Front end
	*/

	public function wc_sm_scripts_frontend()
	{
		?>
		<style>
			.outofstock-message {margin-top: 20px;margin-bottom: 20px;background: #fff999;padding: 20px; }
			.outofstock-message a { font-style: italic; }
		</style>
		<?php
	}

	/*
	 * Fields
	 */

	public function wc_sm_textbox ( )
	{
		global $post;

	    $val = '';
	    $get_saved_val = get_post_meta($post->ID, '_out_of_stock_note', true);
	    if($get_saved_val != ''){
	      $val = $get_saved_val;
	    }

		woocommerce_wp_textarea_input(  array(
				'id' => '_out_of_stock_note',
				'wrapper_class' => 'outofstock_field',
				'label' => __( 'Out of Stock Note', 'woocommerce' ),
				'desc_tip' => 'true',
				'value' => $val,
				'description' => __( 'Enter an optional note to out of stock item.', 'woocommerce' ),
				'style' => 'width:70%;'
			)
		);

		woocommerce_wp_checkbox( array(
				'id' => '_wc_sm_use_global_note',
				'wrapper_class' => 'outofstock_field',
				'label' => __( 'Use Global Note', 'woocommerce' ),
				'cbvalue' => 'yes',
				'value' => esc_attr( $post->_wc_sm_use_global_note )
			)
		);
	}

	/*Saving the value*/
	public function wc_sm_product_save_data( $post_id, $post )
	{
			$note = wp_filter_post_kses( $_POST['_out_of_stock_note'] );
			$global_checkbox = wc_clean( $_POST['_wc_sm_use_global_note'] );

	    	// save the data to the database
			update_post_meta($post_id, '_out_of_stock_note', $note);
			update_post_meta($post_id, '_wc_sm_use_global_note', $global_checkbox);

	}

	/*Display message*/

	public function wc_sm_display_outofstock_message ( ) 
	{
		global $post, $product;
		$get_saved_val = get_post_meta($post->ID, '_out_of_stock_note', true);
		$global_checkbox = get_post_meta($post->ID, '_wc_sm_use_global_note', true);
		$global_note = get_option('woocommerce_out_of_stock_note');

		if( $get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes') { ?>

			<div class="outofstock-message">
				<?php echo $get_saved_val; ?>
			</div><!-- /.outofstock-message -->

		<?php }

		if( $global_checkbox == 'yes' && !$product->is_in_stock() ) { ?>
			<div class="outofstock-message">
				<?php echo $global_note; ?>
			</div> <!-- /.outofstock-message -->
		<?php }
	}

	/*WooCommerce settings->product inverntory tab new settings field for out-of-stock message/note*/
	public function wc_sm_setting( $setting ) 
	{
		$out_stock_message = array (
						'title' => __( 'Out of Stock Note', 'woocommerce' ),
						'desc' 		=> __( 'Note for out of stock product.', 'woocommerce' ),
						'id' 		=> 'woocommerce_out_of_stock_note',
						'css' 		=> 'width:60%; height: 125px;margin-top:10px;',
						'type' 		=> 'textarea',
						'autoload'  => false
					);
		array_splice( $setting, 2, 0, [$out_stock_message]);
		return $setting;
	}


    /**
     * Show action links on the plugin screen
     *
     * @param mixed $links
     * @return array
     */
    public function action_links( $links ) {
        return array_merge(
            [
                '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '">' . __( 'Settings', 'messengerbot' ) . '</a>',
                '<a href="' . esc_url( 'https://www.facebook.com/coderstime' ) . '">' . __( 'Support', 'messengerbot' ) . '</a>'
            ], $links );
    }

}

new WC_Stock_Msg;

















