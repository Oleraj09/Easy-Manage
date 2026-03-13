<?php
/**
 * Module: File MIME Type Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class MZEM_Mime_Types {

    public function __construct() {
        add_filter( 'upload_mimes',                    array( $this, 'modify_mimes' ) );
        add_action( 'wp_ajax_mzem_save_mime_types',    array( $this, 'save_mime_types' ) );
        add_action( 'wp_ajax_mzem_get_mime_types',     array( $this, 'get_mime_types' ) );
        // Allow unfiltered uploads for SVG etc.
        add_filter( 'wp_check_filetype_and_ext',       array( $this, 'fix_filetype' ), 10, 5 );
    }

    /**
     * Full list of possible MIME types.
     */
    public static function get_all_mime_types() {
        return array(
            // Images
            'jpg|jpeg|jpe' => array( 'mime' => 'image/jpeg',       'label' => 'JPEG Image',            'default' => true ),
            'gif'          => array( 'mime' => 'image/gif',        'label' => 'GIF Image',             'default' => true ),
            'png'          => array( 'mime' => 'image/png',        'label' => 'PNG Image',             'default' => true ),
            'bmp'          => array( 'mime' => 'image/bmp',        'label' => 'BMP Image',             'default' => true ),
            'webp'         => array( 'mime' => 'image/webp',       'label' => 'WebP Image',            'default' => true ),
            'ico'          => array( 'mime' => 'image/x-icon',     'label' => 'ICO Icon',              'default' => true ),
            'tif|tiff'     => array( 'mime' => 'image/tiff',       'label' => 'TIFF Image',            'default' => true ),
            'svg'          => array( 'mime' => 'image/svg+xml',    'label' => 'SVG Image',             'default' => false ),
            'svgz'         => array( 'mime' => 'image/svg+xml',    'label' => 'Compressed SVG',        'default' => false ),
            'heic'         => array( 'mime' => 'image/heic',       'label' => 'HEIC Image',            'default' => false ),
            'heif'         => array( 'mime' => 'image/heif',       'label' => 'HEIF Image',            'default' => false ),
            'avif'         => array( 'mime' => 'image/avif',       'label' => 'AVIF Image',            'default' => false ),
            'psd'          => array( 'mime' => 'image/vnd.adobe.photoshop', 'label' => 'Photoshop File', 'default' => false ),
            'ai'           => array( 'mime' => 'application/postscript',    'label' => 'Illustrator File', 'default' => false ),

            // Documents
            'pdf'          => array( 'mime' => 'application/pdf',  'label' => 'PDF Document',          'default' => true ),
            'doc'          => array( 'mime' => 'application/msword', 'label' => 'Word Document',       'default' => true ),
            'docx'         => array( 'mime' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'label' => 'Word Document (DOCX)', 'default' => true ),
            'xls'          => array( 'mime' => 'application/vnd.ms-excel', 'label' => 'Excel Spreadsheet', 'default' => true ),
            'xlsx'         => array( 'mime' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'label' => 'Excel Spreadsheet (XLSX)', 'default' => true ),
            'ppt'          => array( 'mime' => 'application/vnd.ms-powerpoint', 'label' => 'PowerPoint', 'default' => true ),
            'pptx'         => array( 'mime' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation', 'label' => 'PowerPoint (PPTX)', 'default' => true ),
            'odt'          => array( 'mime' => 'application/vnd.oasis.opendocument.text', 'label' => 'OpenDocument Text', 'default' => true ),
            'ods'          => array( 'mime' => 'application/vnd.oasis.opendocument.spreadsheet', 'label' => 'OpenDocument Spreadsheet', 'default' => false ),
            'odp'          => array( 'mime' => 'application/vnd.oasis.opendocument.presentation', 'label' => 'OpenDocument Presentation', 'default' => false ),
            'csv'          => array( 'mime' => 'text/csv',         'label' => 'CSV File',              'default' => false ),
            'txt|asc|c|cc|h|srt' => array( 'mime' => 'text/plain', 'label' => 'Plain Text',           'default' => true ),
            'rtf'          => array( 'mime' => 'application/rtf',  'label' => 'Rich Text Format',      'default' => true ),
            'epub'         => array( 'mime' => 'application/epub+zip', 'label' => 'EPUB eBook',        'default' => false ),

            // Audio
            'mp3|m4a|m4b'  => array( 'mime' => 'audio/mpeg',      'label' => 'MP3 Audio',             'default' => true ),
            'ogg|oga'      => array( 'mime' => 'audio/ogg',       'label' => 'OGG Audio',             'default' => true ),
            'wav'          => array( 'mime' => 'audio/wav',        'label' => 'WAV Audio',             'default' => true ),
            'wma'          => array( 'mime' => 'audio/x-ms-wma',  'label' => 'WMA Audio',             'default' => false ),
            'flac'         => array( 'mime' => 'audio/flac',       'label' => 'FLAC Audio',            'default' => false ),
            'aac'          => array( 'mime' => 'audio/aac',        'label' => 'AAC Audio',             'default' => false ),
            'mid|midi'     => array( 'mime' => 'audio/midi',       'label' => 'MIDI Audio',            'default' => false ),
            'aiff|aif'     => array( 'mime' => 'audio/aiff',       'label' => 'AIFF Audio',            'default' => false ),

            // Video
            'mp4|m4v'      => array( 'mime' => 'video/mp4',       'label' => 'MP4 Video',             'default' => true ),
            'mov|qt'       => array( 'mime' => 'video/quicktime', 'label' => 'QuickTime Video',       'default' => true ),
            'avi'          => array( 'mime' => 'video/x-msvideo', 'label' => 'AVI Video',             'default' => true ),
            'wmv'          => array( 'mime' => 'video/x-ms-wmv',  'label' => 'WMV Video',             'default' => true ),
            'webm'         => array( 'mime' => 'video/webm',      'label' => 'WebM Video',            'default' => true ),
            'ogv'          => array( 'mime' => 'video/ogg',       'label' => 'OGG Video',             'default' => true ),
            'mkv'          => array( 'mime' => 'video/x-matroska','label' => 'MKV Video',             'default' => false ),
            'flv'          => array( 'mime' => 'video/x-flv',     'label' => 'FLV Video',             'default' => false ),
            '3gp|3gpp'     => array( 'mime' => 'video/3gpp',      'label' => '3GP Video',             'default' => false ),
            '3g2|3gp2'     => array( 'mime' => 'video/3gpp2',     'label' => '3G2 Video',             'default' => false ),
            'mpg|mpeg'     => array( 'mime' => 'video/mpeg',      'label' => 'MPEG Video',            'default' => false ),

            // Archives
            'zip'          => array( 'mime' => 'application/zip', 'label' => 'ZIP Archive',           'default' => false ),
            'rar'          => array( 'mime' => 'application/x-rar-compressed', 'label' => 'RAR Archive', 'default' => false ),
            '7z'           => array( 'mime' => 'application/x-7z-compressed',  'label' => '7Z Archive',  'default' => false ),
            'tar'          => array( 'mime' => 'application/x-tar', 'label' => 'TAR Archive',         'default' => false ),
            'gz|gzip'      => array( 'mime' => 'application/gzip', 'label' => 'GZip Archive',         'default' => false ),

            // Code & Data
            'json'         => array( 'mime' => 'application/json','label' => 'JSON File',             'default' => false ),
            'xml'          => array( 'mime' => 'application/xml', 'label' => 'XML File',              'default' => false ),
            'html|htm'     => array( 'mime' => 'text/html',       'label' => 'HTML File',             'default' => false ),
            'css'          => array( 'mime' => 'text/css',        'label' => 'CSS File',              'default' => false ),
            'js'           => array( 'mime' => 'application/javascript', 'label' => 'JavaScript File','default' => false ),
            'sql'          => array( 'mime' => 'application/sql', 'label' => 'SQL File',              'default' => false ),
            'yaml|yml'     => array( 'mime' => 'application/x-yaml','label' => 'YAML File',           'default' => false ),
            'md'           => array( 'mime' => 'text/markdown',   'label' => 'Markdown File',         'default' => false ),

            // Fonts
            'ttf'          => array( 'mime' => 'font/ttf',        'label' => 'TrueType Font',         'default' => false ),
            'otf'          => array( 'mime' => 'font/otf',        'label' => 'OpenType Font',         'default' => false ),
            'woff'         => array( 'mime' => 'font/woff',       'label' => 'WOFF Font',             'default' => false ),
            'woff2'        => array( 'mime' => 'font/woff2',      'label' => 'WOFF2 Font',            'default' => false ),
            'eot'          => array( 'mime' => 'application/vnd.ms-fontobject', 'label' => 'EOT Font','default' => false ),

            // CAD / 3D
            'dwg'          => array( 'mime' => 'application/acad','label' => 'AutoCAD DWG',           'default' => false ),
            'dxf'          => array( 'mime' => 'application/dxf', 'label' => 'AutoCAD DXF',           'default' => false ),
            'stl'          => array( 'mime' => 'model/stl',       'label' => 'STL 3D Model',          'default' => false ),
            'obj'          => array( 'mime' => 'model/obj',       'label' => 'OBJ 3D Model',          'default' => false ),
            'glb'          => array( 'mime' => 'model/gltf-binary', 'label' => 'GLB 3D Model',        'default' => false ),
            'gltf'         => array( 'mime' => 'model/gltf+json', 'label' => 'glTF 3D Model',         'default' => false ),

            // Others
            'eps'          => array( 'mime' => 'application/postscript', 'label' => 'EPS Vector',     'default' => false ),
            'ics'          => array( 'mime' => 'text/calendar',   'label' => 'Calendar File',         'default' => false ),
            'vcf'          => array( 'mime' => 'text/vcard',      'label' => 'vCard Contact',         'default' => false ),
            'torrent'      => array( 'mime' => 'application/x-bittorrent', 'label' => 'Torrent File', 'default' => false ),
            'apk'          => array( 'mime' => 'application/vnd.android.package-archive', 'label' => 'Android APK', 'default' => false ),
            'exe'          => array( 'mime' => 'application/x-msdownload', 'label' => 'Windows Exe',  'default' => false ),
            'dmg'          => array( 'mime' => 'application/x-apple-diskimage', 'label' => 'macOS DMG','default' => false ),
            'iso'          => array( 'mime' => 'application/x-iso9660-image', 'label' => 'ISO Image', 'default' => false ),
        );
    }

    /**
     * Modify allowed MIME types based on saved settings.
     */
    public function modify_mimes( $mimes ) {
        $saved = get_option( 'mzem_mime_types', array() );
        $all   = self::get_all_mime_types();

        if ( empty( $saved ) ) {
            return $mimes;
        }

        foreach ( $all as $ext => $info ) {
            if ( isset( $saved[ $ext ] ) ) {
                if ( $saved[ $ext ] ) {
                    $mimes[ $ext ] = $info['mime'];
                } else {
                    unset( $mimes[ $ext ] );
                }
            }
        }

        return $mimes;
    }

    /**
     * Fix file type detection for non-standard types (e.g. SVG).
     */
    public function fix_filetype( $data, $file, $filename, $mimes, $real_mime = '' ) {
        if ( ! empty( $data['ext'] ) && ! empty( $data['type'] ) ) {
            return $data;
        }

        $filetype = wp_check_filetype( $filename, $mimes );

        if ( ! empty( $filetype['ext'] ) ) {
            $data['ext']             = $filetype['ext'];
            $data['type']            = $filetype['type'];
            $data['proper_filename'] = $filename;
        }

        return $data;
    }

    /**
     * AJAX: Save MIME type settings.
     */
    public function save_mime_types() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $types = isset( $_POST['mime_types'] ) ? map_deep( wp_unslash( $_POST['mime_types'] ), 'sanitize_text_field' ) : array();

        // Sanitise to boolean map
        $clean = array();
        if ( is_array( $types ) ) {
            foreach ( $types as $ext => $enabled ) {
                $clean[ $ext ] = (bool) $enabled;
            }
        }

        update_option( 'mzem_mime_types', $clean );

        wp_send_json_success( array( 'message' => 'MIME type settings saved!' ) );
    }

    /**
     * AJAX: Get all MIME types with saved states.
     */
    public function get_mime_types() {
        check_ajax_referer( 'mzem_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Permission denied.' );
        }

        $saved = get_option( 'mzem_mime_types', array() );
        $all   = self::get_all_mime_types();
        $result = array();

        foreach ( $all as $ext => $info ) {
            $enabled = $info['default'];
            if ( isset( $saved[ $ext ] ) ) {
                $enabled = (bool) $saved[ $ext ];
            }
            $result[] = array(
                'extension' => $ext,
                'mime'      => $info['mime'],
                'label'     => $info['label'],
                'enabled'   => $enabled,
                'default'   => $info['default'],
            );
        }

        wp_send_json_success( array( 'types' => $result ) );
    }
}
