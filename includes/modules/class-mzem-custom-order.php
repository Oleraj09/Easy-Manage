<?php
/**
 * Custom Order Module for MZ Easy Manage.
 * Allows drag-and-drop reordering of posts/pages/CPTs.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Custom_Order {

    public function __construct() {
        add_action( 'wp_ajax_mzem_get_posts_for_order', array( $this, 'ajax_get_posts' ) );
        add_action( 'wp_ajax_mzem_save_post_order',     array( $this, 'ajax_save_order' ) );
    }

    /**
     * Fetch posts for ordering.
     */
    public function ajax_get_posts() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $post_type = isset( $_POST['post_type'] ) ? sanitize_text_field( wp_unslash( $_POST['post_type'] ) ) : 'post';

        $args = array(
            'post_type'      => $post_type,
            'posts_per_page' => -1,
            'orderby'        => 'menu_order',
            'order'          => 'ASC',
            'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
        );

        $query = new WP_Query( $args );
        $posts = array();

        if ( $query->have_posts() ) {
            while ( $query->have_posts() ) {
                $query->the_post();
                $posts[] = array(
                    'id'    => get_the_ID(),
                    'title' => get_the_title(),
                );
            }
            wp_reset_postdata();
        }

        wp_send_json_success( array( 'posts' => $posts ) );
    }

    /**
     * Save the new order.
     */
    public function ajax_save_order() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( array( 'message' => 'Unauthorized' ) );
        }

        $order = isset( $_POST['order'] ) ? array_map( 'absint', wp_unslash( (array) $_POST['order'] ) ) : array();

        if ( empty( $order ) ) {
            wp_send_json_error( array( 'message' => 'No order data received.' ) );
        }

        foreach ( $order as $index => $post_id ) {
            wp_update_post( array(
                'ID'         => $post_id,
                'menu_order' => $index,
            ) );
        }

        wp_send_json_success( array( 'message' => 'Order updated successfully!' ) );
    }
}
