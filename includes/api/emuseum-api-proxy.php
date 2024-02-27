<?php
/**
 * Proxy requests to the eMuseum API, because of CORS.
 *
 * This may not be necessary when we get a real eMuseum instance - but CORS is blocked
 * for the eMuseum demo site, so we need this to get around it.
 *
 * @package mocp-emuseum-integration
 */

namespace MoCP\EMuseum_Integration;

/**
 * Proxy the call to the eMuseum API.
 *
 * @param \WP_REST_Request $data Data from REST request.
 *
 * @return \WP_REST_Response|\WP_Error Fetched JSON object from DeviantArt
 */
function proxy_emuseum_api( $data ) {
	$response = wp_remote_get(
		'https://demo.emuseum.com/' . $data->get_param( 'path' ),
	);

	if ( empty( $response ) || 200 !== $response['response']['code'] ) {
		return new \WP_Error(
			'error',
			'Error from eMuseum response',
			[
				'input'    => $data,
				'response' => $response,
			]
		);
	}

	return new \WP_REST_Response(
		json_decode( $response['body'] )
	);
}

/**
 * Register the proxy route.
 */
function register_route() {
	register_rest_route(
		'mocp/v1',
		'/emuseum-proxy/',
		[
			'methods'             => 'GET',
			'callback'            => __NAMESPACE__ . '\proxy_emuseum_api',
			'permission_callback' => '__return_true',
		]
	);
}
add_action( 'rest_api_init', __NAMESPACE__ . '\register_route' );
