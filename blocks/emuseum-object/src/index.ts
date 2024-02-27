import { registerBlockType } from '@wordpress/blocks';

import Edit from './edit';

/**
 * Every block starts by registering a new block type definition.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-registration/
 */
registerBlockType( 'mocp/emuseum-object', {
	edit: Edit,
	save: () => null,
} );
