<?php
/**
 * Front End WooCommerce Jne Bams
 *
 * Untuk menampilkan kecamatan, dan pilihan layanan
 *
 * @class    JNE_FRONTEND
 * @package  WooCommerce-JNE-Bams/Classes
 * @category Class
 * @author   bams
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class JNE_Frontend extends WC_Checkout {
	/**
	 * Constructor
	 *
	 * @return void
	 * @since 1.0.0
	 **/

	public function __construct() {
		// action hook to woocomerce
		add_filter( 'woocommerce_checkout_fields', array( $this, 'woocommerce_checkout_fields_bams' ) ); // 1
//		add_action( 'woocommerce_checkout_create_order',array($this, 'woocommerce_checkout_create_order_bams', 10, 2 )); //2  save the extra field when checkout is processed
//		add_action('woocommerce_checkout_process', array($this, 'woocommerce_checkout_process_bams')); //3 validation checkout proses Process the checkout
		/*	add_action( 'woocommerce_checkout_update_order_meta', array($this,  'woocommerce_checkout_update_order_meta_bams' ));// 4 Update the order meta with field value
			add_action( 'woocommerce_admin_order_data_after_billing_address', array($this, 'woocommerce_admin_order_data_after_billing_address_bams' )); // 5 Display field value on the order edit page*/

	}

	//1 add kecamatan checkout fields

	function woocommerce_checkout_fields_bams( $fields ) {

		$fields['billing']['billing_kecamatan'] = array(
			'label'    => __( 'Kecamatan', 'bams' ),
			'required' => true,
			'type'     => 'text',
			'class'    => array(
				'form-row-wide',
				'update_totals_on_change ',
				'update-kecamatan'
			),
			'priority' => 65
		);
		$fields['billing']['billing_postcode']  = array(
			'label'    => __( 'Kode Pos', 'bams' ),
			'required' => false,
			'class'    => array( 'form-row-wide' ),
			'priority' => 80
		);
		$fields['billing']['billing_state']     = array(
			'label'    => __( 'Propinsi', 'bams' ),
			'required' => true,
			'class'    => array( 'form-row-wide' ),
			'priority' => 90
		);

		return $fields;
	}

}

