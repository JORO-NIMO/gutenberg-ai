<?php
/**
 * Plugin Name:       {{PLUGIN_NAME}}
 * Description:       An AI-powered Gutenberg block scaffolded by create-wp-ai-block.
 * Version:           1.0.0
 * Author:            create-wp-ai-block
 * Text Domain:       {{BLOCK_SLUG}}
 * License:           GPL-2.0-or-later
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
 * Register admin settings page for configuring AI credentials.
 */
add_action( 'admin_menu', function () {
	add_options_page(
		'{{PLUGIN_NAME}} Settings',
		'{{PLUGIN_NAME}}',
		'manage_options',
		'{{BLOCK_SLUG}}-settings',
		'{{FUNCTION_PREFIX}}_render_settings_page'
	);
} );

add_action( 'admin_init', function () {
	register_setting( '{{FUNCTION_PREFIX}}_settings_group', '{{FUNCTION_PREFIX}}_api_key', array(
		'type'              => 'string',
		'sanitize_callback' => 'sanitize_text_field',
		'default'           => '',
	) );
} );

/**
 * Add a Settings shortcut link on the WordPress Plugins screen.
 */
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( $links ) {
	$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page={{BLOCK_SLUG}}-settings' ) ) . '">' . __( 'Settings', '{{BLOCK_SLUG}}' ) . '</a>';
	array_unshift( $links, $settings_link );
	return $links;
} );

/**
 * Render admin settings page.
 */
function {{FUNCTION_PREFIX}}_render_settings_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$provider = '{{AI_PROVIDER}}';
	$key_option_name = '{{FUNCTION_PREFIX}}_api_key';
	$saved_key = get_option( $key_option_name, '' );

	$constant_name = ( $provider === 'openai' ) ? 'OPENAI_API_KEY' : ( ( $provider === 'gemini' ) ? 'GEMINI_API_KEY' : '' );
	$is_constant_defined = ! empty( $constant_name ) && defined( $constant_name );
	?>
	<div class="wrap">
		<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
		<p><?php echo esc_html( sprintf( __( 'Configure API settings for the %s Gutenberg block.', '{{BLOCK_SLUG}}' ), '{{PLUGIN_NAME}}' ) ); ?></p>

		<?php if ( $provider === 'mock' ) : ?>
			<div class="notice notice-info inline">
				<p><strong><?php _e( 'Mock Mode Active:', '{{BLOCK_SLUG}}' ); ?></strong> <?php _e( 'This block is configured in demo/mock mode. No API key is required.', '{{BLOCK_SLUG}}' ); ?></p>
			</div>
		<?php else : ?>
			<form method="post" action="options.php">
				<?php
				settings_fields( '{{FUNCTION_PREFIX}}_settings_group' );
				do_settings_sections( '{{FUNCTION_PREFIX}}_settings_group' );
				?>
				<table class="form-table" role="presentation">
					<tr>
						<th scope="row">
							<label for="<?php echo esc_attr( $key_option_name ); ?>">
								<?php echo esc_html( strtoupper( $provider ) ); ?> <?php _e( 'API Key', '{{BLOCK_SLUG}}' ); ?>
							</label>
						</th>
						<td>
							<?php if ( $is_constant_defined ) : ?>
								<input type="password" value="••••••••••••••••••••••••" class="regular-text" disabled />
								<p class="description" style="color: #007017;">
									<strong>✓ <?php printf( esc_html__( 'Configured via %s in wp-config.php', '{{BLOCK_SLUG}}' ), esc_html( $constant_name ) ); ?></strong>
								</p>
							<?php else : ?>
								<input
									name="<?php echo esc_attr( $key_option_name ); ?>"
									type="password"
									id="<?php echo esc_attr( $key_option_name ); ?>"
									value="<?php echo esc_attr( $saved_key ); ?>"
									class="regular-text"
									placeholder="<?php echo ( $provider === 'openai' ) ? 'sk-...' : 'AIzaSy...'; ?>"
								/>
								<p class="description">
									<?php printf( esc_html__( 'Enter your %s API key. Alternatively, you can define %s in wp-config.php.', '{{BLOCK_SLUG}}' ), esc_html( ucfirst( $provider ) ), '<code>' . esc_html( $constant_name ) . '</code>' ); ?>
								</p>
							<?php endif; ?>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Save Changes', '{{BLOCK_SLUG}}' ) ); ?>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * Register our custom REST API endpoint for fetching AI generations.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( '{{BLOCK_SLUG}}/v1', '/generate', array(
		'methods'             => 'POST',
		'callback'            => '{{FUNCTION_PREFIX}}_generate_ai_response',
		'permission_callback' => function () {
			// Only allow users who can actually write/edit posts to trigger the AI
			return current_user_can( 'edit_posts' );
		}
	) );
} );

