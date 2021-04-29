<?php
/*
 * Plugin Name: WC Out of Stock Message
 * Plugin URI: https://github.com/coderstimes/wc-out-of-stock-message
 * Description: WooCommerce out of stock custom Message plugin
 * Version: 1.0.0
 * Author: coderstime
 * Author URI: https://profiles.wordpress.org/coderstime/
 * Domain Path: /languages
 * License: GPLv2 or later
 * Text Domain: wcosm
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
        register_activation_hook( WP_WCSM_FILE, [ $this, 'activate' ] ); /*plugin activation hook*/
        register_deactivation_hook( WP_WCSM_FILE, [ $this, 'deactivate' ] ); /*plugin deactivation hook*/
        
        add_action( 'init', [ $this, 'localization_setup' ] ); /*Localize our plugin*/
        add_filter( 'plugin_action_links_' . WP_WCSM_BASENAME, [ $this, 'action_links' ] );        
        add_action( 'admin_enqueue_scripts', [ $this, 'wcosm_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'wcosm_scripts_frontend' ] );

        add_action('woocommerce_product_options_inventory_product_data', [ $this,'wcosm_textbox'], 11);
        add_action('woocommerce_process_product_meta', [ $this, 'wcosm_product_save_data'], 10, 2);
        add_action('woocommerce_single_product_summary',[$this,'wc_single_product_msg'], 6);
        add_filter( 'woocommerce_inventory_settings', [ $this,'wcosm_setting'], 1 );

    }

    /**
     *
     * run when plugin install
     * install time store on option table
     */
    

    public function activate ( ) 
    {
        add_option( 'wcosm_active',time() );
    }

    /**
     *
     * run when deactivate the plugin
     * store deactivate time on database option table
     */
    

    public function deactivate ( ) 
    {
    	defined( 'WP_UNINSTALL_PLUGIN' ) || exit;
        update_option( 'wcosm_deactive',time() );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() 
    {
        load_plugin_textdomain( 'wcsm', false, dirname( WP_WCSM_BASENAME ) . '/languages/' );
    }


    /*
	 * Scripts
	 * Admin screen
	 */

	public function wcosm_scripts ( )
	{
		$screen = get_current_screen();
		if( $screen->post_type == 'product' &&  $screen->base == 'post') { 
			$asset_file_link = plugins_url( '/assets/', __FILE__ );
	        $folder_path= __DIR__ .'/assets/';

			wp_enqueue_style( 'wc-inventory-stock-msg', $asset_file_link . 'wc-sm.css', filemtime( $folder_path.'wc-sm.css' ) );
			wp_enqueue_script( 'wc-inventory-stock-msg', $asset_file_link . 'wc-sm.js', array('jquery'), filemtime($folder_path.'wc-sm.js') );
		}
		
	}

	/*
	* Scripts
	* Front end
	*/

	public function wcosm_scripts_frontend()
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

	public function wcosm_textbox ( )
	{
		global $post;

	    $val = '';
	    $get_saved_val = get_post_meta($post->ID, '_out_of_stock_msg', true);
	    if($get_saved_val != ''){
	      $val = $get_saved_val;
	    }

		woocommerce_wp_textarea_input(  array(
				'id' => '_out_of_stock_msg',
				'wrapper_class' => 'outofstock_field',
				'label' => __( 'Out of Stock Message', 'wcosm' ),
				'desc_tip' => 'true',
				'value' => $val,
				'description' => __( 'Enter an optional note to out of stock item.', 'wcosm' ),
				'style' => 'width:70%;'
			)
		);

		woocommerce_wp_checkbox( array(
				'id' => '_wcosm_use_global_note',
				'wrapper_class' => 'outofstock_field',
				'label' => __( 'Use Global Message', 'wcosm' ),
				'cbvalue' => 'yes',
				'value' => esc_attr( $post->_wcosm_use_global_note )
			)
		);
	}

	/*Saving the value*/
	public function wcosm_product_save_data( $post_id, $post )
	{
			$note = wp_filter_post_kses( $_POST['_out_of_stock_msg'] );
			$global_checkbox = wc_clean( $_POST['_wcosm_use_global_note'] );

	    	// save the data to the database
			update_post_meta($post_id, '_out_of_stock_msg', $note);
			update_post_meta($post_id, '_wcosm_use_global_note', $global_checkbox);

	}

	/*Display message*/

	public function wc_single_product_msg ( ) 
	{
		global $post, $product;
		$get_saved_val = get_post_meta( $post->ID, '_out_of_stock_msg', true);
		$global_checkbox = get_post_meta($post->ID, '_wcosm_use_global_note', true);
		$global_note = get_option('woocommerce_out_of_stock_message');

		if( $get_saved_val && !$product->is_in_stock() && $global_checkbox != 'yes') { 
			add_filter('woocommerce_get_stock_html','__return_false');
		?>
			<div class="outofstock-message">
				<?php echo $get_saved_val; ?>
			</div><!-- /.outofstock-product_message -->
		<?php }

		if( $global_checkbox == 'yes' && !$product->is_in_stock() ) {
			add_filter('woocommerce_get_stock_html','__return_false');
		?>
			<div class="outofstock-message">
				<?php echo $global_note; ?>
			</div> <!-- /.outofstock_global-message -->
		<?php }
	}

	/*WooCommerce settings->product inverntory tab new settings field for out-of-stock message/note*/
	public function wcosm_setting( $setting ) 
	{
		$out_stock_message = array (
			'title' => __( 'Out of Stock Message', 'woocommerce' ),
			'desc' 		=> __( 'Message for out of stock product.', 'woocommerce' ),
			'id' 		=> 'woocommerce_out_of_stock_message',
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
                '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) . '">' . __( 'Settings', 'wcosm' ) . '</a>',
                '<a href="' . esc_url( 'https://www.facebook.com/coderstime' ) . '">' . __( 'Support', 'wcosm' ) . '</a>'
            ], $links );
    }

}


add_action( 'plugins_loaded',function(){

	/*Include the main WooCommerce class.*/
	if ( ! class_exists( 'WooCommerce', false ) ) {
		die('please install woocommerce plugin first to use the plugin');
	} else {
		new WC_Stock_Msg;
	}
} );
