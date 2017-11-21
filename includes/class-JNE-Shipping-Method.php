<?php
/**
 * JNE Shipping Method WooCommerce Jne Bams
 *
 * Menambahkan shipping method via jne extend WC_Shipping_Method
 *
 * @class    JNE_Shipping_Method
 * @package  WooCommerce-JNE-Bams/Classes
 * @category Class
 * @author   bams
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

if ( ! class_exists( 'JNE_Shipping_Method' ) ) {
	class JNE_Shipping_Method extends WC_Shipping_Method {

		/**
		 * Constructor for your shipping class
		 *
		 * @access public
		 * @return void
		 */
		public function __construct() {
			//parent::__construct();
			$this->id
				                      = 'bams-jne'; // Id for your shipping method. Should be unique.
			$this->method_title       = __( 'JNE Shipping Method',
				'bams' );  // Title shown in admin
			$this->method_description = __( 'Ongkos Kirim via JNE',
				'bams' ); // Description shown in admin
			$this->init();

		}

		/**
		 * Init your settings
		 *
		 * @access public
		 * @return void
		 */
		function init() {
			// Load the settings API
			$this->init_form_fields();
			$this->init_settings(); // This is part of the settings API. Loads settings you previously init. https://docs.woocommerce.com/document/settings-api/
			$this->enabled = isset( $this->settings['enabled'] )
				? $this->settings['enabled'] : 'yes'; //enabled setting in admin
			$this->title   = isset( $this->settings['title'] )
				? $this->settings['title'] : 'bams-jne-init';
			add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );

		}

		/**
		 * Initialise Shipping Settings Form Fields
		 *
		 * show fields option in admin
		 * Properties inherited from WC_Settings_API ,
		 * https://docs.woocommerce.com/document/settings-api/
		 */
		function init_form_fields() {
			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable', 'bams' ),
					'type'        => 'checkbox',
					'description' => __( 'Enable this shipping.', 'bams' ),
					'default'     => 'yes'
				),

				'title' => array(
					'title'       => __( 'Title', 'bams' ),
					'type'        => 'text',
					'description' => __( 'Title to be display on site',
						'bams' ),
					'default'     => __( 'JNE YES', 'bams' )
					//overided in admin setting
				),
				'tarif' => array(
					'title'       => __( 'Tarif', 'bams' ),
					'type'        => 'text',
					'description' => __( 'Tarif yang ditampilkan di depan', 'bams' ),
					'default'     => __( '99999', 'bams' )
					//overided in admin setting
				),
			);
		} // End init_form_fields()

		/**
		 * Field Kecamatan
		 *
		 * @access public
		 * @return string kecamatan
		 * @since  8.1.19
		 */


		/**
		 * calculate_shipping function.
		 *
		 * @access public
		 *
		 * @param mixed $package
		 *
		 * @return void
		 */
		public function calculate_shipping( $package = array() ) {

			global $woocommerce;
			$total_weight_cart = $woocommerce->cart->cart_contents_weight;
			$total_weight_jne  = $this->calculate_jne_weight( $total_weight_cart );
			if ( ! empty( $_GET['kecamatan'] ) ) {
				$kecamatan = 'tak ada kecamatan';
			};
			if ( ! empty( $_GET['kota'] ) ) {
				$kota = 'tak ada kota';
			};

			$url_api      = 'http://localhost/api/api.php/denpasar?filter=kecamatan,cs,';
			$query_string = $kecamatan;
			$url_to_sent  = $url_api . $query_string;
			$response     = JNE_API::call_jne_api( 'GET', $url_to_sent );
			$jsonObject   = json_decode( $response, true );
			$jsonObject   = JNE_API::php_crud_api_transform( $jsonObject );
			$ongkir       = $jsonObject['denpasar'][0];
			$layanan      = array_slice( $ongkir, 3 );

			foreach ( $layanan as $key => $value ) {
				$rate = array(
					'id'    => $this->id . $ongkir['kecamatan'] . '_' . $key,
					'label' => $key,
					'cost'  => $value * $total_weight_jne,
				);

				// Register the rate
				$this->add_rate( $rate );
			}
		}

		/**
		 * Calculate JNE Weight ceil to next kg
		 *
		 * @access public
		 *
		 * @param integer $weight in gram
		 *
		 * @return integer Total Weight in Kilograms
		 * @since  0.0.1
		 **/
		public function calculate_jne_weight( $total_weight_cart ) {
			$convert_to_kg     = $total_weight_cart / 1000;
			$total_weight_cart = ceil( $convert_to_kg );

			return $total_weight_cart;
		}

	}

}