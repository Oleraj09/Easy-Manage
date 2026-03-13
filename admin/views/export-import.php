<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_all_post_types = get_post_types( array( 'public' => true ), 'objects' );
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">🔄</span>
            Export / Import
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <div class="mzem-two-col">

        <!-- Export Panel -->
        <div class="mzem-card">
            <div class="mzem-card-header">
                <div>
                    <h2>📦 Export</h2>
                    <p>Export posts, pages, and CPTs with all metadata</p>
                </div>
            </div>

            <div class="mzem-form-group">
                <label>Select Post Types</label>
                <div class="mzem-checkbox-grid">
                    <?php foreach ( $mzem_all_post_types as $mzem_pt ) : ?>
                    <label>
                        <input type="checkbox" name="mzem_export_pt[]" value="<?php echo esc_attr( $mzem_pt->name ); ?>" checked>
                        <?php echo esc_html( $mzem_pt->labels->singular_name ); ?>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mzem-form-group">
                <label>Export Format</label>
                <div class="mzem-radio-group">
                    <label>
                        <input type="radio" name="mzem_export_format" value="json" checked>
                        <span><strong>JSON</strong> (recommended for backup and restore)</span>
                    </label>
                    <label>
                        <input type="radio" name="mzem_export_format" value="xml">
                        <span><strong>XML</strong> (classic WordPress export format)</span>
                    </label>
                </div>
            </div>

            <button id="mzem-export-btn" class="mzem-btn mzem-btn-primary" style="width:100%;justify-content:center;">📦 Export</button>
        </div>

        <!-- Import Panel -->
        <div class="mzem-card">
            <div class="mzem-card-header">
                <div>
                    <h2>📥 Import</h2>
                    <p>Restore posts, pages, and metadata from an export file</p>
                </div>
            </div>

            <!-- Progress Steps -->
            <div class="mzem-steps">
                <div class="mzem-step active" data-step="1">
                    <span class="mzem-step-number">1</span>
                    <span>Upload</span>
                </div>
                <div class="mzem-step-line" data-step="1"></div>
                <div class="mzem-step" data-step="2">
                    <span class="mzem-step-number">2</span>
                    <span>Preview</span>
                </div>
                <div class="mzem-step-line" data-step="2"></div>
                <div class="mzem-step" data-step="3">
                    <span class="mzem-step-number">3</span>
                    <span>Import</span>
                </div>
                <div class="mzem-step-line" data-step="3"></div>
                <div class="mzem-step" data-step="4">
                    <span class="mzem-step-number">4</span>
                    <span>Done</span>
                </div>
            </div>

            <!-- Step 1: Upload -->
            <div class="mzem-import-panel" id="mzem-import-step-1">
                <div id="mzem-import-dropzone" class="mzem-dropzone">
                    <div class="icon">📁</div>
                    <h3>Drop your export file here</h3>
                    <p>or click to browse — Supports .json and .xml</p>
                </div>
                <input type="file" id="mzem-import-file" accept=".json,.xml" style="display:none;">
            </div>

            <!-- Step 2: Preview -->
            <div class="mzem-import-panel" id="mzem-import-step-2" style="display:none;">
                <div style="padding:20px;background:var(--mzem-bg);border-radius:var(--mzem-radius-sm);border:1px solid var(--mzem-border);">
                    <h3 style="margin:0 0 12px;font-size:16px;">Import Preview</h3>
                    <div id="mzem-import-preview-info"></div>
                </div>
                <button id="mzem-import-run-btn" class="mzem-btn mzem-btn-success" style="width:100%;justify-content:center;margin-top:16px;">🚀 Start Import</button>
            </div>

            <!-- Step 3: Importing -->
            <div class="mzem-import-panel" id="mzem-import-step-3" style="display:none;">
                <div class="mzem-loading-overlay">
                    <div class="mzem-spinner"></div> Importing data…
                </div>
            </div>

            <!-- Step 4: Done -->
            <div class="mzem-import-panel" id="mzem-import-step-4" style="display:none;">
                <div id="mzem-import-result"></div>
            </div>
        </div>

    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?>
    </div>
</div>
