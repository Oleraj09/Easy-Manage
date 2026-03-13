<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_all_post_types = get_post_types( array( 'public' => true ), 'objects' );
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">📋</span>
            Custom Field Viewer
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Select Custom Field Columns</h2>
                <p>Choose a post type and toggle fields on/off. Saved fields will appear as <strong>columns in the WordPress post list</strong>.</p>
            </div>
            <button id="mzem-cf-save" class="mzem-btn mzem-btn-primary mzem-btn-sm">💾 Save Columns</button>
        </div>

        <!-- Post Type Selector -->
        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:flex-end;margin-bottom:20px;">
            <div class="mzem-form-group" style="margin-bottom:0;flex:0 0 260px;">
                <label for="mzem-cf-post-type">Post Type</label>
                <select id="mzem-cf-post-type" class="mzem-select">
                    <?php foreach ( $mzem_all_post_types as $mzem_pt ) : ?>
                        <option value="<?php echo esc_attr( $mzem_pt->name ); ?>"><?php echo esc_html( $mzem_pt->labels->singular_name ); ?> (<?php echo esc_html( $mzem_pt->name ); ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <a id="mzem-cf-go-to-list" href="<?php echo esc_url( admin_url( 'edit.php' ) ); ?>" class="mzem-btn mzem-btn-secondary mzem-btn-sm" target="_blank">
                📋 View Post List →
            </a>
        </div>

        <!-- Custom Field Toggles (loaded via AJAX) -->
        <div id="mzem-cf-field-toggles" style="margin-bottom:20px;">
            <label style="font-size:14px;font-weight:600;color:var(--mzem-text);display:block;margin-bottom:8px;">
                Available Custom Fields — <span style="color:var(--mzem-primary);">click to select</span>, then save:
            </label>
            <div id="mzem-cf-tags" class="mzem-filter-tags">
                <span style="font-size:13px;color:var(--mzem-text-muted);padding:5px 0;">Loading fields…</span>
            </div>
        </div>

        <!-- Info box -->
        <div style="padding:16px;background:var(--mzem-info-light);border-radius:var(--mzem-radius-sm);border:1px solid rgba(59,130,246,.2);margin-bottom:20px;">
            <p style="margin:0;font-size:13px;color:#1e40af;">
                <strong>ℹ️ How it works:</strong> Select the custom fields you want, click <strong>Save Columns</strong>, then go to the WordPress post list (Posts → All Posts / Pages → All Pages). The selected fields will appear as extra columns in the table.
            </p>
        </div>

        <!-- Preview Table -->
        <div class="mzem-card-header" style="border-bottom:none;padding-top:0;">
            <div>
                <h2>Preview</h2>
                <p>A preview of how the selected columns will look with your data</p>
            </div>
        </div>

        <div class="mzem-search-bar" style="margin-bottom:12px;">
            <input type="text" id="mzem-cf-search" placeholder="Search posts… (press Enter)">
        </div>

        <div id="mzem-cf-table-wrap">
            <div class="mzem-loading-overlay">
                <div class="mzem-spinner"></div> Loading posts…
            </div>
        </div>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?>
    </div>
</div>
