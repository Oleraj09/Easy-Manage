<?php
/**
 * Plugin Name: MZ Easy Manage
 * Plugin URI:  https://github.com/Oleraj09/Easy-Manage
 * Description: All-in-one WordPress management plugin — clone posts, manage custom fields, protect content, control uploads, manage MIME types, and export/import data.
 * Version:     1.0.0
 * Author:      Oleraj Hossin
 * Author URI:  https://olerajhossin.top
 * Contributors: mondoloz
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: mz-easy-manage
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/* ── Constants ──────────────────────────────────────────────── */
define( 'MZEM_VERSION', '1.0.0' );
define( 'MZEM_FILE',    __FILE__ );
define( 'MZEM_PATH',    plugin_dir_path( __FILE__ ) );
define( 'MZEM_URL',     plugin_dir_url( __FILE__ ) );
define( 'MZEM_SLUG',    'mz-easy-manage' );

/* ── Autoloader ─────────────────────────────────────────────── */
spl_autoload_register( function ( $class ) {
    $prefix = 'MZEM_';
    if ( strpos( $class, $prefix ) !== 0 ) {
        return;
    }

    $relative = substr( $class, strlen( $prefix ) );
    $file     = MZEM_PATH . 'includes/class-mzem-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';

    if ( file_exists( $file ) ) {
        require_once $file;
        return;
    }

    // Check modules directory
    $module_file = MZEM_PATH . 'includes/modules/class-mzem-' . strtolower( str_replace( '_', '-', $relative ) ) . '.php';
    if ( file_exists( $module_file ) ) {
        require_once $module_file;
    }
});

/* ── Bootstrap ──────────────────────────────────────────────── */
require_once MZEM_PATH . 'includes/class-mzem-core.php';

function mzem_init() {
    return MZEM_Core::instance();
}
add_action( 'plugins_loaded', 'mzem_init' );

/* ── Activation / Deactivation ──────────────────────────────── */
register_activation_hook( __FILE__, function () {
    // Set default options
    if ( false === get_option( 'mzem_protection_settings' ) ) {
        update_option( 'mzem_protection_settings', array(
            'enabled'              => false,
            'disable_right_click'  => true,
            'disable_keyboard'     => true,
            'disable_text_select'  => false,
            'disable_image_drag'   => true,
            'disable_copy'         => false,
            'notification_message' => 'Inspection is disabled on this website.',
            'notification_type'    => 'toast',
            'skip_admin'           => false,
            'post_types'           => array(),
            'exclude_ids'          => '',
        ));
    }

    if ( false === get_option( 'mzem_upload_limit' ) ) {
        update_option( 'mzem_upload_limit', 1073741824 ); // 1 GB
    }

    if ( false === get_option( 'mzem_mime_types' ) ) {
        update_option( 'mzem_mime_types', array() );
    }

    flush_rewrite_rules();
});

register_deactivation_hook( __FILE__, function () {
    flush_rewrite_rules();
});
