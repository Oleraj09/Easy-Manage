<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_all_post_types = get_post_types( array( 'public' => true ), 'objects' );
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">↕️</span>
            Custom Order Manager
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Reorder Posts &amp; Pages</h2>
                <p>Select a post type and drag items to change their display order. This affects the "menu_order" attribute.</p>
            </div>
            <button id="mzem-order-save" class="mzem-btn mzem-btn-primary" disabled>💾 Save New Order</button>
        </div>

        <div class="mzem-form-group" style="max-width: 300px;">
            <label for="mzem-order-post-type">Select Post Type</label>
            <select id="mzem-order-post-type" class="mzem-select">
                <?php foreach ( $mzem_all_post_types as $mzem_pt ) : ?>
                    <option value="<?php echo esc_attr( $mzem_pt->name ); ?>"><?php echo esc_html( $mzem_pt->labels->singular_name ); ?> (<?php echo esc_html( $mzem_pt->name ); ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <div id="mzem-order-list-wrap" style="position: relative; min-height: 200px;">
            <div id="mzem-order-list" class="mzem-order-list">
                <!-- Posts will be loaded here via AJAX -->
                <div style="padding: 40px; text-align: center; color: var(--mzem-text-muted);">
                    Select a post type to start reordering.
                </div>
            </div>
            
            <div id="mzem-order-loader" class="mzem-loading-overlay" style="display: none;">
                <div class="mzem-spinner"></div> Loading posts…
            </div>
        </div>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?>
    </div>
</div>

<!-- SortableJS (will be enqueued properly in class-mzem-core.php, but adding a check here for local preview if needed) -->
