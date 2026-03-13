<?php
/**
 * Uninstall MZ Easy Manage
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

// Delete all plugin options
$mzem_options = array(
    'mzem_clone_settings',
    'mzem_protection_settings',
    'mzem_upload_limit',
    'mzem_mime_types',
    'mzem_custom_field_columns',
    'mzem_version',
);

foreach ( $mzem_options as $mzem_option ) {
    delete_option( $mzem_option );
}

// Delete transients
global $wpdb;
if ( is_object( $wpdb ) && isset( $wpdb->options ) ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_transient_mzem_' ) . '%' ) );
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery
    $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s", $wpdb->esc_like( '_transient_timeout_mzem_' ) . '%' ) );
}

// Clear any other plugin data if necessary
// Note: We generally don't delete post meta here unless it's strictly plugin-own data
// and not data the user might want to keep (like ACF fields managed by the plugin).
