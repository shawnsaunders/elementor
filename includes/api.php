<?php
namespace Elementor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Api {

	//private static $_api_info_url = 'http://my.elementor.com/api/v1/info/';
	private static $_api_info_url = 'http://pojo-office:5566/api/v1/info/';

	private static function _get_info_data( $force = false ) {
		$cache_key = 'elementor_remote_info_api_data';
		$info_data = get_transient( $cache_key );

		if ( $force || false === $info_data ) {
			$response = wp_remote_post( self::$_api_info_url, [
				'timeout' => 25,
				'body' => [
					'site_lang' => get_bloginfo( 'language' ),
				],
			] );

			if ( is_wp_error( $response ) || 200 !== (int) wp_remote_retrieve_response_code( $response ) ) {
				set_transient( $cache_key, [], 2 * HOUR_IN_SECONDS );

				return false;
			}

			$info_data = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $info_data ) || ! is_array( $info_data ) ) {
				set_transient( $cache_key, [], 2 * HOUR_IN_SECONDS );

				return false;
			}

			set_transient( $cache_key, $info_data, 12 * HOUR_IN_SECONDS );
		}

		return $info_data;
	}

	public static function get_upgrade_notice() {
		$data = self::_get_info_data( true );
		if ( empty( $data['upgrade_notice'] ) )
			return false;

		return $data['upgrade_notice'];
	}
}
