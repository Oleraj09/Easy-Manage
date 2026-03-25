<?php
/**
 * Module: Custom Field Viewer
 *
 * Lets admins pick custom fields per post type.
 * Selected fields are injected as columns in the native
 * WordPress post-list table (edit.php).
 */

if (!defined('ABSPATH')) {
    exit;
}

class MZEM_Custom_Fields
{

    public function __construct()
    {
        add_action('wp_ajax_mzem_get_meta_keys', array($this, 'ajax_get_meta_keys'));
        add_action('wp_ajax_mzem_get_posts_data', array($this, 'ajax_get_posts_data'));
        add_action('wp_ajax_mzem_save_cf_columns', array($this, 'ajax_save_cf_columns'));
        add_action('wp_ajax_mzem_get_cf_columns', array($this, 'ajax_get_cf_columns'));

        // Register columns on WordPress post list tables
        $this->register_column_hooks();
    }

    /* ──────────────────────────────────────────────
     *  Register WP column hooks for every saved post type
     * ────────────────────────────────────────────── */
    private function register_column_hooks()
    {
        $all_settings = get_option('mzem_cf_columns', array());

        if (empty($all_settings) || !is_array($all_settings)) {
            return;
        }

        foreach ($all_settings as $post_type => $fields) {
            if (empty($fields)) {
                continue;
            }

            // Add column headers — insert BEFORE the Tags column
            add_filter("manage_{$post_type}_posts_columns", function ($columns) use ($fields) {
                $new_columns = array();
                $inserted = false;

                // Find the best insertion point: before 'tags' or 'taxonomy-post_tag' or 'date'
                $insert_before = array('tags', 'taxonomy-post_tag', 'date');

                foreach ($columns as $key => $label) {
                    if (!$inserted && in_array($key, $insert_before, true)) {
                        // Insert custom field columns here
                        foreach ($fields as $field) {
                            $new_columns['mzem_cf_' . $field] = $this->format_field_label($field);
                        }
                        $inserted = true;
                    }
                    $new_columns[$key] = $label;
                }

                // If we never found the insertion point, append at the end
                if (!$inserted) {
                    foreach ($fields as $field) {
                        $new_columns['mzem_cf_' . $field] = $this->format_field_label($field);
                    }
                }

                return $new_columns;
            });

            // Render column values
            add_action("manage_{$post_type}_posts_custom_column", function ($column, $post_id) use ($fields) {
                foreach ($fields as $field) {
                    if ($column !== 'mzem_cf_' . $field) {
                        continue;
                    }

                    $raw = get_post_meta($post_id, $field, true);

                    if ('' === $raw || false === $raw) {
                        echo '&mdash;';
                        continue;
                    }

                    // PHP array → comma-separated or JSON
                    if (is_array($raw)) {
                        $flat = array_filter($raw, 'is_scalar');
                        echo esc_html(
                        count($flat) === count($raw)
                        ? implode(', ', $raw)
                        : wp_json_encode($raw)
                        );
                        continue;
                    }

                    $raw = (string)$raw;

                    // JSON-encoded array string → comma-separated list
                    if (substr($raw, 0, 1) === '[') {
                        $decoded = json_decode($raw, true);
                        if (is_array($decoded)) {
                            echo esc_html(implode(', ', array_map('strval', $decoded)));
                            continue;
                        }
                    }

                    // Compact date YYYYMMDD → YYYY-MM-DD
                    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $raw, $m)) {
                        echo esc_html($m[1] . '-' . $m[2] . '-' . $m[3]);
                        continue;
                    }

                    // Numeric ID – check attachment or term
                    if (ctype_digit($raw) && intval($raw) > 0) {
                        $id = intval($raw);
                        if ('attachment' === get_post_type($id)) {
                            $img = wp_get_attachment_image($id, array(40, 40));
                            if ($img) {
                                echo wp_kses_post($img);
                                continue;
                            }
                        }
                        $term = get_term($id);
                        if (!is_wp_error($term) && null !== $term) {
                            echo esc_html($term->name);
                            continue;
                        }
                    }

                    echo esc_html($raw);
                }
            }, 10, 2);

