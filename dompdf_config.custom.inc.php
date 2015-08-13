<?php

$upload_dir = Voce_Post_PDFS::wp_upload_dir();
if( ! is_dir( $upload_dir['basedir'] . '/dompdf/fonts' ) ){
	mkdir( $upload_dir['basedir'] . '/dompdf/fonts', 0777, true );
}

define( 'DOMPDF_ENABLE_REMOTE', true );
define( 'DOMPDF_ENABLE_AUTOLOAD', false );
define( 'DOMPDF_ENABLE_PHP', true );
define( 'DOMPDF_LOG_OUTPUT_FILE', false );
define( 'DOMPDF_FONT_CACHE', $upload_dir['basedir'] . '/dompdf/fonts' );