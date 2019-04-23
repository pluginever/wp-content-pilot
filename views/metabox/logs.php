<?php
$post_id = get_the_ID();
$logs = wpcp_get_latest_logs( $post_id );

/* if ( is_array( $logs ) && count( $logs ) ) {
    ?>
    <table class="fixed striped widefat">
        <tr>
            <th><?php _e( 'Created at', 'wp-content-pilot' ); ?></th>
            <th><?php _e( 'Message', 'wp-content-pilot' ); ?></th>
        </tr>
    <?php
        foreach ( $logs as $log ) {
            ?>
            <tr>
                <td><?php echo $log->created_at; ?></td>
                <td><?php echo $log->message; ?></td>
            </tr>
            <?php
        }
    ?>
    </table>
    <?php
} */
if ( is_array( $logs ) && count( $logs ) ) {
    ?>
    <table class="striped widefat">
    <?php
        foreach ( $logs as $log ) {
            ?>
            <tr>
                <td>
                    <strong><?php echo $log->created_at; ?></strong>
                    <p><?php echo $log->message; ?></p>
                </td>
            </tr>
            <?php
        }
    ?>
    </table>
    <?php
}