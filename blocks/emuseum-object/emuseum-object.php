<?php
/**
 * Registers the block.
 *
 * @package mocp-emuseum-integration
 */

namespace MoCP\EMuseum_Integration;

/**
 * Registers the block using the metadata loaded from the `block.json` file.
 * Behind the scenes, it registers also all assets so they can be enqueued
 * through the block editor in the corresponding context.
 */
function register_emuseum_object() {
	register_block_type(
		__DIR__,
		[
			'render_callback' => __NAMESPACE__ . '\emuseum_object_dynamic_render_callback',
		]
	);
}
add_action( 'init', __NAMESPACE__ . '\register_emuseum_object' );

/**
 * Renders the object data.
 *
 * @param array $block_attributes Associative array of block attributes.
 *
 * @return string Rendered HTML.
 */
function emuseum_object_dynamic_render_callback( $block_attributes ): string {

	// We can't do anything if no object ID was set.
	if ( empty( $block_attributes['objectId'] ) ) {
		return '<div>No object ID has been specified</div>';
	}

	try {
		$request = wp_remote_get(
			'https://demo.emuseum.com/objects/' . $block_attributes['objectId'] . '/json'
		);

		$json = json_decode( $request['body'] );
	} catch ( Exception $ex ) {
		return '<div>There was an error loading the result: check the object ID and try again</div>';
	}

	ob_start();
	?>
		<div class="mocp-emuseum-object-block">
			<?php if ( ! empty( $json->object[0]->primaryMedia->value ) ) : ?>
				<img
					src="<?php echo esc_url( $json->object[0]->primaryMedia->value ?? '' ); ?>"
					alt="<?php echo esc_html( $json->object[0]->description->value ?? '' ); ?>"
					class="mocp-emuseum-object-block__image"
				/>
			<?php else : ?>
				<div class="mocp-emuseum-object-block__missing-image">
					<span class="mocp-emuseum-object-block__missing-image-text">
						<?php esc_html_e( 'Image not available', 'mocp' ); ?>
					</span>
				</div>
			<?php endif; ?>

			<h3 class="mocp-emuseum-object-block__title">
				<?php echo esc_html( $json->object[0]->title->value ?? '' ); ?>
			</h3>
			<p class="mocp-emuseum-object-block__description">
				<?php echo esc_html( $json->object[0]->description->value ?? '' ); ?>
			</p>
		</div>
	<?php
	return ob_get_clean();
}

/**
 * Registers the custom post meta fields.
 */
function register_emuseum_post_meta() {
	$args = [
		'type'         => 'integer',
		'description'  => 'Linked eMuseum object IDs.',
		'single'       => false,
		'show_in_rest' => true,
	];

	register_post_meta( 'post', 'linked_emuseum_object', $args );
	register_post_meta( 'page', 'linked_emuseum_object', $args );
}
add_action( 'init', __NAMESPACE__ . '\register_emuseum_post_meta' );

/**
 * Recursive helper function to check InnerBlocks for the eMuseum blocks.
 *
 * @param array $blocks Array of blocks.
 *
 * @return array<int> Array of eMuseum object IDs.
 */
function find_emuseum_object_ids_from_blocks( array $blocks ) {
	$object_ids = [];

	foreach ( $blocks as $block ) {
		if ( ! empty( $block['innerBlocks'] ) ) {
			$inner_object_ids = find_emuseum_object_ids_from_blocks( $block['innerBlocks'] );

			$object_ids = array_merge( $object_ids, $inner_object_ids );
		} elseif ( 'mocp/emuseum-object' === $block['blockName'] ) {
			if ( ! empty( $block['attrs']['objectId'] ) ) {
				$object_ids[] = absint( $block['attrs']['objectId'] );
			}
		}
	}

	return $object_ids;
}

/**
 * Save associated eMuseum objects embedded in blocks in post meta.
 *
 * @param int      $post_id Post ID.
 * @param \WP_Post $post    Post object.
 * @param bool     $update  Whether this is an existing post being updated.
 */
function set_associated_emuseum_objects( $post_id, $post, $update ) {

	// Only set for specific post types.
	if ( ! in_array( $post->post_type, [ 'post', 'page' ], true ) ) {
		return;
	}

	$blocks = parse_blocks( $post->post_content );

	// Skip if no blocks, or not using the block editor.
	$is_gutenberg_page = ( ! empty( $blocks ) && '' !== $blocks[0]['blockName'] );

	if ( ! $is_gutenberg_page ) {
		return;
	}

	$object_ids = find_emuseum_object_ids_from_blocks( $blocks );

	// Remove any exising object IDs.
	$previous_object_ids = get_post_meta( $post_id, 'linked_emuseum_object', false );
	$previous_object_ids = array_map( 'intval', $previous_object_ids );

	foreach ( $previous_object_ids as $object_id ) {
		if ( empty( $object_id ) ) {
			continue;
		}

		if ( ! in_array( $object_id, $object_ids, true ) ) {
			delete_post_meta( $post_id, 'linked_emuseum_object', $object_id );
		}
	}

	// And set the new ones.
	foreach ( $object_ids as $object_id ) {
		if ( empty( $object_id ) ) {
			continue;
		}

		// Skip if it has already been set.
		if ( in_array( $object_id, $previous_object_ids, true ) ) {
			continue;
		}

		add_post_meta( $post_id, 'linked_emuseum_object', $object_id );
	}
}
add_action( 'save_post', __NAMESPACE__ . '\set_associated_emuseum_objects', 10, 3 );
