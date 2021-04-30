<?php
/*
 * Plugin Name: Out of Stock Message for WooCommerce
 * Plugin URI: https://github.com/coderstimes/out-of-stock-message-woocommerce
 * Description: Out Of Stock product Message for WooCommerce plugin. This plugin for those who want to set custom notification message when individual product out of stock. It will show message on single product after title for visitor notify.
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

class StockOut_Msg_CodersTime {

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
        add_action( 'admin_enqueue_scripts', [ $this, 'wcosm_admin_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'wcosm_scripts_frontend' ] );

        add_action('woocommerce_product_options_inventory_product_data', [ $this,'wcosm_textbox'], 11);
        add_action('woocommerce_process_product_meta', [ $this, 'wcosm_product_save_data'], 10, 2);
        add_action('woocommerce_single_product_summary',[$this,'wc_single_product_msg'], 6);
        add_filter( 'woocommerce_inventory_settings', [ $this,'wcosm_setting'], 1 );

        add_action('wp_dashboard_setup', [ $this,'add_stockout_msg_dashboard'] );

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

	public function wcosm_admin_scripts ( )
	{
		$screen = get_current_screen();
		if( $screen->post_type == 'product' &&  $screen->base == 'post') {
			?>
			<style>
				._out_of_stock_note_field, ._wc_sm_use_global_note_field { display: none; }
				._out_of_stock_note_field.visible, ._wc_sm_use_global_note_field.visible {display: block; }
				#_out_of_stock_note {min-width: 70%;min-height: 120px; }
			</style>	
			<?php
			wp_enqueue_script( 'wcosm-msg', WP_WCSM_DIR . 'assets/wc-sm.js', array('jquery'), filemtime( WP_WCSM_PLUGIN_PATH .'/assets/wc-sm.js') );
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
				'desc_tip' => true,
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
				'value' => esc_attr( $post->_wcosm_use_global_note ),
				'desc_tip' => true,
				'description' => __( 'Tick this if you want to show global out of stock message.', 'wcosm' ),
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

    /*
    	* Add Dashboard metabox for quick review and go to settings page
    */

    public function add_stockout_msg_dashboard() {
        add_meta_box('stockout_msg_widget', __('Stock Out Message','wcosm'), [$this,'stockout_msg_dashboard_widget'], 'dashboard', 'side', 'low');
    }

    /*Dashboard metabox details info */
    public function stockout_msg_dashboard_widget() {
    	$global_msg = get_option('woocommerce_out_of_stock_message');

    	?>
    	<div class="rss-widget">
    		<h3> <strong> <?php echo __('Stock Out Current Message','wcosm');  ?>: </strong></h3>
    		<p>
    			<?php echo $global_msg; ?>
    		</p>
    		<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=products&section=inventory' ) ?>"><button style="padding: 10px 30px;margin:10px 0px; font-size: 16px;background: #607d8b;color: #fff;border: none;border-radius: 5px;width: fit-content;"> <?php echo __( 'Change Message', 'wcosm' ) ?> </button></a>
    	</div>
        <?php 
    }


}


add_action( 'plugins_loaded',function(){
	/*Include the main WooCommerce class.*/
	if ( ! class_exists( 'WooCommerce', false ) ) {
		die('please install woocommerce plugin first to use the plugin');
	} else {
		new StockOut_Msg_CodersTime;
	}
} );
