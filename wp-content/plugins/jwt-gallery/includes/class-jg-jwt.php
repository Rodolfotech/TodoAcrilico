<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Minimal HS256 JWT issue/verify. No external dependency.
 */
class JG_JWT {

	const OPTION_SECRET = 'jg_jwt_secret';

	public static function get_secret() {
		if ( defined( 'JG_JWT_SECRET' ) && JG_JWT_SECRET ) {
			return JG_JWT_SECRET;
		}

		$secret = get_option( self::OPTION_SECRET );
		if ( ! $secret ) {
			$secret = bin2hex( random_bytes( 32 ) );
			update_option( self::OPTION_SECRET, $secret, false );
		}

		return $secret;
	}

	private static function base64url_encode( $data ) {
		return rtrim( strtr( base64_encode( $data ), '+/', '-_' ), '=' );
	}

	private static function base64url_decode( $data ) {
		$padded = str_pad( $data, strlen( $data ) % 4 === 0 ? strlen( $data ) : strlen( $data ) + 4 - strlen( $data ) % 4, '=' );
		return base64_decode( strtr( $padded, '-_', '+/' ) );
	}

	const DEFAULT_TTL = DAY_IN_SECONDS;

	/**
	 * @param array $claims Custom claims (must include 'sub').
	 * @param int   $ttl    Seconds until expiry.
	 * @return string
	 */
	public static function issue( array $claims, $ttl = self::DEFAULT_TTL ) {
		$header = array(
			'alg' => 'HS256',
			'typ' => 'JWT',
		);

		$now             = time();
		$claims['iat']   = $now;
		$claims['exp']   = $now + $ttl;
		$claims['iss']   = home_url();

		$segments   = array();
		$segments[] = self::base64url_encode( wp_json_encode( $header ) );
		$segments[] = self::base64url_encode( wp_json_encode( $claims ) );

		$signing_input = implode( '.', $segments );
		$signature     = hash_hmac( 'sha256', $signing_input, self::get_secret(), true );
		$segments[]    = self::base64url_encode( $signature );

		return implode( '.', $segments );
	}

	/**
	 * @param string $token
	 * @return array|WP_Error Decoded claims on success.
	 */
	public static function verify( $token ) {
		if ( empty( $token ) || substr_count( $token, '.' ) !== 2 ) {
			return new WP_Error( 'jg_invalid_token', 'Token con formato inválido.', array( 'status' => 401 ) );
		}

		list( $header_b64, $payload_b64, $signature_b64 ) = explode( '.', $token );

		$signing_input      = $header_b64 . '.' . $payload_b64;
		$expected_signature = hash_hmac( 'sha256', $signing_input, self::get_secret(), true );
		$given_signature     = self::base64url_decode( $signature_b64 );

		if ( ! hash_equals( $expected_signature, $given_signature ) ) {
			return new WP_Error( 'jg_invalid_signature', 'Firma del token inválida.', array( 'status' => 401 ) );
		}

		$claims = json_decode( self::base64url_decode( $payload_b64 ), true );

		if ( ! is_array( $claims ) || empty( $claims['exp'] ) || empty( $claims['sub'] ) ) {
			return new WP_Error( 'jg_malformed_token', 'Token mal formado.', array( 'status' => 401 ) );
		}

		if ( time() > (int) $claims['exp'] ) {
			return new WP_Error( 'jg_expired_token', 'El token expiró, inicia sesión nuevamente.', array( 'status' => 401 ) );
		}

		return $claims;
	}
}
