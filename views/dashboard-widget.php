<?php
defined('ABSPATH') || exit;
?>

<p>
    <?php
    // Translators: This is the total number of notifications.
    printf( esc_html__( 'Total Notifications: %d', 'restockx' ), absint( $total_notifications ) );
    ?>
</p>

<p>
    <?php
    // Translators: This is the total number of unique products.
    printf( esc_html__( 'Unique Products: %d', 'restockx' ), absint( $unique_products ) );
    ?>
</p>

<p>
    <?php
    // Translators: This is the total number of unique subscribers.
    printf( esc_html__( 'Unique Subscribers: %d', 'restockx' ), absint( $unique_emails ) );
    ?>
</p>