function {{FUNCTION_PREFIX}}_generate_ai_response( WP_REST_Request $request ) {
	$prompt      = sanitize_text_field( $request->get_param( 'prompt' ) );
	$tone        = sanitize_text_field( $request->get_param( 'tone' ) );
	$temperature = floatval( $request->get_param( 'temperature' ) );
	$provider    = '{{AI_PROVIDER}}';

	if ( empty( $prompt ) ) {
		return new WP_Error( 'no_prompt', __( 'Please provide a prompt to generate content.', '{{BLOCK_SLUG}}' ), array( 'status' => 400 ) );
	}

	if ( $temperature <= 0 || $temperature > 2 ) {
		$temperature = 0.7;
	}

	$valid_tones = array( 'professional', 'casual', 'inspiring', 'technical', 'concise' );
	$tone_text = ( ! empty( $tone ) && in_array( $tone, $valid_tones, true ) ) ? ' Tone: ' . ucfirst( $tone ) . '.' : '';

	$system_instruction = "You are a content generation assistant for a WordPress Gutenberg block. Write high-quality, engaging content based on the user's prompt.{$tone_text} CRITICAL INSTRUCTIONS: 1. Faithfully preserve and use all specific names, brand names, product names, titles, and entities mentioned in the prompt without changing or replacing them. 2. Output the content directly using clean HTML markup (such as <p>, <h3>, <h4>, <strong>, <em>, <ul>, <ol>, <li>, <blockquote>) suitable for Gutenberg RichText. 3. Do NOT output raw markdown symbols like **, ##, or markdown code blocks.";

	$is_local_env = function_exists( 'wp_get_environment_type' ) && in_array( wp_get_environment_type(), array( 'local', 'development' ), true );
	$sslverify    = apply_filters( '{{FUNCTION_PREFIX}}_sslverify', ! $is_local_env );

	$response_text = "Sorry, failed to generate content.";

	if ( $provider === 'mock' ) {
		// Mock provider: instant, realistic response without needing API credentials
		$response_text = "<h3>" . esc_html( ucwords( $prompt ) ) . "</h3>\n\n" .
			"<p>This is <strong>AI-generated demo content</strong> created for <em>\"" . esc_html( $prompt ) . "\"</em>" . ( ! empty( $tone ) ? " with a <strong>" . esc_html( $tone ) . "</strong> tone." : "." ) . "</p>\n\n" .
			"<ul>\n" .
			"<li><strong>Fast & Zero-Config:</strong> Generates instant content with full Gutenberg RichText compatibility.</li>\n" .
			"<li><strong>Editable On-Canvas:</strong> Freely edit, style, and rearrange text directly in the block editor.</li>\n" .
			"<li><strong>Production Ready:</strong> Switch anytime to Google Gemini or OpenAI in your block settings.</li>\n" .
			"</ul>\n\n" .
			"<blockquote><p>AI-assisted publishing empowers creators to craft high-impact content faster than ever.</p></blockquote>";

		return rest_ensure_response( $response_text );

	} elseif ( $provider === 'openai' ) {
		$api_key = defined( 'OPENAI_API_KEY' ) ? OPENAI_API_KEY : get_option( '{{FUNCTION_PREFIX}}_api_key', '' );
		if ( empty( $api_key ) ) {
			return new WP_Error(
				'missing_key',
				__( 'Please configure your OpenAI API Key in Settings > {{PLUGIN_NAME}} or define OPENAI_API_KEY in wp-config.php.', '{{BLOCK_SLUG}}' ),
				array( 'status' => 400 )
			);
		}

		$body = array(
			'model'       => 'gpt-4o-mini',
			'messages'    => array(
				array( 'role' => 'system', 'content' => $system_instruction ),
				array( 'role' => 'user', 'content' => $prompt )
			),
			'temperature' => $temperature,
			'max_tokens'  => 1000,
		);

		$request_args = array(
			'headers'   => array(
				'Content-Type'  => 'application/json',
				'Authorization' => 'Bearer ' . $api_key,
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 60,
			'sslverify' => $sslverify,
		);

		$response = wp_remote_post( 'https://api.openai.com/v1/chat/completions', $request_args );

		if ( is_wp_error( $response ) ) {
			return new WP_Error( 'api_error', $response->get_error_message(), array( 'status' => 500 ) );
		}

		$body = json_decode( wp_remote_retrieve_body( $response ), true );
		if ( isset( $body['choices'][0]['message']['content'] ) ) {
			$response_text = $body['choices'][0]['message']['content'];
		} else {
			$err_msg = isset( $body['error']['message'] ) ? $body['error']['message'] : __( 'Invalid API response from OpenAI', '{{BLOCK_SLUG}}' );
			return new WP_Error( 'api_error', $err_msg, array( 'status' => 500 ) );
		}

	} elseif ( $provider === 'gemini' ) {
		$api_key = defined( 'GEMINI_API_KEY' ) ? GEMINI_API_KEY : get_option( '{{FUNCTION_PREFIX}}_api_key', '' );
		if ( empty( $api_key ) ) {
			return new WP_Error(
				'missing_key',
				__( 'Please configure your Gemini API Key in Settings > {{PLUGIN_NAME}} or define GEMINI_API_KEY in wp-config.php.', '{{BLOCK_SLUG}}' ),
				array( 'status' => 400 )
			);
		}

		$body = array(
			'contents' => array(
				array(
					'parts' => array(
						array( 'text' => $system_instruction . "\n\nUser prompt: " . $prompt )
					)
				)
			),
			'generationConfig' => array(
				'temperature'     => $temperature,
				'maxOutputTokens' => 1000,
			)
		);

		$request_args = array(
			'headers'   => array(
				'Content-Type' => 'application/json',
			),
			'body'      => wp_json_encode( $body ),
			'timeout'   => 60,
			'sslverify' => $sslverify,
		);

		// Current Gemini models with automated fallback pool
		$candidate_models = array(
			'gemini-2.0-flash',
			'gemini-1.5-flash',
			'gemini-1.5-flash-8b',
			'gemini-2.5-flash',
		);

		$success    = false;
		$last_error = __( 'Failed to generate AI content.', '{{BLOCK_SLUG}}' );

		foreach ( $candidate_models as $model ) {
			$endpoint_url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $api_key;
			$response     = wp_remote_post( $endpoint_url, $request_args );

			if ( is_wp_error( $response ) ) {
				$last_error = 'API request failed: ' . $response->get_error_message();
				continue;
			}

			$response_code = wp_remote_retrieve_response_code( $response );
			$response_raw  = wp_remote_retrieve_body( $response );
			$body          = json_decode( $response_raw, true );

			if ( $response_code === 200 && isset( $body['candidates'][0]['content']['parts'] ) ) {
				$text_parts = array();
				foreach ( $body['candidates'][0]['content']['parts'] as $part ) {
					if ( ! empty( $part['text'] ) ) {
						$text_parts[] = $part['text'];
					}
				}
				$response_text = implode( "\n\n", $text_parts );
				$success       = true;
				break;
			} else {
				$last_error = isset( $body['error']['message'] ) ? $body['error']['message'] : ( 'HTTP ' . $response_code . ': ' . substr( $response_raw, 0, 200 ) );
				// If error or high demand, proceed to next fallback model
				continue;
			}
		}

		if ( ! $success ) {
			return new WP_Error( 'api_error', $last_error, array( 'status' => 500 ) );
		}

	} else {
		return new WP_Error( 'invalid_provider', __( 'Invalid AI provider configured.', '{{BLOCK_SLUG}}' ), array( 'status' => 500 ) );
	}

	$formatted_html = {{FUNCTION_PREFIX}}_format_markdown_to_html( $response_text );

	return rest_ensure_response( $formatted_html );
}

/**
 * Convert Markdown and raw text into clean, formatted HTML for Gutenberg RichText.
 * Handles headings, bold/italics, bullet/numbered lists, links, blockquotes, and paragraphs.
 */
function {{FUNCTION_PREFIX}}_format_markdown_to_html( $text ) {
	if ( empty( $text ) ) {
		return '';
	}

	// 1. Strip wrapping markdown code blocks if any (e.g. ```html ... ```)
	$text = preg_replace( '/^```(?:html)?\s*(.*?)\s*```$/s', '$1', trim( $text ) );

	// 2. Convert Markdown Headings
	$text = preg_replace( '/^######\s+(.*?)$/m', '<h6>$1</h6>', $text );
	$text = preg_replace( '/^#####\s+(.*?)$/m', '<h5>$1</h5>', $text );
	$text = preg_replace( '/^####\s+(.*?)$/m', '<h4>$1</h4>', $text );
	$text = preg_replace( '/^###\s+(.*?)$/m', '<h3>$1</h3>', $text );
	$text = preg_replace( '/^##\s+(.*?)$/m', '<h2>$1</h2>', $text );
	$text = preg_replace( '/^#\s+(.*?)$/m', '<h2>$1</h2>', $text );

	// 3. Convert Markdown Links: [title](url)
	$text = preg_replace( '/\[([^\]]+)\]\((https?:\/\/[^\s\)]+)\)/', '<a href="$2" target="_blank" rel="noopener noreferrer">$1</a>', $text );

	// 4. Convert Bold (**text** or __text__)
	$text = preg_replace( '/\*\*(.*?)\*\*/s', '<strong>$1</strong>', $text );
	$text = preg_replace( '/__(.*?)__/s', '<strong>$1</strong>', $text );

	// 5. Line-by-Line processing for lists, blockquotes, and paragraphs
	$lines = explode( "\n", $text );
	$output = array();
	$in_ul = false;
	$in_ol = false;
	$in_blockquote = false;

	foreach ( $lines as $line ) {
		$trimmed = trim( $line );

		// Check Unordered List (* item or - item)
		if ( preg_match( '/^[\*\-]\s+(.*)$/', $trimmed, $matches ) ) {
			if ( $in_ol ) {
				$output[] = '</ol>';
				$in_ol = false;
			}
			if ( $in_blockquote ) {
				$output[] = '</blockquote>';
				$in_blockquote = false;
			}
			if ( ! $in_ul ) {
				$output[] = '<ul>';
				$in_ul = true;
			}
			$item = preg_replace( '/(?<!\*)\*([^\*\n]+)\*(?!\*)/', '<em>$1</em>', $matches[1] );
			$item = preg_replace( '/(?<!_)_([^_\n]+)_(?!_)/', '<em>$1</em>', $item );
			$output[] = '<li>' . $item . '</li>';
			continue;
		}

		// Check Ordered List (1. item, 2. item)
		if ( preg_match( '/^\d+\.\s+(.*)$/', $trimmed, $matches ) ) {
			if ( $in_ul ) {
				$output[] = '</ul>';
				$in_ul = false;
			}
			if ( $in_blockquote ) {
				$output[] = '</blockquote>';
				$in_blockquote = false;
			}
			if ( ! $in_ol ) {
				$output[] = '<ol>';
				$in_ol = true;
			}
			$item = preg_replace( '/(?<!\*)\*([^\*\n]+)\*(?!\*)/', '<em>$1</em>', $matches[1] );
			$item = preg_replace( '/(?<!_)_([^_\n]+)_(?!_)/', '<em>$1</em>', $item );
			$output[] = '<li>' . $item . '</li>';
			continue;
		}

		// Close lists if we are no longer in a list item
		if ( $in_ul ) {
			$output[] = '</ul>';
			$in_ul = false;
		}
		if ( $in_ol ) {
			$output[] = '</ol>';
			$in_ol = false;
		}

		// Check Blockquote (> quote)
		if ( preg_match( '/^>\s*(.*)$/', $trimmed, $matches ) ) {
			if ( ! $in_blockquote ) {
				$output[] = '<blockquote>';
				$in_blockquote = true;
			}
			$quote_line = preg_replace( '/(?<!\*)\*([^\*\n]+)\*(?!\*)/', '<em>$1</em>', $matches[1] );
			$quote_line = preg_replace( '/(?<!_)_([^_\n]+)_(?!_)/', '<em>$1</em>', $quote_line );
			$output[] = '<p>' . $quote_line . '</p>';
			continue;
		}

		if ( $in_blockquote ) {
			$output[] = '</blockquote>';
			$in_blockquote = false;
		}

		if ( ! empty( $trimmed ) ) {
			// Convert inline italics
			$trimmed = preg_replace( '/(?<!\*)\*([^\*\n]+)\*(?!\*)/', '<em>$1</em>', $trimmed );
			$trimmed = preg_replace( '/(?<!_)_([^_\n]+)_(?!_)/', '<em>$1</em>', $trimmed );

			// If already wrapped in HTML block tags, keep as is
			if ( preg_match( '/^<\/?(h[1-6]|ul|ol|li|p|blockquote|div|section|article)/i', $trimmed ) ) {
				$output[] = $trimmed;
			} else {
				$output[] = '<p>' . $trimmed . '</p>';
			}
		}
	}

	if ( $in_ul ) {
		$output[] = '</ul>';
	}
	if ( $in_ol ) {
		$output[] = '</ol>';
	}
	if ( $in_blockquote ) {
		$output[] = '</blockquote>';
	}

	return implode( "\n", $output );
}
