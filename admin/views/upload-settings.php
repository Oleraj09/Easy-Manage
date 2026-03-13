<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_current_limit = get_option( 'mzem_upload_limit', 1073741824 );
$mzem_limit_mb = round( $mzem_current_limit / ( 1024 * 1024 ) );
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">📤</span>
            File Upload Settings
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <!-- Server Info -->
    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Current Server Limits</h2>
                <p>These are the PHP configuration values on your server</p>
            </div>
        </div>
        <div class="mzem-info-grid">
            <div class="mzem-info-item">
                <div class="label">Upload Max Filesize</div>
                <div class="value" id="mzem-srv-upload-max">—</div>
            </div>
            <div class="mzem-info-item">
                <div class="label">Post Max Size</div>
                <div class="value" id="mzem-srv-post-max">—</div>
            </div>
            <div class="mzem-info-item">
                <div class="label">Memory Limit</div>
                <div class="value" id="mzem-srv-memory">—</div>
            </div>
            <div class="mzem-info-item">
                <div class="label">Max Execution Time</div>
                <div class="value" id="mzem-srv-exec-time">—</div>
            </div>
            <div class="mzem-info-item">
                <div class="label">WordPress Limit</div>
                <div class="value" id="mzem-srv-wp-limit">—</div>
            </div>
            <div class="mzem-info-item">
                <div class="label">Custom Limit (MZ)</div>
                <div class="value" id="mzem-srv-custom"><?php echo esc_html( size_format( $mzem_current_limit ) ); ?></div>
            </div>
        </div>
    </div>

    <!-- Upload Limit Setting -->
    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Set Upload Size Limit</h2>
                <p>Override the WordPress maximum file upload size</p>
            </div>
        </div>

        <div class="mzem-form-group">
            <label for="mzem-upload-limit">Maximum Upload Size (MB)</label>
            <div style="display:flex;align-items:center;gap:12px;max-width:300px;">
                <input type="number" id="mzem-upload-limit" class="mzem-input" value="<?php echo esc_attr( $mzem_limit_mb ); ?>" min="1" max="10240" step="1">
                <span style="font-size:14px;color:var(--mzem-text-secondary);white-space:nowrap;">MB</span>
            </div>
            <span class="mzem-desc">Default: 1024 MB (1 GB). This overrides the WordPress upload_size_limit filter.</span>
        </div>

        <div style="padding:16px;background:var(--mzem-warning-light);border-radius:var(--mzem-radius-sm);border:1px solid rgba(245,158,11,.2);margin-bottom:20px;">
            <p style="margin:0;font-size:13px;color:#92400e;">
                <strong>⚠️ Note:</strong> This setting overrides the WordPress limit, but your actual upload size may still be restricted by PHP settings (<code>upload_max_filesize</code>, <code>post_max_size</code>). Contact your hosting provider to increase server-level limits.
            </p>
        </div>

        <div class="mzem-card-header" style="border-bottom:none;padding-bottom:0;">
            <div>
                <h2>Applies To</h2>
            </div>
        </div>
        <div class="mzem-checkbox-grid" style="margin-top:12px;">
            <label><input type="checkbox" checked disabled> WordPress Media Upload</label>
            <label><input type="checkbox" checked disabled> Theme Upload</label>
            <label><input type="checkbox" checked disabled> Plugin Upload</label>
        </div>
    </div>

    <!-- Save -->
    <div class="mzem-save-bar">
        <button id="mzem-upload-save" class="mzem-btn mzem-btn-primary">💾 Save Limit</button>
        <span class="mzem-desc">Applies immediately to all upload types.</span>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?>
    </div>
</div>
