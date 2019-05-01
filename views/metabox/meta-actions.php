<button type="button" class="button button-link-delete block wpcp-delete-all" data-camp-id="<?php echo get_the_ID(); ?>" data-nonce="<?php echo wp_create_nonce('wpcp_delete_all_posts_' . get_the_ID() ); ?>">
<?php _e( 'Delete all posted posts', 'wp-content-pilot' ) ?>
</button>
<span class="spinner"></span>
