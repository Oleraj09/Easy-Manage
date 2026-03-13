<?php
/**
 * Module: Website Protection
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Protection {

    private $settings;

    public function __construct() {
        $this->settings = get_option( 'mzem_protection_settings', array() );

        add_action( 'wp_ajax_mzem_save_protection', array( $this, 'save_settings' ) );
        add_action( 'wp_enqueue_scripts',           array( $this, 'enqueue_frontend' ) );
    }

    /**
     * AJAX: Save protection settings.
     */
    public function save_settings() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $settings = array(
            'enabled'              => ! empty( $_POST['enabled'] ),
            'disable_right_click'  => ! empty( $_POST['disable_right_click'] ),
            'disable_keyboard'     => ! empty( $_POST['disable_keyboard'] ),
            'disable_text_select'  => ! empty( $_POST['disable_text_select'] ),
            'disable_image_drag'   => ! empty( $_POST['disable_image_drag'] ),
            'disable_copy'         => ! empty( $_POST['disable_copy'] ),
            'notification_message' => isset( $_POST['notification_message'] ) ? sanitize_text_field( wp_unslash( $_POST['notification_message'] ) ) : 'Inspection is disabled on this website.',
            'notification_type'    => isset( $_POST['notification_type'] ) && in_array( $_POST['notification_type'], array( 'toast', 'alert' ), true ) ? sanitize_text_field( wp_unslash( $_POST['notification_type'] ) ) : 'toast',
            'skip_admin'           => ! empty( $_POST['skip_admin'] ),
            'post_types'           => isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['post_types'] ) ) : array(),
            'exclude_ids'          => isset( $_POST['exclude_ids'] ) ? sanitize_text_field( wp_unslash( $_POST['exclude_ids'] ) ) : '',
        );

        update_option( 'mzem_protection_settings', $settings );

        wp_send_json_success( array( 'message' => 'Protection settings saved!' ) );
    }

    /**
     * Enqueue frontend protection script.
     */
    public function enqueue_frontend() {
        $settings = get_option( 'mzem_protection_settings', array() );

        // Master switch
        if ( empty( $settings['enabled'] ) ) {
            return;
        }

        // Skip for admins if configured
        if ( ! empty( $settings['skip_admin'] ) && current_user_can( 'manage_options' ) ) {
            return;
        }

        // Check post type scope
        if ( ! empty( $settings['post_types'] ) && is_singular() ) {
            $current_type = get_post_type();
            if ( ! in_array( $current_type, $settings['post_types'], true ) ) {
                return;
            }
        }

        // Check excluded IDs
        if ( ! empty( $settings['exclude_ids'] ) && is_singular() ) {
            $excluded = array_map( 'absint', explode( ',', $settings['exclude_ids'] ) );
            if ( in_array( get_the_ID(), $excluded, true ) ) {
                return;
            }
        }

        wp_enqueue_script(
            'mzem-protection-frontend',
            MZEM_URL . 'assets/js/mzem-protection-frontend.js',
            array(),
            MZEM_VERSION,
            true
        );

        wp_localize_script( 'mzem-protection-frontend', 'mzemProtection', array(
            'disableRightClick' => ! empty( $settings['disable_right_click'] ),
            'disableKeyboard'   => ! empty( $settings['disable_keyboard'] ),
            'disableTextSelect' => ! empty( $settings['disable_text_select'] ),
            'disableImageDrag'  => ! empty( $settings['disable_image_drag'] ),
            'disableCopy'       => ! empty( $settings['disable_copy'] ),
            'message'           => isset( $settings['notification_message'] ) ? esc_js( $settings['notification_message'] ) : 'Inspection is disabled on this website.',
            'type'              => isset( $settings['notification_type'] ) ? $settings['notification_type'] : 'toast',
        ));
    }
}
