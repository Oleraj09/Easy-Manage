<?php
if ( ! defined( 'ABSPATH' ) ) exit;

$mzem_post_types = get_post_types( array( '_builtin' => false, 'public' => true ), 'objects' );
?>
<div class="mzem-wrap">

    <!-- Page Header -->
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">📊</span>
            MZ Easy Manage
        </h1>
        <div style="display:flex;align-items:center;gap:12px;">
            <button id="mzem-dark-toggle" class="mzem-btn mzem-btn-secondary mzem-btn-sm" title="Toggle dark mode">🌙</button>
            <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="mzem-stats-grid">
        <div class="mzem-stat-card">
            <div class="mzem-stat-icon purple">📝</div>
            <div class="mzem-stat-info">
                <h3 id="mzem-stat-posts">—</h3>
                <p>Total Posts</p>
            </div>
        </div>
        <div class="mzem-stat-card">
            <div class="mzem-stat-icon blue">📄</div>
            <div class="mzem-stat-info">
                <h3 id="mzem-stat-pages">—</h3>
                <p>Total Pages</p>
            </div>
        </div>
        <div class="mzem-stat-card">
            <div class="mzem-stat-icon green">📁</div>
            <div class="mzem-stat-info">
                <h3 id="mzem-stat-cpt">—</h3>
                <p>Custom Post Types</p>
            </div>
        </div>
        <div class="mzem-stat-card">
            <div class="mzem-stat-icon orange">🔧</div>
            <div class="mzem-stat-info">
                <h3 id="mzem-stat-fields">—</h3>
                <p>Custom Fields</p>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Quick Actions</h2>
                <p>Jump to any feature module</p>
            </div>
        </div>
        <div class="mzem-actions-grid">
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-clone' ) ); ?>" class="mzem-action-card">
                <span class="icon">⧉</span>
                <span class="label">Clone Manager</span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-custom-fields' ) ); ?>" class="mzem-action-card">
                <span class="icon">📋</span>
                <span class="label">Custom Fields</span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-protection' ) ); ?>" class="mzem-action-card">
                <span class="icon">🛡️</span>
                <span class="label">Protection</span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-upload-settings' ) ); ?>" class="mzem-action-card">
                <span class="icon">📤</span>
                <span class="label">File Upload</span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-mime-types' ) ); ?>" class="mzem-action-card">
                <span class="icon">📎</span>
                <span class="label">MIME Types</span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-custom-order' ) ); ?>" class="mzem-action-card">
                <span class="icon">↕️</span>
                <span class="label">Custom Order</span>
            </a>
            <a href="<?php echo esc_url( admin_url( 'admin.php?page=mzem-export-import' ) ); ?>" class="mzem-action-card">
                <span class="icon">🔄</span>
                <span class="label">Export / Import</span>
            </a>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Registered Post Types</h2>
                <p>Custom post types available for cloning and export</p>
            </div>
        </div>
        <?php if ( ! empty( $mzem_post_types ) ) : ?>
        <div class="mzem-table-wrap">
            <table class="mzem-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Slug</th>
                        <th>Public</th>
                        <th>Count</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $mzem_post_types as $mzem_pt ) :
                        $mzem_counts = wp_count_posts( $mzem_pt->name );
                        $mzem_total  = isset( $mzem_counts->publish ) ? (int) $mzem_counts->publish : 0;
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html( $mzem_pt->labels->singular_name ); ?></strong></td>
                        <td><code><?php echo esc_html( $mzem_pt->name ); ?></code></td>
                        <td><span class="mzem-badge mzem-badge-success">Public</span></td>
                        <td><?php echo esc_html( $mzem_total ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else : ?>
            <div class="mzem-empty-state">
                <div class="icon">📂</div>
                <h3>No custom post types found</h3>
                <p>Custom post types registered by themes or plugins will appear here.</p>
            </div>
        <?php endif; ?>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?> — Built for modern WordPress management.
    </div>
</div>
