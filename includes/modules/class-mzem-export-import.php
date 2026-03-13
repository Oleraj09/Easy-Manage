<?php
/**
 * Module: Export / Import Tool
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Export_Import {

    public function __construct() {
        add_action( 'wp_ajax_mzem_export',         array( $this, 'handle_export' ) );
        add_action( 'wp_ajax_mzem_import_preview',  array( $this, 'handle_import_preview' ) );
        add_action( 'wp_ajax_mzem_import_run',      array( $this, 'handle_import_run' ) );
    }

    /* ──────────────────────────────────────────────────────────
     * EXPORT
     * ────────────────────────────────────────────────────────── */
    public function handle_export() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $post_types = isset( $_POST['post_types'] ) ? array_map( 'sanitize_text_field', wp_unslash( (array) $_POST['post_types'] ) ) : array( 'post' );
        $format     = isset( $_POST['format'] ) ? sanitize_text_field( wp_unslash( $_POST['format'] ) ) : 'json';

        $data = $this->collect_export_data( $post_types );

        if ( 'xml' === $format ) {
            $output = $this->to_xml( $data );
            $filename = 'mzem-export-' . gmdate( 'Y-m-d-His' ) . '.xml';
            $mime = 'application/xml';
        } else {
            $output = wp_json_encode( $data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE );
            $filename = 'mzem-export-' . gmdate( 'Y-m-d-His' ) . '.json';
            $mime = 'application/json';
        }

        wp_send_json_success( array(
            'message'  => 'Export completed! ' . count( $data['posts'] ) . ' items exported.',
            'content'  => $output,
            'filename' => $filename,
            'mime'     => $mime,
        ));
    }

    /**
     * Collect post data for export.
     */
    private function collect_export_data( $post_types ) {
        $posts_data = array();

        foreach ( $post_types as $post_type ) {
            $posts = get_posts( array(
                'post_type'      => $post_type,
                'posts_per_page' => -1,
                'post_status'    => 'any',
            ));

            foreach ( $posts as $post ) {
                $meta = get_post_meta( $post->ID );

                // Clean up serialized data
                $clean_meta = array();
                foreach ( $meta as $key => $values ) {
                    $clean_meta[ $key ] = array_map( 'maybe_unserialize', $values );
                }

                // Taxonomies
                $taxonomies  = get_object_taxonomies( $post_type );
                $tax_data    = array();
                foreach ( $taxonomies as $tax ) {
                    $terms = wp_get_object_terms( $post->ID, $tax, array( 'fields' => 'all' ) );
                    if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
                        $tax_data[ $tax ] = wp_list_pluck( $terms, 'name' );
                    }
                }

                // Featured image URL
                $thumb_url = get_the_post_thumbnail_url( $post->ID, 'full' );

                $posts_data[] = array(
                    'post_type'       => $post->post_type,
                    'post_title'      => $post->post_title,
                    'post_content'    => $post->post_content,
                    'post_excerpt'    => $post->post_excerpt,
                    'post_status'     => $post->post_status,
                    'post_date'       => $post->post_date,
                    'post_author'     => get_the_author_meta( 'user_login', $post->post_author ),
                    'post_parent'     => $post->post_parent,
                    'menu_order'      => $post->menu_order,
                    'comment_status'  => $post->comment_status,
                    'ping_status'     => $post->ping_status,
                    'meta'            => $clean_meta,
                    'taxonomies'      => $tax_data,
                    'featured_image'  => $thumb_url ? $thumb_url : '',
                );
            }
        }

        return array(
            'version'    => MZEM_VERSION,
            'exported'   => gmdate( 'c' ),
            'site'       => get_bloginfo( 'url' ),
            'post_types' => $post_types,
            'count'      => count( $posts_data ),
            'posts'      => $posts_data,
        );
    }

    /**
     * Convert export data to XML.
     */
    private function to_xml( $data ) {
        $xml = new SimpleXMLElement( '<?xml version="1.0" encoding="UTF-8"?><mzem_export></mzem_export>' );
        $xml->addChild( 'version', esc_xml( $data['version'] ) );
        $xml->addChild( 'exported', esc_xml( $data['exported'] ) );
        $xml->addChild( 'site', esc_xml( $data['site'] ) );
        $xml->addChild( 'count', (string) $data['count'] );

        $items = $xml->addChild( 'posts' );
        foreach ( $data['posts'] as $post ) {
            $item = $items->addChild( 'post' );
            $item->addChild( 'post_type',      esc_xml( $post['post_type'] ) );
            $item->addChild( 'post_title',     esc_xml( $post['post_title'] ) );
            $item->addChild( 'post_status',    esc_xml( $post['post_status'] ) );
            $item->addChild( 'post_date',      esc_xml( $post['post_date'] ) );
            $item->addChild( 'post_author',    esc_xml( $post['post_author'] ) );

            $content = $item->addChild( 'post_content' );
            $content_node = dom_import_simplexml( $content );
            $content_owner = $content_node->ownerDocument;
            $content_node->appendChild( $content_owner->createCDATASection( $post['post_content'] ) );

            $item->addChild( 'post_excerpt', esc_xml( $post['post_excerpt'] ) );
            $item->addChild( 'featured_image', esc_xml( $post['featured_image'] ) );

            // Meta
            $meta_el = $item->addChild( 'meta' );
            foreach ( $post['meta'] as $key => $values ) {
                foreach ( $values as $value ) {
                    $m = $meta_el->addChild( 'meta_item' );
                    $m->addChild( 'key', esc_xml( $key ) );
                    $v_node = $m->addChild( 'value' );
                    $v_dom = dom_import_simplexml( $v_node );
                    $v_owner = $v_dom->ownerDocument;
                    $v_dom->appendChild( $v_owner->createCDATASection( is_array( $value ) ? wp_json_encode( $value ) : (string) $value ) );
                }
            }

            // Taxonomies
            $tax_el = $item->addChild( 'taxonomies' );
            foreach ( $post['taxonomies'] as $tax => $terms ) {
                $t = $tax_el->addChild( 'taxonomy' );
                $t->addAttribute( 'name', $tax );
                foreach ( $terms as $term_name ) {
                    $t->addChild( 'term', esc_xml( $term_name ) );
                }
            }
        }

        return $xml->asXML();
    }

    /* ──────────────────────────────────────────────────────────
     * IMPORT – Preview
     * ────────────────────────────────────────────────────────── */
    public function handle_import_preview() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        if ( empty( $_FILES['import_file'] ) ) {
            wp_send_json_error( 'No file uploaded.' );
        }

        // Sanitize the global reference indirectly by validating its components
        $mzem_file = array(
            'name'     => isset( $_FILES['import_file']['name'] ) ? sanitize_file_name( wp_unslash( $_FILES['import_file']['name'] ) ) : '',
            'type'     => isset( $_FILES['import_file']['type'] ) ? sanitize_mime_type( wp_unslash( $_FILES['import_file']['type'] ) ) : '',
            'tmp_name' => isset( $_FILES['import_file']['tmp_name'] ) ? sanitize_text_field( wp_unslash( $_FILES['import_file']['tmp_name'] ) ) : '',
            'error'    => isset( $_FILES['import_file']['error'] ) ? absint( $_FILES['import_file']['error'] ) : 1,
            'size'     => isset( $_FILES['import_file']['size'] ) ? absint( $_FILES['import_file']['size'] ) : 0,
        );

        // Validate
        $allowed = array( 'json', 'xml' );
        $ext = strtolower( pathinfo( $mzem_file['name'], PATHINFO_EXTENSION ) );

        if ( ! in_array( $ext, $allowed, true ) ) {
            wp_send_json_error( 'Invalid file type. Only JSON and XML are allowed.' );
        }

        $content = file_get_contents( $mzem_file['tmp_name'] ); // phpcs:ignore WordPress.WP.AlternativeFunctions

        if ( 'json' === $ext ) {
            $data = json_decode( $content, true );
            if ( json_last_error() !== JSON_ERROR_NONE ) {
                wp_send_json_error( 'Invalid JSON file.' );
            }
        } else {
            libxml_use_internal_errors( true );
            $xml = simplexml_load_string( $content );
            if ( false === $xml ) {
                wp_send_json_error( 'Invalid XML file.' );
            }
            $data = json_decode( wp_json_encode( $xml ), true );
        }

        // Stash for import
        set_transient( 'mzem_import_data', $data, HOUR_IN_SECONDS );

        $summary = array(
            'count'      => isset( $data['count'] ) ? (int) $data['count'] : 0,
            'post_types' => isset( $data['post_types'] ) ? $data['post_types'] : array(),
            'site'       => isset( $data['site'] ) ? $data['site'] : '',
            'exported'   => isset( $data['exported'] ) ? $data['exported'] : '',
        );

        wp_send_json_success( array(
            'message' => 'File parsed successfully!',
            'summary' => $summary,
        ));
    }

    /* ──────────────────────────────────────────────────────────
     * IMPORT – Run
     * ────────────────────────────────────────────────────────── */
    public function handle_import_run() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $data = get_transient( 'mzem_import_data' );

        if ( ! $data || empty( $data['posts'] ) ) {
            wp_send_json_error( 'No import data found. Please upload a file first.' );
        }

        $imported = 0;
        $errors   = array();

        foreach ( $data['posts'] as $post_data ) {
            $result = $this->import_single_post( $post_data );
            if ( is_wp_error( $result ) ) {
                $errors[] = $result->get_error_message();
            } else {
                $imported++;
            }
        }

        delete_transient( 'mzem_import_data' );

        wp_send_json_success( array(
            'message'  => sprintf( '%d posts imported successfully!', $imported ),
            'imported' => $imported,
            'errors'   => $errors,
        ));
    }

    /**
     * Import a single post with meta and taxonomies.
     */
    private function import_single_post( $post_data ) {
        $author = get_user_by( 'login', isset( $post_data['post_author'] ) ? $post_data['post_author'] : '' );

        $new_post = array(
            'post_title'     => isset( $post_data['post_title'] ) ? sanitize_text_field( $post_data['post_title'] ) : '',
            'post_content'   => isset( $post_data['post_content'] ) ? wp_kses_post( $post_data['post_content'] ) : '',
            'post_excerpt'   => isset( $post_data['post_excerpt'] ) ? sanitize_textarea_field( $post_data['post_excerpt'] ) : '',
            'post_status'    => isset( $post_data['post_status'] ) ? sanitize_text_field( $post_data['post_status'] ) : 'draft',
            'post_type'      => isset( $post_data['post_type'] ) ? sanitize_text_field( $post_data['post_type'] ) : 'post',
            'post_author'    => $author ? $author->ID : get_current_user_id(),
            'post_date'      => isset( $post_data['post_date'] ) ? sanitize_text_field( $post_data['post_date'] ) : current_time( 'mysql' ),
            'menu_order'     => isset( $post_data['menu_order'] ) ? absint( $post_data['menu_order'] ) : 0,
            'comment_status' => isset( $post_data['comment_status'] ) ? sanitize_text_field( $post_data['comment_status'] ) : 'closed',
        );

        $new_id = wp_insert_post( $new_post, true );

        if ( is_wp_error( $new_id ) ) {
            return $new_id;
        }

        // Import meta
        if ( ! empty( $post_data['meta'] ) && is_array( $post_data['meta'] ) ) {
            foreach ( $post_data['meta'] as $key => $values ) {
                if ( in_array( $key, array( '_edit_lock', '_edit_last' ), true ) ) {
                    continue;
                }
                if ( is_array( $values ) ) {
                    foreach ( $values as $value ) {
                        add_post_meta( $new_id, sanitize_text_field( $key ), $value );
                    }
                } else {
                    add_post_meta( $new_id, sanitize_text_field( $key ), $values );
                }
            }
        }

        // Import taxonomies
        if ( ! empty( $post_data['taxonomies'] ) && is_array( $post_data['taxonomies'] ) ) {
            foreach ( $post_data['taxonomies'] as $taxonomy => $terms ) {
                if ( ! taxonomy_exists( $taxonomy ) ) {
                    continue;
                }
                if ( is_array( $terms ) ) {
                    $term_ids = array();
                    foreach ( $terms as $term_name ) {
                        $existing = get_term_by( 'name', $term_name, $taxonomy );
                        if ( $existing ) {
                            $term_ids[] = (int) $existing->term_id;
                        } else {
                            $new_term = wp_insert_term( sanitize_text_field( $term_name ), $taxonomy );
                            if ( ! is_wp_error( $new_term ) ) {
                                $term_ids[] = (int) $new_term['term_id'];
                            }
                        }
                    }
                    wp_set_object_terms( $new_id, $term_ids, $taxonomy );
                }
            }
        }

        return $new_id;
    }
}
