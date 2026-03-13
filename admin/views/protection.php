<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_settings   = get_option( 'mzem_protection_settings', array() );
$mzem_post_types = get_post_types( array( 'public' => true ), 'objects' );

$mzem_defaults = array(
    'enabled'              => false,
    'disable_right_click'  => true,
    'disable_keyboard'     => true,
    'disable_text_select'  => false,
    'disable_image_drag'   => true,
    'disable_copy'         => false,
    'notification_message' => 'Content is Protected By MZ Easy Manage.',
    'notification_type'    => 'toast',
    'skip_admin'           => false,
    'post_types'           => array(),
    'exclude_ids'          => '',
);
$mzem_s = wp_parse_args( $mzem_settings, $mzem_defaults );
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">🛡️</span>
            Content Protection
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <!-- Master Toggle -->
    <div class="mzem-master-toggle">
        <label class="mzem-toggle">
            <input type="checkbox" id="mzem-protection-enabled" <?php checked( $mzem_s['enabled'] ); ?>>
            <span class="mzem-toggle-slider"></span>
        </label>
        <div>
            <span class="mzem-toggle-label">Enable Protection</span>
            <p class="mzem-desc">Master switch. Disabling this turns off all frontend protection globally.</p>
        </div>
    </div>

    <!-- Tabs -->
    <div class="mzem-tabs">
        <button class="mzem-tab active" data-tab="mzem-tab-general">General Settings</button>
        <button class="mzem-tab" data-tab="mzem-tab-advanced">Advanced Settings</button>
    </div>

    <!-- General Settings Tab -->
    <div id="mzem-tab-general" class="mzem-tab-content active">
        <div class="mzem-card">
            <div class="mzem-card-header">
                <div>
                    <h2>Protection Features</h2>
                    <p>Choose which protection features to enable on the frontend.</p>
                </div>
            </div>

            <div class="mzem-checkbox-row">
                <label for="mzem-disable-right-click">Disable Right Click</label>
                <input type="checkbox" id="mzem-disable-right-click" <?php checked( $mzem_s['disable_right_click'] ); ?>>
                <span class="mzem-desc">Block the browser context menu on right-click</span>
            </div>

            <div class="mzem-checkbox-row">
                <label for="mzem-disable-keyboard">Disable Keyboard Shortcuts</label>
                <input type="checkbox" id="mzem-disable-keyboard" <?php checked( $mzem_s['disable_keyboard'] ); ?>>
                <span class="mzem-desc">Block F12, Ctrl+Shift+I/J, Ctrl+U, Ctrl+S, Ctrl+P</span>
            </div>

            <div class="mzem-checkbox-row">
                <label for="mzem-disable-text-select">Disable Text Selection</label>
                <input type="checkbox" id="mzem-disable-text-select" <?php checked( $mzem_s['disable_text_select'] ); ?>>
                <span class="mzem-desc">Prevent users from selecting text on the page</span>
            </div>

            <div class="mzem-checkbox-row">
                <label for="mzem-disable-image-drag">Disable Image Drag</label>
                <input type="checkbox" id="mzem-disable-image-drag" <?php checked( $mzem_s['disable_image_drag'] ); ?>>
                <span class="mzem-desc">Prevent images from being dragged out of the browser</span>
            </div>

            <div class="mzem-checkbox-row">
                <label for="mzem-disable-copy">Disable Copy (Ctrl+C)</label>
                <input type="checkbox" id="mzem-disable-copy" <?php checked( $mzem_s['disable_copy'] ); ?>>
                <span class="mzem-desc">Block Ctrl+C keyboard shortcut for copying</span>
            </div>
        </div>
    </div>

    <!-- Advanced Settings Tab -->
    <div id="mzem-tab-advanced" class="mzem-tab-content">

        <!-- Notification Settings -->
        <div class="mzem-card">
            <div class="mzem-card-header">
                <div>
                    <h2>Notification Settings</h2>
                </div>
            </div>

            <div class="mzem-form-group">
                <label for="mzem-notification-message">Notification Message</label>
                <input type="text" id="mzem-notification-message" class="mzem-input" value="<?php echo esc_attr( $mzem_s['notification_message'] ); ?>" style="max-width:500px;">
                <p class="mzem-desc">Message shown when a protected action is attempted.</p>
            </div>

            <div class="mzem-form-group">
                <label>Notification Type</label>
                <div class="mzem-radio-group">
                    <label>
                        <input type="radio" name="mzem_notification_type" value="toast" <?php checked( $mzem_s['notification_type'], 'toast' ); ?>>
                        <span><strong>Modern Toast Popup</strong> – bottom-right, auto-dismisses after 3s</span>
                    </label>
                    <label>
                        <input type="radio" name="mzem_notification_type" value="alert" <?php checked( $mzem_s['notification_type'], 'alert' ); ?>>
                        <span><strong>Browser Default Alert</strong> – standard browser popup</span>
                    </label>
                </div>
            </div>
        </div>

        <!-- Access Control -->
        <div class="mzem-card">
            <div class="mzem-card-header">
                <div>
                    <h2>Access Control</h2>
                </div>
            </div>

            <div class="mzem-checkbox-row">
                <label for="mzem-skip-admin">Skip Administrators</label>
                <input type="checkbox" id="mzem-skip-admin" <?php checked( $mzem_s['skip_admin'] ); ?>>
                <span class="mzem-desc">Disable protection for logged-in administrators</span>
            </div>
        </div>

        <!-- Scope & Exclusions -->
        <div class="mzem-card">
            <div class="mzem-card-header">
                <div>
                    <h2>Scope &amp; Exclusions</h2>
                </div>
            </div>

            <div class="mzem-form-group">
                <label>Protect Post Types</label>
                <div class="mzem-checkbox-grid">
                    <?php foreach ( $mzem_post_types as $mzem_pt ) :
                        $mzem_checked = ! empty( $mzem_s['post_types'] ) ? in_array( $mzem_pt->name, $mzem_s['post_types'], true ) : false;
                    ?>
                    <label>
                        <input type="checkbox" name="mzem_protect_pt[]" value="<?php echo esc_attr( $mzem_pt->name ); ?>" <?php checked( $mzem_checked ); ?>>
                        <strong><?php echo esc_html( $mzem_pt->labels->singular_name ); ?></strong>
                        <code style="font-size:12px;color:var(--mzem-text-muted)">(<?php echo esc_html( $mzem_pt->name ); ?>)</code>
                    </label>
                    <?php endforeach; ?>
                </div>
                <span class="mzem-desc">If none selected, protection applies to all post types.</span>
            </div>

            <div class="mzem-form-group">
                <label for="mzem-exclude-ids">Exclude Page IDs</label>
                <textarea id="mzem-exclude-ids" class="mzem-textarea" style="min-height:60px;max-width:500px;"><?php echo esc_textarea( $mzem_s['exclude_ids'] ); ?></textarea>
                <p class="mzem-desc">Enter post/page IDs to exclude from protection, separated by commas. Example: 12, 34, 56</p>
            </div>
        </div>
    </div>

    <!-- Save Bar -->
    <div class="mzem-save-bar">
        <button id="mzem-protection-save" class="mzem-btn mzem-btn-primary">💾 Save Settings</button>
        <span class="mzem-desc">Changes take effect immediately on the frontend.</span>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?> — Deterrence-based frontend security.
    </div>
</div>