            // Make columns sortable
            add_filter("manage_edit-{$post_type}_sortable_columns", function ($sortable) use ($fields) {
                foreach ($fields as $field) {
                    $sortable['mzem_cf_' . $field] = 'mzem_cf_' . $field;
                }
                return $sortable;
            });
        }

        // Handle sorting by meta value
        add_action('pre_get_posts', function ($query) {
            if (!is_admin() || !$query->is_main_query()) {
                return;
            }

            $orderby = $query->get('orderby');
            if (is_string($orderby) && strpos($orderby, 'mzem_cf_') === 0) {
                $meta_key = str_replace('mzem_cf_', '', $orderby);
                $query->set('meta_key', $meta_key);
                $query->set('orderby', 'meta_value');
            }
        });
    }

    /* ──────────────────────────────────────────────
     *  AJAX: Save selected columns for a post type
     * ────────────────────────────────────────────── */
    public function ajax_save_cf_columns()
    {
        check_ajax_referer('mzem_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : '';
        $fields = isset($_POST['fields']) ? array_map('sanitize_text_field', wp_unslash((array)$_POST['fields'])) : array();

        if (empty($post_type)) {
            wp_send_json_error('No post type specified.');
        }

        $all_settings = get_option('mzem_cf_columns', array());
        $all_settings[$post_type] = $fields;
        update_option('mzem_cf_columns', $all_settings);

        $edit_url = admin_url('edit.php' . ($post_type !== 'post' ? '?post_type=' . $post_type : ''));

        wp_send_json_success(array(
            'message' => 'Columns saved! Go to the post list to see them.',
            'edit_url' => $edit_url,
        ));
    }

    /* ──────────────────────────────────────────────
     *  AJAX: Get currently saved columns for a post type
     * ────────────────────────────────────────────── */
    public function ajax_get_cf_columns()
    {
        check_ajax_referer('mzem_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';
        $all_settings = get_option('mzem_cf_columns', array());
        $saved = isset($all_settings[$post_type]) ? $all_settings[$post_type] : array();

        wp_send_json_success(array('saved_fields' => $saved));
    }

    /* ──────────────────────────────────────────────
     *  AJAX: Get all meta keys for a post type
     * ────────────────────────────────────────────── */
    public function ajax_get_meta_keys()
    {
        check_ajax_referer('mzem_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';

        global $wpdb;

        $cache_key = 'mzem_meta_keys_' . $post_type;
        $meta_keys = wp_cache_get($cache_key);

        if (false === $meta_keys) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $meta_keys = $wpdb->get_col(
                $wpdb->prepare(
                "SELECT DISTINCT pm.meta_key
                     FROM {$wpdb->postmeta} pm
                     INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                     WHERE p.post_type = %s
                       AND pm.meta_key NOT LIKE %s
                     ORDER BY pm.meta_key ASC",
                $post_type,
                $wpdb->esc_like('_') . '%'
            )
            );
            wp_cache_set($cache_key, $meta_keys, '', 3600);
        }

        // Also include underscore-prefixed keys (ACF etc) but filter internal WP ones
        $internal_keys = array('_edit_lock', '_edit_last', '_wp_old_slug', '_wp_trash_meta_status', '_wp_trash_meta_time', '_wp_desired_post_slug');

        $acf_cache_key = 'mzem_acf_keys_' . $post_type;
        $acf_keys = wp_cache_get($acf_cache_key);

        if (false === $acf_keys) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $acf_keys = $wpdb->get_col(
                $wpdb->prepare(
                "SELECT DISTINCT pm.meta_key
                     FROM {$wpdb->postmeta} pm
                     INNER JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                     WHERE p.post_type = %s
                       AND pm.meta_key LIKE %s
                     ORDER BY pm.meta_key ASC",
                $post_type,
                $wpdb->esc_like('_') . '%'
            )
            );
            wp_cache_set($acf_cache_key, $acf_keys, '', 3600);
        }

        $acf_keys = array_diff($acf_keys, $internal_keys);

        // Return saved columns too
        $all_settings = get_option('mzem_cf_columns', array());
        $saved = isset($all_settings[$post_type]) ? $all_settings[$post_type] : array();

        wp_send_json_success(array(
            'meta_keys' => $meta_keys,
            'acf_keys' => array_values($acf_keys),
            'saved_fields' => $saved,
        ));
    }

    /* ──────────────────────────────────────────────
     *  Helper: Convert a raw meta key to a readable column label
     * ────────────────────────────────────────────── */
    private function format_field_label($field)
    {
        // Strip leading underscores (e.g. _thumbnail_id → thumbnail_id)
        $label = ltrim($field, '_');
        // Replace underscores and hyphens with spaces
        $label = str_replace(array('_', '-'), ' ', $label);
        // Title-case every word
        return ucwords($label);
    }

    /* ──────────────────────────────────────────────
     *  Helper: Resolve a raw meta value to a human-readable string or HTML
     * ────────────────────────────────────────────── */
    private function resolve_meta_value($value, $field_name = '')
    {
        if ('' === $value || false === $value || null === $value) {
            return '';
        }

        // PHP array / object → comma-separated or JSON
        if (is_array($value)) {
            $flat = array_filter($value, 'is_scalar');
            return count($flat) === count($value)
                ? implode(', ', $value)
                : wp_json_encode($value);
        }
        if (is_object($value)) {
            return wp_json_encode($value);
        }

        $value = (string)$value;

        // JSON-encoded array string → comma-separated list
        if (substr($value, 0, 1) === '[') {
            $decoded = json_decode($value, true);
            if (is_array($decoded)) {
                return implode(', ', array_map('strval', $decoded));
            }
        }

        // Compact date YYYYMMDD → YYYY-MM-DD
        if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $value, $m)) {
            return $m[1] . '-' . $m[2] . '-' . $m[3];
        }

        // Numeric ID – check attachment or term
        if (ctype_digit($value) && intval($value) > 0) {
            $id = intval($value);

            // Check if it's an attachment (image)
            if ('attachment' === get_post_type($id)) {
                $img = wp_get_attachment_image($id, array(40, 40));
                if ($img) {
                    return array(
                        'type' => 'image',
                        'html' => $img,
                        'label' => $value,
                    );
                }
            }

            // Check if it's a term ID
            $term = get_term($id);
            if (!is_wp_error($term) && null !== $term) {
                return $term->name;
            }
        }

        return $value;
    }

    /* ──────────────────────────────────────────────
     *  AJAX: Get posts data (for preview table)
     * ────────────────────────────────────────────── */
    public function ajax_get_posts_data()
    {
        check_ajax_referer('mzem_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permission denied.');
        }

        $post_type = isset($_POST['post_type']) ? sanitize_text_field(wp_unslash($_POST['post_type'])) : 'post';
        $fields = isset($_POST['fields']) ? array_map('sanitize_text_field', wp_unslash((array)$_POST['fields'])) : array();
        $search = isset($_POST['search']) ? sanitize_text_field(wp_unslash($_POST['search'])) : '';
        $orderby = isset($_POST['orderby']) ? sanitize_text_field(wp_unslash($_POST['orderby'])) : 'date';
        $order = isset($_POST['order']) ? sanitize_text_field(wp_unslash($_POST['order'])) : 'DESC';
        $paged = isset($_POST['paged']) ? absint($_POST['paged']) : 1;
        $per_page = isset($_POST['per_page']) ? absint($_POST['per_page']) : 20;

        $args = array(
            'post_type' => $post_type,
            'posts_per_page' => $per_page,
            'paged' => $paged,
            'orderby' => $orderby,
            'order' => strtoupper($order) === 'ASC' ? 'ASC' : 'DESC',
            'post_status' => 'any',
        );

        if (!empty($search)) {
            $args['s'] = $search;
        }

        $query = new WP_Query($args);
        $data = array();

        foreach ($query->posts as $post) {
            $row = array(
                'ID' => $post->ID,
                'title' => $post->post_title,
                'author' => get_the_author_meta('display_name', $post->post_author),
                'date' => get_the_date('Y-m-d', $post),
                'status' => $post->post_status,
            );

            foreach ($fields as $field) {
                $raw = get_post_meta($post->ID, $field, true);
                $value = $this->resolve_meta_value($raw, $field);
                $row[$field] = $value;
            }

            $data[] = $row;
        }

        wp_send_json_success(array(
            'posts' => $data,
            'total' => $query->found_posts,
            'pages' => $query->max_num_pages,
            'current' => $paged,
        ));
    }
}
