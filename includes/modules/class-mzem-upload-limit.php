<?php
/**
 * Module: File Upload Limit Control
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Upload_Limit {

    public function __construct() {
        add_filter( 'upload_size_limit', array( $this, 'override_upload_limit' ) );
        add_action( 'wp_ajax_mzem_save_upload_limit', array( $this, 'save_limit' ) );
        add_action( 'wp_ajax_mzem_get_server_limits', array( $this, 'get_server_limits' ) );
    }

    /**
     * Override the WordPress upload size limit.
     */
    public function override_upload_limit( $size ) {
        $custom = get_option( 'mzem_upload_limit', 0 );
        if ( $custom > 0 ) {
            return $custom;
        }
        return $size;
    }

    /**
     * AJAX: Save upload limit.
     */
    public function save_limit() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $limit_mb = isset( $_POST['limit_mb'] ) ? absint( $_POST['limit_mb'] ) : 1024;
        $limit_bytes = $limit_mb * 1024 * 1024;

        update_option( 'mzem_upload_limit', $limit_bytes );

        wp_send_json_success( array(
            'message'    => 'Upload limit updated to ' . $limit_mb . ' MB!',
            'limit_bytes'=> $limit_bytes,
        ));
    }

    /**
     * AJAX: Get current server limits.
     */
    public function get_server_limits() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        wp_send_json_success( array(
            'upload_max_filesize' => ini_get( 'upload_max_filesize' ),
            'post_max_size'       => ini_get( 'post_max_size' ),
            'memory_limit'        => ini_get( 'memory_limit' ),
            'max_execution_time'  => ini_get( 'max_execution_time' ),
            'current_wp_limit'    => size_format( wp_max_upload_size() ),
            'custom_limit'        => size_format( get_option( 'mzem_upload_limit', 0 ) ),
        ));
    }
}
