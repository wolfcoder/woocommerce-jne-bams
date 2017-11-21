<?php
/**
 * Call api JNE
 *
 * Created by PhpStorm.
 * User: BBG
 * Date: 8/5/2017
 * Time: 2:12 AM
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


if ( ! class_exists( 'JNE_API' ) ) {
	class JNE_API {

		/**
		 * call api
		 *
		 * @return mixed
		 */
		public static function call_jne_api( $method, $url, $data = false ) {
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
			curl_setopt( $ch, CURLOPT_URL, $url );
			if ( $data ) {
				curl_setopt( $ch, CURLOPT_POSTFIELDS, $data );
				$headers   = array();
				$headers[] = 'Content-Type: application/json';
				$headers[] = 'Content-Length: ' . strlen( $data );
				curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
			}
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );

			return curl_exec( $ch );
		}


		/**
		 * transformm to tree
		 *
		 */

		public static function php_crud_api_transform( &$tables ) {
			$get_objects = function (
				&$tables, $table_name, $where_index = false,
				$match_value = false
			) use ( &$get_objects ) {
				$objects = array();
				if ( isset( $tables[ $table_name ]['records'] ) ) {
					foreach ( $tables[ $table_name ]['records'] as $record ) {
						if ( $where_index === false
						     || $record[ $where_index ] == $match_value
						) {
							$object = array();
							foreach (
								$tables[ $table_name ]['columns'] as $index =>
								$column
							) {
								$object[ $column ] = $record[ $index ];
								foreach ( $tables as $relation => $reltable ) {
									if ( isset( $reltable['relations'] ) ) {
										foreach (
											$reltable['relations'] as $key =>
											$target
										) {
											if ( $target
											     == "$table_name.$column"
											) {
												$column_indices
													= array_flip( $reltable['columns'] );
												$object[ $relation ]
													= $get_objects( $tables,
													$relation,
													$column_indices[ $key ],
													$record[ $index ] );
											}
										}
									}
								}
							}
							$objects[] = $object;
						}
					}
				}

				return $objects;
			};
			$tree        = array();
			foreach ( $tables as $name => $table ) {
				if ( ! isset( $table['relations'] ) ) {
					$tree[ $name ] = $get_objects( $tables, $name );
					if ( isset( $table['results'] ) ) {
						$tree['_results'] = $table['results'];
					}
				}
			}

			return $tree;
		}

	}
}
