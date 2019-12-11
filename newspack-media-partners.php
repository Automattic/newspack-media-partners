<?php
/**
 * Plugin Name: Newspack Media Partners
 * Description: Add media partners and their logos to posts. Intended for posts published in conjunction with other outlets.
 * Version: 1.0.0
 * Author: Automattic
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-media-partners
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manages the whole show.
 */
class Newspack_Media_Partners {

	/**
	 * Initialize everything.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_taxonomies' ] );

		add_action( 'partner_add_form_fields', [ __CLASS__, 'add_partner_meta_fields' ] );
		add_action( 'partner_edit_form_fields', [ __CLASS__, 'edit_partner_meta_fields' ] );
		add_action( 'edited_partner', [ __CLASS__, 'save_partner_meta_fields' ] );
		add_action( 'create_partner', [ __CLASS__, 'save_partner_meta_fields' ] );
		add_action( 'init', [ __CLASS__, 'add_partners_shortcode' ] );

		add_filter( 'the_content', [ __CLASS__, 'add_content_partner_logo' ] );
	}

	/**
	 * Register Partner taxonomy.
	 */
	public static function register_taxonomies() {
		register_taxonomy(
			'partner',
			'post',
			array(
				'hierarchical' => true,
				'labels' => array(
					'name'              => _x( 'Media Partners', 'taxonomy general name' ),
					'singular_name'     => _x( 'Media Partner', 'taxonomy singular name' ),
					'search_items'      => __( 'Search Media Partners' ),
					'all_items'         => __( 'All Media Partners' ),
					'parent_item'       => __( 'Parent Media Partner' ),
					'parent_item_colon' => __( 'Parent Media Partner:' ),
					'edit_item'         => __( 'Edit Media Partner' ),
					'view_item'         => __( 'View Media Partner' ),
					'update_item'       => __( 'Update Media Partner' ),
					'add_new_item'      => __( 'Add New Media Partner' ),
					'new_item_name'     => __( 'New Media Partner Name' ),
					'menu_name'         => __( 'Media Partners' ),
				),
				'public'            => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'partners' ],
				'show_in_rest'      => true,
			)
		);
	}

	/**
	 * Add custom meta to the Add New Partner screen.
	 */
	public static function add_partner_meta_fields() {
		?>
		<div class="form-field">
			<label for="partner_logo"><?php _e( 'Partner Logo:' ); ?></label>
			<input type="hidden" name="partner_logo" id="partner_logo" value="" />
			<input class="upload_image_button button" name="add_partner_logo" id="add_partner_logo" type="button" value="Select/Upload Image" />
			<img src='' id='partner_logo_preview' style='max-width: 250px; width: 100%; height: auto' />
			<script>
				jQuery( document ).ready( function() {
					jQuery( '#add_partner_logo' ).click( function() {
						wp.media.editor.send.attachment = function( props, attachment ) {
							jQuery( '#partner_logo' ).val( attachment.id );
							jQuery( '#partner_logo_preview' ).attr( 'src', attachment.url );
						}
						wp.media.editor.open( this );
						return false;
					} );
				} );
			</script>
		</div>

		<div class="form-field">
			<label for="partner_logo"><?php _e( 'Partner URL:' ); ?></label>
			<input type="text" name="partner_url" value="" />
		</div>
		<?php
	}

	/**
	 * Add custom meta to the Edit Partner screen.
	 *
	 * @param WP_Term $term Current term object.
	 */
	public static function edit_partner_meta_fields( $term ) {
	 	$logo_id = (int) get_term_meta( $term->term_id, 'logo', true );
	 	$logo = '';
	 	if ( $logo_id ) {
	 		$logo_atts = wp_get_attachment_image_src( $logo_id );
	 		if ( $logo_atts ) {
	 			$logo = $logo_atts[0];
	 		}
	 	}

	 	$partner_url = esc_url( get_term_meta( $term->term_id, 'partner_homepage_url', true ) );

		?>
		<tr class="form-field">
			<th scope="row" valign="top"><label for="add_partner_logo"><?php _e( 'Partner Logo' ); ?></label></th>
			<td>
				<input type="hidden" name="partner_logo" id="partner_logo" value="<?php echo esc_attr( $logo_id ); ?>" />
				<input class="upload_image_button button" name="add_partner_logo" id="add_partner_logo" type="button" value="Select/Upload Image" />
			</td>
		</tr>
		<tr class="form-field">
			<th scope="row" valign="top"></th>
			<td>
				<div class="img-preview">
					<img src='<?php echo esc_attr( $logo ); ?>' id='partner_logo_preview' style='max-width: 250px; width: 100%; height: auto' />
				</div>

				<script>
					jQuery( document ).ready( function() {
						jQuery( '#add_partner_logo' ).click( function() {
							wp.media.editor.send.attachment = function( props, attachment ) {
								jQuery( '#partner_logo' ).val( attachment.id );
								jQuery( '#partner_logo_preview' ).attr( 'src', attachment.url );
							}
							wp.media.editor.open( this );
							return false;
						} );
					} );
				</script>
			</td>
		</tr>

		<tr class="form-field">
			<th scope="row" valign="top"><label for="partner_url"><?php _e( 'Partner URL' ); ?></label></th>
			<td>
				<input type="text" name="partner_url" value="<?php echo esc_attr( $partner_url ); ?>" />
			</td>
		</tr>
		<?php
	}

	/**
	 * Save the meta fields for the Partner taxonomy.
	 *
	 * @param int $term_id Term ID.
	 */
	public static function save_partner_meta_fields( $term_id ) {
		if ( ! current_user_can ( 'edit_posts' ) ) {
			return;
		}

		$partner_logo = filter_input( INPUT_POST, 'partner_logo', FILTER_SANITIZE_NUMBER_INT );
		if ( $partner_logo ) {
			update_term_meta( $term_id, 'logo', (int) $partner_logo );
		}

		$partner_url = filter_input( INPUT_POST, 'partner_url', FILTER_SANITIZE_STRING );
		if ( $partner_url ) {
			update_term_meta( $term_id, 'partner_homepage_url',  esc_url( $partner_url ) );
		}
	}

	/**
	 * Register the 'partners' shortcode.
	 */
	public static function add_partners_shortcode() {
		add_shortcode( 'partners', [ __CLASS__, 'render_partners_shortcode' ] );
	}

	/**
	 * Render the 'partners' shortcode.
	 */
	public static function render_partners_shortcode() {
		$partners = get_terms( [
			'taxonomy' => 'partner',
			'hide_empty' => false,
		] );

		ob_start();

		$num_columns = 3;
		$columns = [ '', '', '' ];
		$current = 0;
		foreach ( $partners as $partner ) {
			$partner_html = '';
			$partner_logo = get_term_meta( $partner->term_id, 'logo', true );
			$partner_url = get_term_meta( $partner->term_id, 'partner_homepage_url', true );

			$partner_html .= '';
			if ( $partner_logo ) {
				$logo_html = '';
		 		$logo_atts = wp_get_attachment_image_src( $partner_logo, 'full' );
		 		if ( $logo_atts ) {
		 			$logo_html = '<figure class="wp-block-image"><img class="aligncenter" src="' . esc_attr( $logo_atts[0] ) . '" /></figure>';
		 		}

		 		if ( $logo_html && $partner_url ) {
		 			$logo_html = '<a href="' . esc_url( $partner_url ) . '">' . $logo_html . '</a>';
		 		}

		 		$partner_html .= $logo_html;
			}

			$partner_name = $partner->name;
			if ( $partner_url ) {
				$partner_name = '<a href="' . esc_url( $partner_url ) . '">' . $partner_name . '</a>';
			}
			$partner_html .= '<p class="has-text-align-center">' . $partner_name . '</p>';
			$partner_html .= '<hr class="wp-block-separator is-style-wide">';

			$columns[$current] .= $partner_html;
			$current += 1;
			$current = $current % $num_columns;
		}
		?>
		<div class="wp-block-columns is-style-borders">
			<?php foreach ( $columns as $column ): ?>
				<div class="wp-block-column">
					<?php echo wp_kses_post( $column ); ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Filter in a partner logo on posts that have partners.
	 *
	 * @param string $content The post content.
	 * @return string Modified $content.
	 */
	public static function add_content_partner_logo( $content ) {
		$id = get_the_ID();
		$partners = get_the_terms( $id, 'partner' );
		if ( ! $partners ) {
			return $content;
		}

		$partner = $partners[0];
		$partner_image_id = get_term_meta( $partner->term_id, 'logo', true );
		$image = '';
		if ( $partner_image_id ) {
			$image = wp_get_attachment_image( $partner_image_id, [ 200, 999 ] );
		}
		ob_start();
		?>
		<div class="wp-block-group alignright">
			<div class="wp-block-group__inner-container">
				<figure class="wp-block-image size-full is-resized">
					<?php echo $image; ?>
					<figcaption><?php echo esc_html( sprintf( __( 'This story also appeared in %s', 'newspack-media-partners' ), $partner->name ) ); ?></figcaption>
				</figure>
			</div>
		</div>

		<?php
		$partner_html = ob_get_clean();

		$content_halves = explode( '</p>', $content, 2 );

		// Just append it to the top if for some reason there are no paragraphs.
		if ( 1 === count( $content_halves ) ) {
			$content = $partner_html . $content;
		} else {
			$content = $content_halves[0] . '</p>' . $partner_html . $content_halves[1];
		}

		return $content;
	}
}
Newspack_Media_Partners::init();