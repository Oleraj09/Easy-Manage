<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">📎</span>
            MIME Type Manager
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Allowed File Types</h2>
                <p>Enable or disable file types for WordPress media uploads</p>
            </div>
            <button id="mzem-mime-save" class="mzem-btn mzem-btn-primary mzem-btn-sm">💾 Save Changes</button>
        </div>

        <!-- Category Filters -->
        <div class="mzem-filter-tags">
            <button class="mzem-filter-tag active" data-category="all">All</button>
            <button class="mzem-filter-tag" data-category="images">🖼️ Images</button>
            <button class="mzem-filter-tag" data-category="documents">📄 Documents</button>
            <button class="mzem-filter-tag" data-category="audio">🎵 Audio</button>
            <button class="mzem-filter-tag" data-category="video">🎬 Video</button>
            <button class="mzem-filter-tag" data-category="archives">📦 Archives</button>
            <button class="mzem-filter-tag" data-category="fonts">🔤 Fonts</button>
            <button class="mzem-filter-tag" data-category="3d">🧊 3D / CAD</button>
        </div>

        <!-- Search -->
        <div class="mzem-search-bar">
            <input type="text" id="mzem-mime-search" placeholder="Search file types…">
        </div>

        <!-- Table -->
        <div class="mzem-table-wrap">
            <table class="mzem-table">
                <thead>
                    <tr>
                        <th>Extension</th>
                        <th>Description</th>
                        <th>MIME Type</th>
                        <th>Status</th>
                        <th>Toggle</th>
                    </tr>
                </thead>
                <tbody id="mzem-mime-tbody">
                    <tr>
                        <td colspan="5">
                            <div class="mzem-loading-overlay">
                                <div class="mzem-spinner"></div> Loading file types…
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div style="padding:16px;background:var(--mzem-info-light);border-radius:var(--mzem-radius-sm);border:1px solid rgba(59,130,246,.2);margin-bottom:20px;">
        <p style="margin:0;font-size:13px;color:#1e40af;">
            <strong>ℹ️ SVG Note:</strong> SVG files can contain embedded scripts. Enabling SVG uploads is recommended only if you trust all users who can upload files. Consider using an SVG sanitization plugin for additional security.
        </p>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?>
    </div>
</div>
