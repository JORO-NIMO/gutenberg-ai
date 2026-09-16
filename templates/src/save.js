import { useBlockProps, RichText } from '@wordpress/block-editor';

import './style.scss';

export default function save( { attributes } ) {
	const { response } = attributes;

	return (
		<div { ...useBlockProps.save() }>
			{ response && (
				<RichText.Content
					tagName="div"
					value={ response }
				/>
			) }
		</div>
	);
}
