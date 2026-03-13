<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_all_post_types    = get_post_types( array( 'public' => true ), 'objects' );
$mzem_enabled_types     = get_option( 'mzem_clone_post_types', array( 'post', 'page' ) );
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">⧉</span>
            Clone Manager
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>How It Works</h2>
                <p>One-click duplication for posts, pages, and custom post types</p>
            </div>
        </div>

        <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px;">
            <div style="padding:24px;background:var(--mzem-primary-light);border-radius:var(--mzem-radius);border:1px solid rgba(79,70,229,.15);">
                <h3 style="margin:0 0 8px;color:var(--mzem-primary);font-size:16px;">1. Go to Posts / Pages</h3>
                <p style="margin:0;font-size:14px;color:var(--mzem-text-secondary);">Navigate to any post, page, or custom post type listing in your WordPress admin.</p>
            </div>
            <div style="padding:24px;background:var(--mzem-success-light);border-radius:var(--mzem-radius);border:1px solid rgba(16,185,129,.15);">
                <h3 style="margin:0 0 8px;color:var(--mzem-success);font-size:16px;">2. Click "Clone"</h3>
                <p style="margin:0;font-size:14px;color:var(--mzem-text-secondary);">Hover over any item and click the <strong>⧉ Clone</strong> link in the row actions.</p>
            </div>
            <div style="padding:24px;background:var(--mzem-info-light);border-radius:var(--mzem-radius);border:1px solid rgba(59,130,246,.15);">
                <h3 style="margin:0 0 8px;color:var(--mzem-info);font-size:16px;">3. Done!</h3>
                <p style="margin:0;font-size:14px;color:var(--mzem-text-secondary);">A draft clone is created with all content, meta, taxonomies, and ACF fields intact.</p>
            </div>
        </div>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>What Gets Cloned</h2>
                <p>Every detail is preserved in the duplicated item</p>
            </div>
        </div>
        <div class="mzem-table-wrap">
            <table class="mzem-table">
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Included</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $mzem_clone_items = array(
                        'Title', 'Content', 'Excerpt', 'Featured Image',
                        'Categories', 'Tags', 'Custom Taxonomies',
                        'Custom Fields', 'ACF Fields', 'Post Meta',
                        'Menu Order', 'Post Parent',
                    );
                    foreach ( $mzem_clone_items as $mzem_item ) :
                    ?>
                    <tr>
                        <td><?php echo esc_html( $mzem_item ); ?></td>
                        <td><span class="mzem-badge mzem-badge-success">✓ Yes</span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Editable Supported Post Types -->
    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Supported Post Types</h2>
                <p>Select which post types should have the Clone action available</p>
            </div>
            <button id="mzem-clone-save" class="mzem-btn mzem-btn-primary mzem-btn-sm">💾 Save</button>
        </div>
        <div class="mzem-checkbox-grid">
            <?php foreach ( $mzem_all_post_types as $mzem_pt ) :
                $mzem_is_checked = in_array( $mzem_pt->name, $mzem_enabled_types, true );
            ?>
            <label style="padding:12px;border:1px solid var(--mzem-border);border-radius:var(--mzem-radius-sm);transition:var(--mzem-transition);">
                <input type="checkbox" name="mzem_clone_pt[]" value="<?php echo esc_attr( $mzem_pt->name ); ?>" <?php checked( $mzem_is_checked ); ?>>
                <strong><?php echo esc_html( $mzem_pt->labels->singular_name ); ?></strong>
                <code style="font-size:12px;color:var(--mzem-text-muted)">(<?php echo esc_html( $mzem_pt->name ); ?>)</code>
            </label>
            <?php endforeach; ?>
        </div>
        <p class="mzem-desc" style="margin-top:12px;">Only checked post types will show the <strong>⧉ Clone</strong> row action in their list table.</p>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?>
    </div>
</div>
