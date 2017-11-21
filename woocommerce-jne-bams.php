<?php
/**
 * Plugin Name: WooCommerce Jne Bams
 * Plugin URI: http://bambangsetyawan.com/plugin-wordpress/woocommerce-jne-bams/
 * Description: plugin untuk add Shipping method via jne yes, jne reg, jne oke
 * Version: 1.0.0
 * Author: WooCommerce
 * Author URI: http://woocommerce.com/
 * Developer: Bambang setyawan
 * Developer URI: http://bambangsetyawan.com/
 * Text Domain: woocommerce-jne-bams
 * Domain Path: /languages
 *
 * Copyright: © 2009-2015 WooCommerce.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Check if WooCommerce is active
 */

if ( in_array( 'woocommerce/woocommerce.php',
	apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) )
) {
	// Tie into WC Hooks and any functions that should run on load.
	add_action( 'woocommerce_shipping_init', 'JNE_Shipping_Method_init', 10 );// add to collection array woordpress hook
	add_filter( 'woocommerce_shipping_methods', 'add_JNE_Shipping_Method', 14 );
	add_action( 'wp_enqueue_scripts', 'ajax_kecamatan' ); // ajax kecamatan
	include_once( 'includes/class-JNE-API.php' );
	/**
	 * Create a function to house your class
	 *
	 * To ensure the classes you need to extend exist, you should wrap your class
	 * in a function which is called after all plugins are loaded:
	 */
	function JNE_Shipping_Method_init() {
		// include() or require() any necessary files here...
		include_once( 'includes/class-JNE-Shipping-Method.php' );
		include_once( 'includes/class-JNE-Frontend.php' );
		//include_once ('includes/class-JNE-Kecamatan.php');
		new JNE_Frontend();
	}

	add_action( 'wp_ajax_nopriv_select_kecamatan_JNE', 'select_kecamatan_JNE' );
	add_action( 'wp_ajax_select_kecamatan_JNE', 'select_kecamatan_JNE' );

	function select_kecamatan_JNE() {
		$kecamatan = (string) $_REQUEST['kecamatan'];
		$kota      = (string) $_REQUEST['kota'];
		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			$url_api      = 'http://localhost/api/api.php/denpasar?filter=kecamatan,cs,';
			$query_string = $kecamatan;
			$url_to_sent  = $url_api . $query_string;
			$response     = JNE_API::call_jne_api( 'GET', $url_to_sent );
			$jsonObject   = json_decode( $response, true );
			$jsonObject   = JNE_API::php_crud_api_transform( $jsonObject );
			$ongkir       = $jsonObject['denpasar'][0];
			$layanan      = array_slice( $ongkir, 3 );

			push_jne_rates( $kecamatan, $ongkir );

			die();
		} else {
			echo 'else' . $kota;
			exit();
		}
	}

	//return ongkir
	function push_jne_rates( $kecamatan, $ongkir ) {
		print_r( $ongkir );
		//return $kecamatan;
		echo $kecamatan;

	}


	function add_JNE_Shipping_Method( $methods ) {
		$methods['JNE_Shipping_Methods'] = 'JNE_Shipping_Method';

		return $methods;
	}

	function ajax_kecamatan() {
		wp_enqueue_script( 'bams',
			plugin_dir_url( __FILE__ ) . 'includes/kecamatan.js',
			array( 'jquery' ), '0.1', true );
		wp_enqueue_style( 'custom-style-jne',
			plugin_dir_url( __FILE__ ) . 'includes/kecamatan.css' );
		wp_localize_script( 'bams', 'jnekecamatan',
			array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
	}


	add_action( 'woocommerce_checkout_create_order', 'woocommerce_checkout_create_order_bams', 10, 2 ); //2  save the extra field when checkout is processed
	add_action( 'woocommerce_checkout_process', 'woocommerce_checkout_process_bams' ); //3 validation checkout proses
	add_action( 'woocommerce_checkout_update_order_meta', 'woocommerce_checkout_update_order_meta_bams' );// 4 Update the order meta with field value
	//add_action( 'woocommerce_admin_order_data_after_billing_address', 'woocommerce_admin_order_data_after_billing_address_bams' ); // 5 Display field value on the order edit page


	// 2 save the extra field when checkout is processed
	function woocommerce_checkout_create_order_bams( $order, $data ) {
		// don't forget appropriate sanitization if you are using a different field type
		if ( ! empty( $_POST['billing_kecamatan'] ) ) {
			$order->update_meta_data( 'billing_kecamatan', sanitize_text_field( $data['billing_kecamatan'] ) );
		}
	}

	/**
	 * 3  validation checkout proses
	 *
	 * Check if set, if its not set add an error.
	 */

	function woocommerce_checkout_process_bams() {
		if ( ! $_POST['billing_kecamatan'] ) {
			wc_add_notice( __( 'Please enter billing_kecamatan.' ), 'error' );
		}
	}

	/**
	 * 4 Update the order meta with field value
	 */
	function woocommerce_checkout_update_order_meta_bams( $order_id ) {
		if ( ! empty( $_POST['billing_kecamatan'] ) ) {
			update_post_meta( $order_id, 'billing_kecamatan', sanitize_text_field( $_POST['billing_kecamatan'] ) );
		}
	}

	/**
	 * 5 Display field value on the order edit page
	 */
	function woocommerce_admin_order_data_after_billing_address_bams( $order ) {
		echo '<p><strong>' . __( 'billing_kecamatan' ) . ':</strong> ' . get_post_meta( $order->id, 'billing_kecamatan', true ) . '</p>';
	}

}