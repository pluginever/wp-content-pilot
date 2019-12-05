<?php defined('ABSPATH')|| exit(); ?>
<?php $disabled = get_the_ID() ? '' : 'disabled'; ?>
<button type="button" class="button button-link-delete block wpcp-delete-all" data-camp-id="<?php echo get_the_ID(); ?>" data-nonce="<?php echo wp_create_nonce('wpcp_delete_all_posts_' . get_the_ID() ); ?>" <?php echo esc_html( $disabled ); ?>>
<?php _e( 'Delete all posted posts', 'wp-content-pilot' ) ?>
</button>
<span class="spinner"></span>
