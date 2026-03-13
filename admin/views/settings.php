<?php
if ( ! defined( 'ABSPATH' ) ) exit;
?>
<div class="mzem-wrap">
    <div class="mzem-page-header">
        <h1>
            <span class="mzem-icon">⚙️</span>
            Settings
        </h1>
        <span class="mzem-version">v<?php echo esc_html( MZEM_VERSION ); ?></span>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>General Settings</h2>
                <p>Global configuration for MZ Easy Manage</p>
            </div>
        </div>

        <div class="mzem-form-group">
            <div class="mzem-form-inline">
                <label>Dark Mode</label>
                <label class="mzem-toggle">
                    <input type="checkbox" id="mzem-dark-toggle">
                    <span class="mzem-toggle-slider"></span>
                </label>
            </div>
            <p class="mzem-desc">Enable dark mode for admin pages. This preference is saved in your browser's local storage.</p>
        </div>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Plugin Information</h2>
            </div>
        </div>
        <div class="mzem-table-wrap">
            <table class="mzem-table">
                <tbody>
                    <tr>
                        <td><strong>Plugin Name</strong></td>
                        <td>MZ Easy Manage</td>
                    </tr>
                    <tr>
                        <td><strong>Version</strong></td>
                        <td><?php echo esc_html( MZEM_VERSION ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>PHP Version</strong></td>
                        <td><?php echo esc_html( PHP_VERSION ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>WordPress Version</strong></td>
                        <td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Active Theme</strong></td>
                        <td><?php echo esc_html( wp_get_theme()->get( 'Name' ) ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Site URL</strong></td>
                        <td><?php echo esc_html( get_site_url() ); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Memory Usage</strong></td>
                        <td><?php echo esc_html( size_format( memory_get_usage( true ) ) ); ?> / <?php echo esc_html( ini_get( 'memory_limit' ) ); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mzem-card">
        <div class="mzem-card-header">
            <div>
                <h2>Active Modules</h2>
                <p>All feature modules included in this plugin</p>
            </div>
        </div>
        <div class="mzem-table-wrap">
            <table class="mzem-table">
                <thead>
                    <tr>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>⧉ Clone Manager</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Duplicate posts, pages, and CPTs with all metadata</td>
                    </tr>
                    <tr>
                        <td><strong>📋 Custom Field Viewer</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Browse and filter posts by custom field values</td>
                    </tr>
                    <tr>
                        <td><strong>🛡️ Content Protection</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Disable right-click, inspect, and keyboard shortcuts</td>
                    </tr>
                    <tr>
                        <td><strong>📤 File Upload Control</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Set custom max upload size for media and plugins</td>
                    </tr>
                    <tr>
                        <td><strong>📎 MIME Type Manager</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Enable or disable allowed file upload types</td>
                    </tr>
                    <tr>
                        <td><strong>🔄 Export / Import</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Export and import posts with meta in JSON or XML</td>
                    </tr>
                    <tr>
                        <td><strong>↕️ Custom Order</strong></td>
                        <td><span class="mzem-badge mzem-badge-success">Active</span></td>
                        <td>Manage post and page display order via drag-and-drop</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="mzem-footer">
        MZ Easy Manage v<?php echo esc_html( MZEM_VERSION ); ?> — Built for modern WordPress management.
    </div>
</div>
