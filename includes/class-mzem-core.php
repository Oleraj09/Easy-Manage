<?php
/**
 * Core orchestrator for MZ Easy Manage.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Core {

    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_modules();
        add_action( 'admin_menu',            array( $this, 'register_menus' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'rest_api_init',         array( $this, 'register_rest_routes' ) );
    }

    /* ── Modules ──────────────────────────────────────────── */
    private function load_modules() {
        require_once MZEM_PATH . 'includes/modules/class-mzem-clone.php';
        require_once MZEM_PATH . 'includes/modules/class-mzem-custom-fields.php';
        require_once MZEM_PATH . 'includes/modules/class-mzem-protection.php';
        require_once MZEM_PATH . 'includes/modules/class-mzem-upload-limit.php';
        require_once MZEM_PATH . 'includes/modules/class-mzem-mime-types.php';
        require_once MZEM_PATH . 'includes/modules/class-mzem-export-import.php';
        require_once MZEM_PATH . 'includes/modules/class-mzem-custom-order.php';

        new MZEM_Clone();
        new MZEM_Custom_Fields();
        new MZEM_Protection();
        new MZEM_Upload_Limit();
        new MZEM_Mime_Types();
        new MZEM_Export_Import();
        new MZEM_Custom_Order();
    }

    /* ── Admin Menus ──────────────────────────────────────── */
    public function register_menus() {

        add_menu_page(
            __( 'MZ Easy Manage', 'mz-easy-manage' ),
            __( 'MZ Easy Manage', 'mz-easy-manage' ),
            'manage_options',
            'mzem-dashboard',
            array( $this, 'render_dashboard' ),
            'dashicons-screenoptions',
            3
        );

        $subpages = array(
            'mzem-dashboard'       => __( 'Dashboard',        'mz-easy-manage' ),
            'mzem-clone'           => __( 'Clone Manager',    'mz-easy-manage' ),
            'mzem-custom-fields'   => __( 'Custom Fields',    'mz-easy-manage' ),
            'mzem-upload-settings' => __( 'File Upload',      'mz-easy-manage' ),
            'mzem-mime-types'      => __( 'MIME Types',        'mz-easy-manage' ),
            'mzem-custom-order'    => __( 'Custom Order',     'mz-easy-manage' ),
            'mzem-protection'      => __( 'Protection',       'mz-easy-manage' ),
            'mzem-export-import'   => __( 'Export / Import',  'mz-easy-manage' ),
            'mzem-settings'        => __( 'Settings',         'mz-easy-manage' ),
        );

        foreach ( $subpages as $slug => $title ) {
            if ( 'mzem-dashboard' === $slug ) {
                // Already registered as parent
                add_submenu_page(
                    'mzem-dashboard',
                    $title,
                    $title,
                    'manage_options',
                    $slug,
                    array( $this, 'render_dashboard' )
                );
                continue;
            }

            add_submenu_page(
                'mzem-dashboard',
                $title,
                $title,
                'manage_options',
                $slug,
                array( $this, 'render_page' )
            );
        }
    }

    /* ── Page Renderers ───────────────────────────────────── */
    public function render_dashboard() {
        require_once MZEM_PATH . 'admin/views/dashboard.php';
    }

    public function render_page() {
        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
        $page = $page ? $page : '';
        
        // Security check for plugin pages
        if ( strpos( $page, 'mzem-' ) !== 0 ) {
            return;
        }

        $map  = array(
            'mzem-clone'           => 'clone.php',
            'mzem-custom-fields'   => 'custom-fields.php',
            'mzem-upload-settings' => 'upload-settings.php',
            'mzem-mime-types'      => 'mime-types.php',
            'mzem-custom-order'    => 'custom-order.php',
            'mzem-protection'      => 'protection.php',
            'mzem-export-import'   => 'export-import.php',
            'mzem-settings'        => 'settings.php',
        );

        if ( isset( $map[ $page ] ) ) {
            $view_file = MZEM_PATH . 'admin/views/' . $map[ $page ];
            if ( file_exists( $view_file ) ) {
                require_once $view_file;
            }
        }
    }

    /* ── Assets ───────────────────────────────────────────── */
    public function enqueue_assets( $hook ) {
        // Only load on our plugin pages
        if ( strpos( $hook, 'mzem' ) === false && strpos( $hook, 'mz-easy-manage' ) === false ) {
            // Still load on post list pages for clone button
            $screen = get_current_screen();
            if ( $screen && 'edit' !== $screen->base ) {
                return;
            }
        }

        wp_enqueue_style(
            'mzem-admin-css',
            MZEM_URL . 'assets/css/mzem-admin.css',
            array(),
            MZEM_VERSION
        );

        wp_enqueue_script(
            'mzem-admin-js',
            MZEM_URL . 'assets/js/mzem-admin.js',
            array( 'jquery' ),
            MZEM_VERSION,
            true
        );

        // SortableJS for Custom Order page
        $page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS );
        if ( 'mzem-custom-order' === $page ) {
            wp_enqueue_script(
                'sortable-js',
                MZEM_URL . 'assets/js/vendor/sortable.min.js',
                array(),
                '1.15.0',
                true
            );
        }

        wp_localize_script( 'mzem-admin-js', 'mzemData', array(
            'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
            'adminUrl' => admin_url(),
            'restUrl'  => rest_url( 'mzem/v1/' ),
            'nonce'    => wp_create_nonce( 'mzem_nonce' ),
            'restNonce'=> wp_create_nonce( 'wp_rest' ),
        ));
    }

    /* ── REST API ─────────────────────────────────────────── */
    public function register_rest_routes() {
        register_rest_route( 'mzem/v1', '/stats', array(
            'methods'             => 'GET',
            'callback'            => array( $this, 'rest_get_stats' ),
            'permission_callback' => function () {
                return current_user_can( 'manage_options' );
            },
        ));
    }

    public function rest_get_stats() {
        $cache_key = 'mzem_stats_data';
        $stats     = wp_cache_get( $cache_key );

        if ( false === $stats ) {
            global $wpdb;

            $posts  = wp_count_posts( 'post' );
            $pages  = wp_count_posts( 'page' );

            // Count CPTs (any non-builtin post types)
            $cpt_count  = 0;
            $post_types = get_post_types( array( '_builtin' => false ), 'objects' );
            foreach ( $post_types as $pt ) {
                $counts     = wp_count_posts( $pt->name );
                $cpt_count += isset( $counts->publish ) ? (int) $counts->publish : 0;
            }

            // Count unique meta keys
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery
            $meta_keys = $wpdb->get_var( 
                $wpdb->prepare( 
                    "SELECT COUNT(DISTINCT meta_key) FROM {$wpdb->postmeta} WHERE meta_key NOT LIKE %s", 
                    $wpdb->esc_like( '_' ) . '%' 
                ) 
            );

            $stats = array(
                'posts'         => isset( $posts->publish ) ? (int) $posts->publish : 0,
                'pages'         => isset( $pages->publish ) ? (int) $pages->publish : 0,
                'cpt'           => $cpt_count,
                'custom_fields' => (int) $meta_keys,
                'post_types'    => array_keys( $post_types ),
            );

            wp_cache_set( $cache_key, $stats, '', 3600 ); // Cache for 1 hour
        }

        return rest_ensure_response( $stats );
    }
}
