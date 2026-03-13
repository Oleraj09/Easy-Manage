<?php
/**
 * Module: Post/Page/CPT Clone
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Clone {

    public function __construct() {
        add_filter( 'post_row_actions', array( $this, 'add_clone_link' ), 10, 2 );
        add_filter( 'page_row_actions', array( $this, 'add_clone_link' ), 10, 2 );
        add_action( 'admin_action_mzem_clone_post', array( $this, 'clone_post' ) );
        add_action( 'wp_ajax_mzem_clone_post_ajax', array( $this, 'clone_post_ajax' ) );
        add_action( 'wp_ajax_mzem_save_clone_settings', array( $this, 'save_settings' ) );
    }

    /**
     * Get enabled post types for cloning.
     */
    private function get_enabled_types() {
        $saved = get_option( 'mzem_clone_post_types', null );
        if ( null === $saved ) {
            // Default: all public post types enabled
            return array( 'post', 'page' );
        }
        return (array) $saved;
    }

    /**
     * Check if cloning is enabled for a given post type.
     */
    private function is_clone_enabled( $post_type ) {
        $enabled = $this->get_enabled_types();
        return in_array( $post_type, $enabled, true );
    }

    /**
     * Add "Clone" link in row actions (only if post type is enabled).
     */
    public function add_clone_link( $actions, $post ) {
        if ( ! current_user_can( 'edit_posts' ) ) {
            return $actions;
        }

        if ( ! $this->is_clone_enabled( $post->post_type ) ) {
            return $actions;
        }

        $url = wp_nonce_url(
            admin_url( 'admin.php?action=mzem_clone_post&post_id=' . $post->ID ),
            'mzem_clone_' . $post->ID,
            'mzem_clone_nonce'
        );
        $actions['mzem_clone'] = sprintf(
            '<a href="%s" title="%s" class="mzem-clone-link" style="color:#4f46e5;font-weight:500;">%s</a>',
            esc_url( $url ),
            esc_attr__( 'Clone this item', 'mz-easy-manage' ),
            esc_html__( '⧉ Clone', 'mz-easy-manage' )
        );

        return $actions;
    }

    /**
     * AJAX: Save clone post type settings.
     */
    public function save_settings() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['post_types'] ) ) : array();

        update_option( 'mzem_clone_post_types', $types );

        wp_send_json_success( array( 'message' => 'Clone settings saved!' ) );
    }

    /**
     * Handle clone via admin action (full page reload).
     */
    public function clone_post() {
        if ( ! isset( $_GET['post_id'] ) || ! isset( $_GET['mzem_clone_nonce'] ) ) {
            wp_die( esc_html__( 'Invalid request.', 'mz-easy-manage' ) );
        }

        $post_id = absint( $_GET['post_id'] );

        if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mzem_clone_nonce'] ) ), 'mzem_clone_' . $post_id ) ) {
            wp_die( esc_html__( 'Security check failed.', 'mz-easy-manage' ) );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_die( esc_html__( 'You do not have permission.', 'mz-easy-manage' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post || ! $this->is_clone_enabled( $post->post_type ) ) {
            wp_die( esc_html__( 'Cloning is not enabled for this post type.', 'mz-easy-manage' ) );
        }

        $new_id = $this->duplicate_post( $post_id );

        if ( is_wp_error( $new_id ) ) {
            wp_die( esc_html( $new_id->get_error_message() ) );
        }

        $redirect = admin_url( 'edit.php?post_type=' . $post->post_type . '&mzem_cloned=1' );
        wp_safe_redirect( $redirect );
        exit;
    }

    /**
     * AJAX clone handler.
     */
    public function clone_post_ajax() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied.' ) );
        }

        $post_id = isset( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : 0;

        if ( ! $post_id ) {
            wp_send_json_error( array( 'message' => 'Invalid post ID.' ) );
        }

        $post = get_post( $post_id );
        if ( ! $post || ! $this->is_clone_enabled( $post->post_type ) ) {
            wp_send_json_error( array( 'message' => 'Cloning is not enabled for this post type.' ) );
        }

        $new_id = $this->duplicate_post( $post_id );

        if ( is_wp_error( $new_id ) ) {
            wp_send_json_error( array( 'message' => $new_id->get_error_message() ) );
        }

        wp_send_json_success( array(
            'message' => 'Post cloned successfully!',
            'new_id'  => $new_id,
        ));
    }

    /**
     * Duplicate a post with all meta, taxonomies, featured image, ACF fields.
     */
    private function duplicate_post( $post_id ) {
        $post = get_post( $post_id );

        if ( ! $post ) {
            return new WP_Error( 'not_found', 'Original post not found.' );
        }

        // Create new post
        $new_post = array(
            'post_title'     => $post->post_title . ' (Clone)',
            'post_content'   => $post->post_content,
            'post_excerpt'   => $post->post_excerpt,
            'post_status'    => 'draft',
            'post_type'      => $post->post_type,
            'post_author'    => get_current_user_id(),
            'post_parent'    => $post->post_parent,
            'menu_order'     => $post->menu_order,
            'comment_status' => $post->comment_status,
            'ping_status'    => $post->ping_status,
            'post_password'  => $post->post_password,
        );

        $new_id = wp_insert_post( $new_post, true );

        if ( is_wp_error( $new_id ) ) {
            return $new_id;
        }

        // Clone all taxonomies
        $taxonomies = get_object_taxonomies( $post->post_type );
        foreach ( $taxonomies as $taxonomy ) {
            $terms = wp_get_object_terms( $post_id, $taxonomy, array( 'fields' => 'ids' ) );
            if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                wp_set_object_terms( $new_id, $terms, $taxonomy );
            }
        }

        // Clone all post meta (includes ACF, custom fields)
        $mzem_all_meta = get_post_custom( $post_id );

        if ( is_array( $mzem_all_meta ) ) {
            foreach ( $mzem_all_meta as $key => $values ) {
                // Skip internal WP keys that shouldn't be duplicated
                if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
                    continue;
                }

                foreach ( $values as $value ) {
                    add_post_meta( $new_id, $key, maybe_unserialize( $value ) );
                }
            }
        }

        return $new_id;
    }
}
