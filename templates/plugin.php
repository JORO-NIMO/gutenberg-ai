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

	$system_instruction = "You are a content generation assistant for a WordPress Gutenberg block. Write high-quality, engaging content based on the user's prompt. CRITICAL INSTRUCTIONS: 1. Faithfully preserve and use all specific names, brand names, product names, titles, and entities mentioned in the prompt without changing or replacing them. 2. Output the content directly using clean HTML markup (such as <p>, <h3>, <strong>, <em>, <ul>, <li>) suitable for Gutenberg RichText. 3. Do NOT output raw markdown symbols like **, ##, or markdown code blocks.";

	if ( $provider === 'openai' ) {
		$api_key = defined('OPENAI_API_KEY') ? OPENAI_API_KEY : '';
		if ( empty( $api_key ) ) {
			return new WP_Error( 'missing_key', 'Please define your OPENAI_API_KEY in this file or wp-config.php.', array( 'status' => 400 ) );
		}

		$body = array(
			'model' => 'gpt-4o-mini',
			'messages' => array(
				array('role' => 'system', 'content' => $system_instruction),
				array('role' => 'user', 'content' => $prompt)
			),
			'max_tokens' => 800,
		);

		$request_args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 60,
			'sslverify' => false,
		);

		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', $request_args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', $response->get_error_message(), array( 'status' => 500 ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			$response_text = $body['choices'][0]['message']['content'];
		} else {
			return new WP_Error( 'api_error', 'Invalid API response from OpenAI', array( 'status' => 500 ) );
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
						array('text' => $system_instruction . "\n\nUser prompt: " . $prompt)
					)
				)
			)
		);

		$request_args = array(
			'headers' => array(
				'Content-Type'  => 'application/json',
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 60,
			'sslverify' => false,
		);

		// Modern Gemini models with automated fallback to guarantee uptime
		$candidate_models = array(
			'gemini-3.6-flash',
			'gemini-3.5-flash',
			'gemini-3.7-flash',
			'gemini-3.5-flash-lite',
			'gemini-flash-latest'
		);

		$success = false;
		$last_error = 'Failed to generate AI content.';

		foreach ( $candidate_models as $model ) {
			$endpoint_url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;
			$response = wp_remote_post( $endpoint_url, $request_args );

			if ( is_wp_error( $response ) ) {
				$last_error = 'API request failed: ' . $response->get_error_message();
				continue;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_raw  = wp_remote_retrieve_body( $response );
			$body = json_decode( $response_raw, true );

			if ( $response_code === 200 && isset( $body['candidates'][0]['content']['parts'] ) ) {
				$text_parts = array();
				foreach ( $body['candidates'][0]['content']['parts'] as $part ) {
					if ( ! empty( $part['text'] ) ) {
						$text_parts[] = $part['text'];
					}
				}
				$response_text = implode( "\n\n", $text_parts );
				$success = true;
				break;
			} else {
				$last_error = isset( $body['error']['message'] ) ? $body['error']['message'] : ( 'HTTP ' . $response_code . ': ' . substr( $response_raw, 0, 200 ) );
				// If high demand or temporary error, continue to next fallback model
				continue;
			}
		}

		if ( ! $success ) {
			return new WP_Error( 'api_error', $last_error, array( 'status' => 500 ) );
		}

	} else {
		// Invalid provider
		return new WP_Error( 'invalid_provider', 'Invalid AI provider configured.', array( 'status' => 500 ) );
	}

	$formatted_html = {{FUNCTION_PREFIX}}_format_markdown_to_html( $response_text );

	return rest_ensure_response( $formatted_html );
}

/**
 * Convert Markdown and raw text into clean, formatted HTML for Gutenberg RichText.
 * Strips raw markdown syntax like **, ## and creates proper HTML tags.
 */
function {{FUNCTION_PREFIX}}_format_markdown_to_html( $text ) {
	if ( empty( $text ) ) {
		return '';
	}

	// Strip wrapping markdown code blocks if any (e.g. ```html ... ```)
	$text = preg_replace( '/^```(?:html)?\s*(.*?)\s*```$/s', '$1', trim( $text ) );

	// 1. Convert Markdown Headings (### -> <h3>, ## -> <h2>, # -> <h2>)
	$text = preg_replace( '/^###\s+(.*?)$/m', '<h3>$1</h3>', $text );
	$text = preg_replace( '/^##\s+(.*?)$/m', '<h2>$1</h2>', $text );
	$text = preg_replace( '/^#\s+(.*?)$/m', '<h2>$1</h2>', $text );

	// 2. Convert Bold (**text** or __text__)
	$text = preg_replace( '/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text );
	$text = preg_replace( '/__(.*?)__/s', '<strong>$1</strong>', $text );

	// 3. Process Lists and Paragraphs Line-by-Line
	$lines = explode( "\n", $text );
	$output = array();
	$in_list = false;

	foreach ( $lines as $line ) {
		$trimmed = trim( $line );
		// Match bullet lists: * item or - item
		if ( preg_match( '/^[\*\-]\s+(.*)$/', $trimmed, $matches ) ) {
			if ( ! $in_list ) {
				$output[] = '<ul>';
				$in_list = true;
			}
			$item_content = $matches[1];
			// Convert inline italic inside list item: *text*
			$item_content = preg_replace( '/(?<!\*)\*([^\*\n]+)\*(?!\*)/', '<em>$1</em>', $item_content );
			$output[] = '<li>' . $item_content . '</li>';
		} else {
			if ( $in_list ) {
				$output[] = '</ul>';
				$in_list = false;
			}
			if ( ! empty( $trimmed ) ) {
				// Convert inline italic: *text*
				$trimmed = preg_replace( '/(?<!\*)\*([^\*\n]+)\*(?!\*)/', '<em>$1</em>', $trimmed );

				// If it already starts with an HTML block tag, leave as is
				if ( preg_match( '/^<\/?(h[1-6]|ul|ol|li|p|blockquote|div|section|article)/i', $trimmed ) ) {
					$output[] = $trimmed;
				} else {
					$output[] = '<p>' . $trimmed . '</p>';
				}
			}
		}
	}
	if ( $in_list ) {
		$output[] = '</ul>';
	}

	return implode( "\n", $output );
}
