<?php
/**
 * Created by PhpStorm.
 * User: BBG
 * Date: 8/1/2017
 * Time: 9:45 PM
 */

/*if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}*/

if ( ! class_exists( 'JNE_Kecamatan' ) ) {
	class JNE_Kecamatan
		//extends JNE_Shipping_Method
	{

		public function __construct() {
			//include_once('class-JNE-Shipping-Method.php');
			//parent::__construct();
			//parent::calculate_shipping();
			//calculate_shipping($package = array());
			$this->get_field_kecamatan();
		}

		/**
		 * Field Kecamatan
		 *
		 * @access public
		 * @return string kecamatan
		 * @since  8.1.19
		 */
		public function get_field_kecamatan() {
			$kecamatan = $_GET["kecamatan"];
			print_r( $kecamatan );
			print_r( $_GET );
		}
	}
}

new JNE_Kecamatan();
