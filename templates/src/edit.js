import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, RichText } from '@wordpress/block-editor';
import { PanelBody, TextareaControl, SelectControl, RangeControl, Button, Spinner, Notice } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';

import './editor.scss';

export default function Edit( { attributes, setAttributes } ) {
	const { prompt, response, status, tone = 'professional', temperature = 0.7 } = attributes;

	const generateAI = async () => {
		if ( ! prompt || ! prompt.trim() ) {
			return;
		}

		setAttributes( { status: 'loading' } );
		try {
			const result = await apiFetch( {
				path: '/{{BLOCK_SLUG}}/v1/generate',
				method: 'POST',
				data: {
					prompt: prompt.trim(),
					tone,
					temperature,
				},
			} );
			
			// result is returned as clean, formatted HTML by our PHP endpoint
			setAttributes( { response: result, status: 'success' } );
		} catch ( error ) {
			console.error( 'Error fetching AI response:', error );
			setAttributes( { 
				response: error?.message || __( 'An error occurred during generation.', '{{BLOCK_SLUG}}' ), 
				status: 'error' 
			} );
		}
	};

	const quickPrompts = [
		'Write a 2-sentence catchy intro about innovation',
		'Summarize 3 core benefits in a bullet list',
		'Write an inspiring paragraph about modern technology'
	];

	return (
		<div { ...useBlockProps() }>
			<InspectorControls>
				<PanelBody title={ __( 'AI Settings', '{{BLOCK_SLUG}}' ) } initialOpen={ true }>
					<TextareaControl
						label={ __( 'Prompt', '{{BLOCK_SLUG}}' ) }
						value={ prompt || '' }
						onChange={ ( val ) => setAttributes( { prompt: val } ) }
						help={ __( 'Enter instructions for the AI to generate content.', '{{BLOCK_SLUG}}' ) }
						rows={ 4 }
					/>

					<SelectControl
						label={ __( 'Content Tone', '{{BLOCK_SLUG}}' ) }
						value={ tone }
						options={ [
							{ label: __( 'Professional', '{{BLOCK_SLUG}}' ), value: 'professional' },
							{ label: __( 'Casual & Friendly', '{{BLOCK_SLUG}}' ), value: 'casual' },
							{ label: __( 'Inspiring & Engaging', '{{BLOCK_SLUG}}' ), value: 'inspiring' },
							{ label: __( 'Technical & Precise', '{{BLOCK_SLUG}}' ), value: 'technical' },
							{ label: __( 'Concise Summary', '{{BLOCK_SLUG}}' ), value: 'concise' },
						] }
						onChange={ ( val ) => setAttributes( { tone: val } ) }
					/>

					<RangeControl
						label={ __( 'Creativity (Temperature)', '{{BLOCK_SLUG}}' ) }
						value={ temperature }
						onChange={ ( val ) => setAttributes( { temperature: val } ) }
						min={ 0.1 }
						max={ 1.5 }
						step={ 0.1 }
						help={ __( 'Lower values produce focused responses; higher values produce more creative output.', '{{BLOCK_SLUG}}' ) }
					/>

					<div style={ { marginTop: '14px' } }>
						<Button 
							variant="primary"
							onClick={ generateAI }
							disabled={ status === 'loading' || !prompt || !prompt.trim() }
						>
							{ status === 'loading' ? __( 'Generating...', '{{BLOCK_SLUG}}' ) : __( '✨ Generate AI Content', '{{BLOCK_SLUG}}' ) }
						</Button>
					</div>
				</PanelBody>
			</InspectorControls>
			
			<div className="ai-block-container">
				<div className="ai-block-header">
					<span className="ai-block-badge">🤖 {{PLUGIN_NAME}}</span>
					<span className="ai-block-status">
						{ status === 'loading' && <span><Spinner /> { __( 'Generating...', '{{BLOCK_SLUG}}' ) }</span> }
						{ status === 'success' && <span className="status-success-tag">{ __( '✓ Generated', '{{BLOCK_SLUG}}' ) }</span> }
						{ status === 'error' && <span className="status-error-tag">{ __( '⚠ Error', '{{BLOCK_SLUG}}' ) }</span> }
					</span>
				</div>

				<div className="ai-block-prompt-section">
					<TextareaControl
						label={ __( 'Enter your AI Prompt:', '{{BLOCK_SLUG}}' ) }
						value={ prompt || '' }
						onChange={ ( val ) => setAttributes( { prompt: val } ) }
						placeholder={ __( 'e.g. Write an engaging overview about...', '{{BLOCK_SLUG}}' ) }
						rows={ 3 }
					/>

					<div className="ai-block-controls-row">
						<Button 
							variant="primary"
							onClick={ generateAI }
							disabled={ status === 'loading' || !prompt || !prompt.trim() }
							className="ai-block-generate-btn"
						>
							{ status === 'loading' ? (
								<>
									<Spinner /> { __( 'Generating...', '{{BLOCK_SLUG}}' ) }
								</>
							) : (
								__( '✨ Generate AI Content', '{{BLOCK_SLUG}}' )
							) }
						</Button>

						{ response && (
							<Button 
								variant="tertiary"
								onClick={ () => setAttributes( { response: '', status: 'idle' } ) }
								disabled={ status === 'loading' }
							>
								{ __( 'Clear', '{{BLOCK_SLUG}}' ) }
							</Button>
						) }
					</div>

					{ !response && status === 'idle' && (
						<div className="ai-block-quick-prompts">
							<span className="quick-prompts-label">{ __( 'Try an example:', '{{BLOCK_SLUG}}' ) }</span>
							{ quickPrompts.map( ( qp, i ) => (
								<button
									key={ i }
									type="button"
									className="ai-block-chip"
									onClick={ () => {
										setAttributes( { prompt: qp } );
									} }
								>
									{ qp }
								</button>
							) ) }
						</div>
					) }
				</div>

				{ status === 'error' && (
					<Notice status="error" isDismissible={ false }>
						{ response }
					</Notice>
				) }

				{ response && status !== 'error' && (
					<div className="ai-block-result-card">
						<div className="result-card-header">
							<strong>{ __( 'Generated Content (Editable):', '{{BLOCK_SLUG}}' ) }</strong>
						</div>
						<RichText
							tagName="div"
							className="ai-block-content-editor"
							value={ response }
							onChange={ ( val ) => setAttributes( { response: val } ) }
							placeholder={ __( 'AI generated content will appear here...', '{{BLOCK_SLUG}}' ) }
						/>
					</div>
				) }
			</div>
		</div>
	);
}
