<?php
/**
 * Plugin Name: Newspack Hechinger Taxonomies
 * Description: Custom taxonomies for backwards-compatibility with Hechinger Report.
 * Version: 1.0.0
 * Author: Automattic
 * Author URI: https://newspack.blog/
 * License: GPL2
 * Text Domain: newspack-hechinger-taxonomies
 * Domain Path: /languages/
 */

defined( 'ABSPATH' ) || exit;

/**
 * Manages the whole show.
 */
class Newspack_Hechinger_Taxonomies {

	/**
	 * Initialize everything.
	 */
	public static function init() {
		add_action( 'init', [ __CLASS__, 'register_taxonomies' ] );

		add_action( 'partner_add_form_fields', [ __CLASS__, 'add_partner_meta_fields' ] );
		add_action( 'partner_edit_form_fields', [ __CLASS__, 'edit_partner_meta_fields' ] );
		add_action( 'edited_partner', [ __CLASS__, 'save_partner_meta_fields' ] );
		add_action( 'create_partner', [ __CLASS__, 'save_partner_meta_fields' ] );
	}

	public static function register_taxonomies() {
		register_taxonomy(
			'special-report',
			'post',
			array(
				'hierarchical' => true,
				'labels' => array(
					'name'              => _x( 'Special Reports', 'taxonomy general name' ),
					'singular_name'     => _x( 'Special Report', 'taxonomy singular name' ),
					'search_items'      => __( 'Search Special Reports' ),
					'all_items'         => __( 'All Special Reports' ),
					'parent_item'       => __( 'Parent Special Report' ),
					'parent_item_colon' => __( 'Parent Special Report:' ),
					'edit_item'         => __( 'Edit Special Report' ),
					'view_item'         => __( 'View Special Report' ),
					'update_item'       => __( 'Update Special Report' ),
					'add_new_item'      => __( 'Add New Special Report' ),
					'new_item_name'     => __( 'New Special Report Name' ),
					'menu_name'         => __( 'Special Reports' ),
				),
				'public'            => true,
				'show_admin_column' => true,
				'show_in_nav_menus' => true,
				'query_var'         => true,
				'rewrite'           => [ 'slug' => 'special-reports' ],
				'show_in_rest'      => true,
			)
		);
		register_taxonomy(
			'partner',
			'post',
			array(
				'hierarchical' => true,
				'labels' => array(
					'name'              => _x( 'Partners', 'taxonomy general name' ),
					'singular_name'     => _x( 'Partner', 'taxonomy singular name' ),
					'search_items'      => __( 'Search Partners' ),
					'all_items'         => __( 'All Partners' ),
					'parent_item'       => __( 'Parent Partner' ),
					'parent_item_colon' => __( 'Parent Partner:' ),
					'edit_item'         => __( 'Edit Partner' ),
					'view_item'         => __( 'View Partner' ),
					'update_item'       => __( 'Update Partner' ),
					'add_new_item'      => __( 'Add New Partner' ),
					'new_item_name'     => __( 'New Partner Name' ),
					'menu_name'         => __( 'Partners' ),
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
	 * Add custom meta to the Add New Term screen.
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
	 * Add custom meta to the Edit Term screen.
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
}
Newspack_Hechinger_Taxonomies::init();