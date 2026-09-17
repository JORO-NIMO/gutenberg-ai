<?php
/**
 * Plugin Name:       {{PLUGIN_NAME}}
 * Description:       An AI-powered Gutenberg block scaffolded by create-wp-ai-block.
 * Version:           1.0.0
 * Author:            create-wp-ai-block
 * Text Domain:       {{BLOCK_SLUG}}
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Register the block and load its compiled assets from the build directory.
 */
function {{FUNCTION_PREFIX}}_block_init() {
	register_block_type( __DIR__ . '/build' );
}
add_action( 'init', '{{FUNCTION_PREFIX}}_block_init' );

/**
 * Register our custom REST API endpoint for fetching AI generations.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( '{{BLOCK_SLUG}}/v1', '/generate', array(
		'methods'  => 'POST',
		'callback' => '{{FUNCTION_PREFIX}}_generate_ai_response',
		'permission_callback' => function () {
			// Only allow users who can actually write/edit posts to trigger the AI
			return current_user_can( 'edit_posts' );
		}
	) );
} );

function {{FUNCTION_PREFIX}}_generate_ai_response( WP_REST_Request $request ) {
	$prompt = sanitize_text_field( $request->get_param( 'prompt' ) );
	$provider = '{{AI_PROVIDER}}'; 
	
	if ( empty( $prompt ) ) {
		return new WP_Error( 'no_prompt', 'Please provide a prompt to generate content.', array( 'status' => 400 ) );
	}

	$response_text = "Sorry, failed to generate content.";

	if ( $provider === 'openai' ) {
		$api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_key', 'Please define your OPENAI_API_KEY in this file or wp-config.php.', array( 'status' => 400 ) );
		}

		$body = array(
			'model' => 'gpt-4o-mini',
			'messages' => array(
				array('role' => 'user', 'content' => $prompt)
			),
			'max_tokens' => 300,
		);

		$request_args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key
			),
			'body'    => wp_json_encode( $body ),
			'timeout' => 30,
		);

		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', $request_args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', $response->get_error_message(), array( 'status' => 500 ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			$response_text = $body['choices'][0]['message']['content'];
		} else {
			return new WP_Error( 'api_error', 'Invalid API response', array( 'status' => 500 ) );
		}

	} elseif ( $provider === 'gemini' ) {
		$api_key = defined('GEMINI_API_KEY') ? GEMINI_API_KEY : '';
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_key', 'Please define your GEMINI_API_KEY in this file or wp-config.php.', array( 'status' => 400 ) );
		}

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array('text' => $prompt)
					)
				)
			)
		);

		$request_args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
			),
			'body'    => wp_json_encode( $body ),
			'timeout' => 30,
		);

		$response = wp_remote_post( 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $api_key, $request_args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', $response->get_error_message(), array( 'status' => 500 ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['candidates'][0]['content']['parts'][0]['text'] ) ) {
			$response_text = $body['candidates'][0]['content']['parts'][0]['text'];
		} else {
			return new WP_Error( 'api_error', 'Invalid API response', array( 'status' => 500 ) );
		}

	} else {
		// Invalid provider
		return new WP_Error( 'invalid_provider', 'Invalid AI provider configured.', array( 'status' => 500 ) );
	}

	return rest_ensure_response( $response_text );
}
