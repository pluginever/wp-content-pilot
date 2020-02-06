<?php
echo sprintf( '<p>%s</p>', __('Delete all posts by this article', 'wp-content-pilot'));
echo sprintf( '<a href="#" id="wpcp-delete-campaign-posts" class="button button-secondary" data-campid="%d" data-nonce="%s">Delete</a>',  $campaign_id, wp_create_nonce('wpcp_delete_posts'));
