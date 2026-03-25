/**
 * MZ Easy Manage – Admin JavaScript
 */
(function ($) {
    'use strict';

    var MZEM = window.MZEM || {};

    /* ════════════════════════════════════════════════════════
     *  Toast Notifications
     * ════════════════════════════════════════════════════════ */
    MZEM.toast = function (message, type) {
        type = type || 'success';
        var existing = document.querySelector('.mzem-toast');
        if (existing) existing.remove();

        var toast = document.createElement('div');
        toast.className = 'mzem-toast mzem-toast-' + type;
        toast.innerHTML = '<span>' + message + '</span>';
        document.body.appendChild(toast);

        requestAnimationFrame(function () {
            toast.classList.add('show');
        });

        setTimeout(function () {
            toast.classList.remove('show');
            setTimeout(function () { toast.remove(); }, 400);
        }, 3500);
    };

    /* ════════════════════════════════════════════════════════
     *  Tab Switching
     * ════════════════════════════════════════════════════════ */
    $(document).on('click', '.mzem-tab', function () {
        var target = $(this).data('tab');
        $(this).siblings().removeClass('active');
        $(this).addClass('active');
        $(this).closest('.mzem-card, .mzem-wrap').find('.mzem-tab-content').removeClass('active');
        $('#' + target).addClass('active');
    });

    /* ════════════════════════════════════════════════════════
     *  Dashboard Stats
     * ════════════════════════════════════════════════════════ */
    MZEM.loadStats = function () {
        $.ajax({
            url: mzemData.restUrl + 'stats',
            method: 'GET',
            beforeSend: function (xhr) {
                xhr.setRequestHeader('X-WP-Nonce', mzemData.restNonce);
            },
            success: function (res) {
                if (res.posts !== undefined) $('#mzem-stat-posts').text(res.posts);
                if (res.pages !== undefined) $('#mzem-stat-pages').text(res.pages);
                if (res.cpt !== undefined) $('#mzem-stat-cpt').text(res.cpt);
                if (res.custom_fields !== undefined) $('#mzem-stat-fields').text(res.custom_fields);
            }
        });
    };

    /* ════════════════════════════════════════════════════════
     *  Protection Settings
     * ════════════════════════════════════════════════════════ */
    MZEM.saveProtection = function () {
        var postTypes = [];
        $('input[name="mzem_protect_pt[]"]:checked').each(function () {
            postTypes.push($(this).val());
        });

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mzem_save_protection',
                nonce: mzemData.nonce,
                enabled: $('#mzem-protection-enabled').is(':checked') ? 1 : 0,
                disable_right_click: $('#mzem-disable-right-click').is(':checked') ? 1 : 0,
                disable_keyboard: $('#mzem-disable-keyboard').is(':checked') ? 1 : 0,
                disable_text_select: $('#mzem-disable-text-select').is(':checked') ? 1 : 0,
                disable_image_drag: $('#mzem-disable-image-drag').is(':checked') ? 1 : 0,
                disable_copy: $('#mzem-disable-copy').is(':checked') ? 1 : 0,
                notification_message: $('#mzem-notification-message').val(),
                notification_type: $('input[name="mzem_notification_type"]:checked').val(),
                skip_admin: $('#mzem-skip-admin').is(':checked') ? 1 : 0,
                post_types: postTypes,
                exclude_ids: $('#mzem-exclude-ids').val()
            },
            success: function (res) {
                if (res.success) {
                    MZEM.toast(res.data.message, 'success');
                } else {
                    MZEM.toast('Failed to save.', 'error');
                }
            },
            error: function () {
                MZEM.toast('Network error.', 'error');
            }
        });
    };

    /* ════════════════════════════════════════════════════════
     *  Upload Limit
     * ════════════════════════════════════════════════════════ */
    MZEM.saveUploadLimit = function () {
        var limitMb = $('#mzem-upload-limit').val();
        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mzem_save_upload_limit',
                nonce: mzemData.nonce,
                limit_mb: limitMb
            },
            success: function (res) {
                if (res.success) {
                    MZEM.toast(res.data.message, 'success');
                    // Instantly update the Custom Limit display
                    var sizeStr = limitMb >= 1024 ? (limitMb / 1024) + ' GB' : limitMb + ' MB';
                    $('#mzem-srv-custom').text(sizeStr);
                    // Refresh the WP limit too
                    MZEM.loadServerLimits();
                } else {
                    MZEM.toast('Failed to save.', 'error');
                }
            }
        });
    };

    MZEM.loadServerLimits = function () {
        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: { action: 'mzem_get_server_limits', nonce: mzemData.nonce },
            success: function (res) {
                if (res.success) {
                    var d = res.data;
                    $('#mzem-srv-upload-max').text(d.upload_max_filesize);
                    $('#mzem-srv-post-max').text(d.post_max_size);
                    $('#mzem-srv-memory').text(d.memory_limit);
                    $('#mzem-srv-exec-time').text(d.max_execution_time + 's');
                    $('#mzem-srv-wp-limit').text(d.current_wp_limit);
                    if (d.custom_limit && d.custom_limit !== '0 B') {
                        $('#mzem-srv-custom').text(d.custom_limit);
                    }
                }
            }
        });
    };

    /* ════════════════════════════════════════════════════════
     *  MIME Types
     * ════════════════════════════════════════════════════════ */
    MZEM.loadMimeTypes = function () {
        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: { action: 'mzem_get_mime_types', nonce: mzemData.nonce },
            success: function (res) {
                if (res.success) {
                    MZEM.renderMimeTable(res.data.types);
                }
            }
        });
    };

    MZEM.renderMimeTable = function (types) {
        var tbody = $('#mzem-mime-tbody');
        tbody.empty();
        var searchTerm = ($('#mzem-mime-search').val() || '').toLowerCase();
        var filterCat = $('.mzem-filter-tag.active').data('category') || 'all';

        types.forEach(function (t) {
            if (searchTerm && t.label.toLowerCase().indexOf(searchTerm) === -1 && t.extension.toLowerCase().indexOf(searchTerm) === -1) {
                return;
            }

            // Category filter
            var cat = MZEM.getMimeCategory(t.mime);
            if (filterCat !== 'all' && cat !== filterCat) return;

            var checked = t.enabled ? 'checked' : '';
            var statusBadge = t.enabled
                ? '<span class="mzem-badge mzem-badge-success">Enabled</span>'
                : '<span class="mzem-badge mzem-badge-danger">Disabled</span>';

            tbody.append(
                '<tr>' +
                '<td><strong>' + MZEM.escHtml(t.extension) + '</strong></td>' +
                '<td>' + MZEM.escHtml(t.label) + '</td>' +
                '<td><code style="font-size:12px;color:var(--mzem-text-muted)">' + MZEM.escHtml(t.mime) + '</code></td>' +
                '<td>' + statusBadge + '</td>' +
                '<td><label class="mzem-toggle"><input type="checkbox" data-ext="' + MZEM.escHtml(t.extension) + '" ' + checked + '><span class="mzem-toggle-slider"></span></label></td>' +
                '</tr>'
            );
        });

        // Store types for save
        MZEM._mimeTypes = types;
    };

    MZEM.getMimeCategory = function (mime) {
        if (mime.indexOf('image') === 0) return 'images';
        if (mime.indexOf('audio') === 0) return 'audio';
        if (mime.indexOf('video') === 0) return 'video';
        if (mime.indexOf('font') === 0 || mime.indexOf('font') > -1) return 'fonts';
        if (mime.indexOf('model') === 0) return '3d';
        if (mime.indexOf('zip') > -1 || mime.indexOf('compressed') > -1 || mime.indexOf('tar') > -1 || mime.indexOf('gzip') > -1 || mime.indexOf('rar') > -1 || mime.indexOf('7z') > -1) return 'archives';
        return 'documents';
    };

    MZEM.saveMimeTypes = function () {
        var mimeTypes = {};
        $('#mzem-mime-tbody input[type="checkbox"]').each(function () {
            mimeTypes[$(this).data('ext')] = $(this).is(':checked') ? 1 : 0;
        });

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: { action: 'mzem_save_mime_types', nonce: mzemData.nonce, mime_types: mimeTypes },
            success: function (res) {
                if (res.success) {
                    MZEM.toast(res.data.message, 'success');
                    // Instantly update status badges and internal state
                    if (MZEM._mimeTypes) {
                        MZEM._mimeTypes.forEach(function (t) {
                            if (mimeTypes[t.extension] !== undefined) {
                                t.enabled = !!mimeTypes[t.extension];
                            }
                        });
                        MZEM.renderMimeTable(MZEM._mimeTypes);
                    }
                } else {
                    MZEM.toast('Failed.', 'error');
                }
            }
        });
    };

    /* ════════════════════════════════════════════════════════
     *  Clone Manager – Save post types
     * ════════════════════════════════════════════════════════ */
    MZEM.saveCloneSettings = function () {
        var postTypes = [];
        $('input[name="mzem_clone_pt[]"]').each(function () {
            if ($(this).is(':checked')) postTypes.push($(this).val());
        });

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: { action: 'mzem_save_clone_settings', nonce: mzemData.nonce, post_types: postTypes },
            success: function (res) {
                if (res.success) {
                    MZEM.toast(res.data.message, 'success');
                } else {
                    MZEM.toast('Failed to save.', 'error');
                }
            },
            error: function () { MZEM.toast('Network error.', 'error'); }
        });
    };

    /* ════════════════════════════════════════════════════════
     *  Custom Fields Viewer
     * ════════════════════════════════════════════════════════ */

    // Currently selected fields for the table
    MZEM._cfSelectedFields = [];

    MZEM.loadMetaKeys = function (postType) {
        $('#mzem-cf-tags').html('<span style="font-size:13px;color:var(--mzem-text-muted);padding:5px 0;">Loading fields…</span>');
        MZEM._cfSelectedFields = [];

        // Update "View Post List" link
        var editUrl = mzemData.adminUrl + 'edit.php' + (postType !== 'post' ? '?post_type=' + postType : '');
        $('#mzem-cf-go-to-list').attr('href', editUrl);

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: { action: 'mzem_get_meta_keys', nonce: mzemData.nonce, post_type: postType },
            success: function (res) {
                if (res.success) {
                    var tags = $('#mzem-cf-tags');
                    tags.empty();

                    var allKeys = res.data.meta_keys || [];
                    var savedFields = res.data.saved_fields || [];

                    // Pre-select saved fields
                    MZEM._cfSelectedFields = savedFields.slice();

                    if (!allKeys.length) {
                        tags.html('<span style="font-size:13px;color:var(--mzem-text-muted);padding:5px 0;">No custom fields found for this post type.</span>');
                    } else {
                        allKeys.forEach(function (key) {
                            var isActive = savedFields.indexOf(key) !== -1 ? ' active' : '';
                            tags.append(
                                '<button type="button" class="mzem-filter-tag mzem-cf-field-tag' + isActive + '" data-field="' + MZEM.escHtml(key) + '">' +
                                MZEM.escHtml(key) +
                                '</button>'
                            );
                        });
                    }

                    // Load posts with saved fields as columns
                    MZEM.loadPostsData(postType, MZEM._cfSelectedFields, 1);
                }
            }
        });
    };

    MZEM.saveCFColumns = function () {
        var postType = $('#mzem-cf-post-type').val();

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mzem_save_cf_columns',
                nonce: mzemData.nonce,
                post_type: postType,
                fields: MZEM._cfSelectedFields
            },
            success: function (res) {
                if (res.success) {
                    MZEM.toast(res.data.message, 'success');
                } else {
                    MZEM.toast('Failed to save.', 'error');
                }
            },
            error: function () { MZEM.toast('Network error.', 'error'); }
        });
    };

    MZEM.loadPostsData = function (postType, fields, page) {
        var search = $('#mzem-cf-search').val() || '';
        var orderby = MZEM._cfOrderBy || 'date';
        var order = MZEM._cfOrder || 'DESC';

        $('#mzem-cf-table-wrap').html('<div class="mzem-loading-overlay"><div class="mzem-spinner"></div> Loading...</div>');

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mzem_get_posts_data',
                nonce: mzemData.nonce,
                post_type: postType,
                fields: fields,
                search: search,
                orderby: orderby,
                order: order,
                paged: page,
                per_page: 20
            },
            success: function (res) {
                if (res.success) {
                    MZEM.renderCFTable(res.data, fields, postType);
                }
            }
        });
    };

    MZEM.renderCFTable = function (data, fields, postType) {
        var wrap = $('#mzem-cf-table-wrap');
        var html = '<div class="mzem-table-wrap"><table class="mzem-table"><thead><tr>';
        html += '<th data-sort="ID">ID <span class="sort-icon">↕</span></th>';
        html += '<th data-sort="title">Title <span class="sort-icon">↕</span></th>';
        html += '<th data-sort="author">Author <span class="sort-icon">↕</span></th>';
        html += '<th>Status</th>';
        html += '<th data-sort="date">Date <span class="sort-icon">↕</span></th>';

        fields.forEach(function (f) {
            html += '<th>' + MZEM.escHtml(MZEM.humanizeField(f)) + '</th>';
        });

        html += '</tr></thead><tbody>';

        if (!data.posts.length) {
            var colCount = 5 + fields.length;
            html += '<tr><td colspan="' + colCount + '"><div class="mzem-empty-state"><div class="icon">📭</div><h3>No posts found</h3></div></td></tr>';
        }

        data.posts.forEach(function (post) {
            var statusClass = post.status === 'publish' ? 'success' : (post.status === 'draft' ? 'warning' : 'info');
            html += '<tr>';
            html += '<td>' + post.ID + '</td>';
            html += '<td><strong>' + MZEM.escHtml(post.title) + '</strong></td>';
            html += '<td>' + MZEM.escHtml(post.author) + '</td>';
            html += '<td><span class="mzem-badge mzem-badge-' + statusClass + '">' + MZEM.escHtml(post.status) + '</span></td>';
            html += '<td>' + MZEM.escHtml(post.date) + '</td>';

            fields.forEach(function (f) {
                var val = post[f];
                // Image object returned by PHP resolve_meta_value for attachments
                if (val && typeof val === 'object' && val.type === 'image') {
                    html += '<td>' + val.html + '</td>';
                } else {
                    val = val !== null && val !== undefined && val !== '' ? val : '—';
                    // Format compact YYYYMMDD dates → YYYY-MM-DD (e.g. 20250430 → 2025-04-30)
                    if (typeof val === 'string' && /^\d{8}$/.test(val)) {
                        val = val.substring(0, 4) + '-' + val.substring(4, 6) + '-' + val.substring(6, 8);
                    }
                    if (typeof val === 'string' && val.length > 60) val = val.substring(0, 60) + '…';
                    html += '<td>' + MZEM.escHtml(String(val)) + '</td>';
                }
            });

            html += '</tr>';
        });

        html += '</tbody></table></div>';

        // Pagination
        if (data.pages > 1) {
            html += '<div class="mzem-pagination">';
            html += '<button ' + (data.current <= 1 ? 'disabled' : '') + ' data-page="' + (data.current - 1) + '">← Prev</button>';
            for (var i = 1; i <= data.pages; i++) {
                html += '<button class="' + (i === data.current ? 'active' : '') + '" data-page="' + i + '">' + i + '</button>';
            }
            html += '<button ' + (data.current >= data.pages ? 'disabled' : '') + ' data-page="' + (data.current + 1) + '">Next →</button>';
            html += '</div>';
        }

        wrap.html(html);

        // Sort click
        wrap.find('th[data-sort]').off('click').on('click', function () {
            var sortBy = $(this).data('sort');
            if (MZEM._cfOrderBy === sortBy) {
                MZEM._cfOrder = MZEM._cfOrder === 'ASC' ? 'DESC' : 'ASC';
            } else {
                MZEM._cfOrderBy = sortBy;
                MZEM._cfOrder = 'ASC';
            }
            MZEM.loadPostsData(postType, fields, 1);
        });

        // Pagination click
        wrap.find('.mzem-pagination button').off('click').on('click', function () {
            var pg = $(this).data('page');
            if (pg) MZEM.loadPostsData(postType, fields, pg);
        });
    };

    /* ════════════════════════════════════════════════════════
     *  Export / Import
     * ════════════════════════════════════════════════════════ */
    MZEM.exportData = function () {
        var postTypes = [];
        $('input[name="mzem_export_pt[]"]:checked').each(function () {
            postTypes.push($(this).val());
        });

        if (!postTypes.length) {
            MZEM.toast('Please select at least one post type.', 'error');
            return;
        }

        var format = $('input[name="mzem_export_format"]:checked').val() || 'json';

        $('#mzem-export-btn').prop('disabled', true).html('<span class="mzem-spinner"></span> Exporting...');

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: {
                action: 'mzem_export',
                nonce: mzemData.nonce,
                post_types: postTypes,
                format: format
            },
            success: function (res) {
                $('#mzem-export-btn').prop('disabled', false).html('📦 Export');
                if (res.success) {
                    MZEM.toast(res.data.message, 'success');
                    // Create blob and trigger download
                    var blob = new Blob([res.data.content], { type: res.data.mime });
                    var url = window.URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.href = url;
                    a.download = res.data.filename;
                    document.body.appendChild(a);
                    a.click();
                    setTimeout(function () {
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    }, 0);
                } else {
                    MZEM.toast('Export failed.', 'error');
                }
            },
            error: function () {
                $('#mzem-export-btn').prop('disabled', false).html('📦 Export');
                MZEM.toast('Network error.', 'error');
            }
        });
    };

    MZEM.importPreview = function (file) {
        var formData = new FormData();
        formData.append('action', 'mzem_import_preview');
        formData.append('nonce', mzemData.nonce);
        formData.append('import_file', file);

        MZEM.setImportStep(2);

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (res) {
                if (res.success) {
                    var s = res.data.summary;
                    $('#mzem-import-preview-info').html(
                        '<p><strong>Items:</strong> ' + s.count + '</p>' +
                        '<p><strong>Post Types:</strong> ' + (s.post_types ? s.post_types.join(', ') : 'N/A') + '</p>' +
                        '<p><strong>Source:</strong> ' + MZEM.escHtml(s.site || 'N/A') + '</p>' +
                        '<p><strong>Exported:</strong> ' + MZEM.escHtml(s.exported || 'N/A') + '</p>'
                    );
                    MZEM.toast(res.data.message, 'success');
                } else {
                    MZEM.toast(res.data || 'Parse failed.', 'error');
                    MZEM.setImportStep(1);
                }
            },
            error: function () {
                MZEM.toast('Upload failed.', 'error');
                MZEM.setImportStep(1);
            }
        });
    };

    MZEM.importRun = function () {
        MZEM.setImportStep(3);
        $('#mzem-import-run-btn').prop('disabled', true).html('<span class="mzem-spinner"></span> Importing...');

        $.ajax({
            url: mzemData.ajaxUrl,
            method: 'POST',
            data: { action: 'mzem_import_run', nonce: mzemData.nonce },
            success: function (res) {
                $('#mzem-import-run-btn').prop('disabled', false).html('🚀 Start Import');
                if (res.success) {
                    MZEM.setImportStep(4);
                    $('#mzem-import-result').html(
                        '<div class="mzem-empty-state"><div class="icon">✅</div><h3>Import Complete!</h3><p>' + res.data.message + '</p></div>'
                    );
                    MZEM.toast(res.data.message, 'success');
                } else {
                    MZEM.toast(res.data || 'Import failed.', 'error');
                }
            },
            error: function () {
                MZEM.toast('Network error.', 'error');
                $('#mzem-import-run-btn').prop('disabled', false).html('🚀 Start Import');
            }
        });
    };

    MZEM.setImportStep = function (step) {
        $('.mzem-step').each(function () {
            var s = $(this).data('step');
            $(this).removeClass('active done');
            if (s < step) $(this).addClass('done');
            if (s === step) $(this).addClass('active');
        });
        $('.mzem-step-line').each(function () {
            var s = $(this).data('step');
            $(this).toggleClass('done', s < step);
        });
        $('.mzem-import-panel').hide();
        $('#mzem-import-step-' + step).show();
    };

    /* ════════════════════════════════════════════════════════
     *  Utilities
     * ════════════════════════════════════════════════════════ */
    MZEM.escHtml = function (str) {
        if (typeof str !== 'string') return str;
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(str));
        return div.innerHTML;
    };

    /**
     * Convert a raw meta key into a readable label.
     * e.g. "_product_cat_id" → "Product Cat Id"
     */
    MZEM.humanizeField = function (key) {
        return key
            .replace(/^_+/, '')          // strip leading underscores
            .replace(/[_-]+/g, ' ')      // underscores / hyphens → spaces
            .replace(/\b\w/g, function (c) { return c.toUpperCase(); }); // title-case
    };

    /* ════════════════════════════════════════════════════════
     *  Init on DOM ready
     * ════════════════════════════════════════════════════════ */
    $(function () {
        // Dashboard: load stats
        if ($('#mzem-stat-posts').length) {
            MZEM.loadStats();
        }

        // Upload settings: load server limits
        if ($('#mzem-srv-upload-max').length) {
            MZEM.loadServerLimits();
        }

        // MIME Types: load table
        if ($('#mzem-mime-tbody').length) {
            MZEM.loadMimeTypes();
        }

        // Protection: save
        $(document).on('click', '#mzem-protection-save', function () {
            MZEM.saveProtection();
        });

        // Upload: save
        $(document).on('click', '#mzem-upload-save', function () {
            MZEM.saveUploadLimit();
        });

        // MIME: save
        $(document).on('click', '#mzem-mime-save', function () {
            MZEM.saveMimeTypes();
        });

        // MIME: search
        $(document).on('input', '#mzem-mime-search', function () {
            if (MZEM._mimeTypes) MZEM.renderMimeTable(MZEM._mimeTypes);
        });

        // MIME: category filter (only on MIME page tags, not CF tags)
        $(document).on('click', '.mzem-filter-tag:not(.mzem-cf-field-tag)', function () {
            $(this).closest('.mzem-filter-tags').find('.mzem-filter-tag').removeClass('active');
            $(this).addClass('active');
            if (MZEM._mimeTypes) MZEM.renderMimeTable(MZEM._mimeTypes);
        });

        // Clone Manager: save
        $(document).on('click', '#mzem-clone-save', function () {
            MZEM.saveCloneSettings();
        });

        // Custom Fields: save columns
        $(document).on('click', '#mzem-cf-save', function () {
            MZEM.saveCFColumns();
        });

        // Custom fields: post type change → load meta keys + table
        $(document).on('change', '#mzem-cf-post-type', function () {
            MZEM.loadMetaKeys($(this).val());
        });

        // Custom fields: auto-load on page init
        if ($('#mzem-cf-post-type').length) {
            MZEM.loadMetaKeys($('#mzem-cf-post-type').val());
        }

        // Custom fields: click a field tag to add/remove column
        $(document).on('click', '.mzem-cf-field-tag', function () {
            var field = $(this).data('field');
            $(this).toggleClass('active');

            if ($(this).hasClass('active')) {
                if (MZEM._cfSelectedFields.indexOf(field) === -1) {
                    MZEM._cfSelectedFields.push(field);
                }
            } else {
                MZEM._cfSelectedFields = MZEM._cfSelectedFields.filter(function (f) { return f !== field; });
            }

            var postType = $('#mzem-cf-post-type').val();
            MZEM.loadPostsData(postType, MZEM._cfSelectedFields, 1);
        });

        // Custom fields: search
        $(document).on('keypress', '#mzem-cf-search', function (e) {
            if (e.which === 13) {
                var postType = $('#mzem-cf-post-type').val();
                MZEM.loadPostsData(postType, MZEM._cfSelectedFields, 1);
            }
        });

        // Export
        $(document).on('click', '#mzem-export-btn', function () {
            MZEM.exportData();
        });

        // Import: file upload via dropzone
        $(document).on('click', '#mzem-import-dropzone', function () {
            $('#mzem-import-file').click();
        });

        $(document).on('change', '#mzem-import-file', function () {
            if (this.files[0]) {
                MZEM.importPreview(this.files[0]);
            }
        });

        // Import: drag and drop
        $(document).on('dragover', '#mzem-import-dropzone', function (e) {
            e.preventDefault();
            $(this).addClass('dragover');
        });
        $(document).on('dragleave', '#mzem-import-dropzone', function () {
            $(this).removeClass('dragover');
        });
        $(document).on('drop', '#mzem-import-dropzone', function (e) {
            e.preventDefault();
            $(this).removeClass('dragover');
            var files = e.originalEvent.dataTransfer.files;
            if (files[0]) {
                MZEM.importPreview(files[0]);
            }
        });

        // Import: run
        $(document).on('click', '#mzem-import-run-btn', function () {
            MZEM.importRun();
        });

        // Dark mode toggle – fix for checkbox slider
        $(document).on('change', '#mzem-dark-toggle', function () {
            var isDark = $(this).is(':checked');
            $('body').toggleClass('mzem-dark', isDark);
            localStorage.setItem('mzem_dark_mode', isDark ? '1' : '0');
        });

        // Restore dark mode
        if (localStorage.getItem('mzem_dark_mode') === '1') {
            $('body').addClass('mzem-dark');
            $('#mzem-dark-toggle').prop('checked', true);
        }

        // Custom Order: load posts
        var loadOrderPosts = function (postType) {
            $('#mzem-order-loader').show();
            $('#mzem-order-save').prop('disabled', true);

            $.ajax({
                url: mzemData.ajaxUrl,
                method: 'POST',
                data: { action: 'mzem_get_posts_for_order', nonce: mzemData.nonce, post_type: postType },
                success: function (res) {
                    $('#mzem-order-loader').hide();
                    if (res.success) {
                        var list = $('#mzem-order-list');
                        list.empty();

                        if (!res.data.posts.length) {
                            list.html('<div style="padding: 40px; text-align: center; color: var(--mzem-text-muted);">No posts found for this post type.</div>');
                            return;
                        }

                        res.data.posts.forEach(function (p) {
                            list.append(
                                '<div class="mzem-order-item" data-id="' + p.id + '">' +
                                '<span class="handle">⋮⋮</span>' +
                                '<span class="title">' + MZEM.escHtml(p.title) + '</span>' +
                                '<span class="id-badge">#' + p.id + '</span>' +
                                '</div>'
                            );
                        });

                        // Initialize Sortable – destroy previous instance first.
                        // Resolve the constructor: supports window.Sortable and
                        // window.Sortable.default (some UMD/ESM builds).
                        var SortableLib = (typeof window.Sortable === 'function')
                            ? window.Sortable
                            : (window.Sortable && typeof window.Sortable.default === 'function')
                                ? window.Sortable.default
                                : null;

                        if (SortableLib) {
                            if (MZEM._sortableInstance) {
                                MZEM._sortableInstance.destroy();
                                MZEM._sortableInstance = null;
                            }
                            MZEM._sortableInstance = new SortableLib(document.getElementById('mzem-order-list'), {
                                handle: '.handle',
                                animation: 150,
                                ghostClass: 'sortable-ghost',
                                onEnd: function () {
                                    $('#mzem-order-save').prop('disabled', false);
                                }
                            });
                        }
                    }
                }
            });
        };

        $(document).on('change', '#mzem-order-post-type', function () {
            loadOrderPosts($(this).val());
        });

        if ($('#mzem-order-post-type').length) {
            loadOrderPosts($('#mzem-order-post-type').val());
        }

        $(document).on('click', '#mzem-order-save', function () {
            var order = [];
            $('#mzem-order-list .mzem-order-item').each(function () {
                order.push($(this).data('id'));
            });

            var btn = $(this);
            btn.prop('disabled', true).text('⏳ Saving...');

            $.ajax({
                url: mzemData.ajaxUrl,
                method: 'POST',
                data: { action: 'mzem_save_post_order', nonce: mzemData.nonce, order: order },
                success: function (res) {
                    btn.text('💾 Save New Order');
                    if (res.success) {
                        MZEM.toast(res.data.message, 'success');
                    } else {
                        MZEM.toast(res.data.message || 'Failed to save.', 'error');
                        btn.prop('disabled', false);
                    }
                },
                error: function () {
                    btn.prop('disabled', false).text('💾 Save New Order');
                    MZEM.toast('Network error.', 'error');
                }
            });
        });
    });

    window.MZEM = MZEM;

})(jQuery);
