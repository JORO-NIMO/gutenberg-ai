import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, Button, Spinner } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { prompt, response, status } = attributes;

	const generateAI = async () => {
		setAttributes( { status: 'loading' } );
		try {
			const result = await apiFetch( {
				path: '/{{BLOCK_SLUG}}/v1/generate',
				method: 'POST',
				data: { prompt },
			} );
			
			// result is returned as plain string by our PHP endpoint via rest_ensure_response
			setAttributes( { response: result, status: 'success' } );
		} catch ( error ) {
			console.error( 'Error fetching AI response:', error );
			setAttributes( { 
				response: error?.message || 'An error occurred during generation.', 
				status: 'error' 
			} );
		}
	};

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'AI Settings', '{{BLOCK_SLUG}}' ) }>
					<TextControl
						label={ __( 'Prompt', '{{BLOCK_SLUG}}' ) }
						value={ prompt }
						onChange={ ( val ) => setAttributes( { prompt: val } ) }
						help={ __( 'Enter the prompt for the AI', '{{BLOCK_SLUG}}' ) }
					/>
					<Button 
						isPrimary
						onClick={ generateAI }
						disabled={ status === 'loading' || !prompt }
					>
						{ status === 'loading' ? __( 'Generating...', '{{BLOCK_SLUG}}' ) : __( 'Generate AI Content', '{{BLOCK_SLUG}}' ) }
					</Button>
				</PanelBody>
			</InspectorControls>
			
			<div className="ai-block-editor-view">
				<h4>{ __( '{{PLUGIN_NAME}} Preview', '{{BLOCK_SLUG}}' ) }</h4>
				
				{ status === 'loading' && <Spinner /> }
				
				{ status === 'idle' && !response && (
					<p>{ __( 'Enter a prompt in the sidebar and click generate.', '{{BLOCK_SLUG}}' ) }</p>
				) }
				
				{ response && (
					<div className={`ai-response status-${status}`}>
						<RichText
							tagName="div"
							value={ response }
							onChange={ ( val ) => setAttributes( { response: val } ) }
						/>
					</div>
				) }
			</div>
		</div>
	);
}
