<?php

class Voce_Post_PDFS {

	const TEMPLATE = 'print.php';

	/**
	 *
	 */
	public static function initialize() {
		add_rewrite_endpoint( 'pdf', EP_PERMALINK );
		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2 );
		add_filter( 'request', array( __CLASS__, 'request' ) );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
	}

	/**
	 * Generate a pdf for the post when it is updated.
	 * @param $post_id
	 * @param $post
	 */
	public static function save_post( $post_id, $post ) {
		if( wp_is_post_autosave($post_id) || wp_is_post_revision($post_id) || 'post' != $post->post_type || isset($_REQUEST['bulk_edit']) ) {
			return $post_id;
		}

		self::save_pdf( $post );
	}

	/**
	 * If 'print' or 'pdf' request var is set. Set the value to true.
	 * @param $vars array of request variables
	 * @return array of request variables
	 */
	public static function request( $vars ) {
		if( isset( $vars['pdf'] ) )
			$vars['pdf'] = true;

		return $vars;
	}

	/**
	 * Override the default template
	 * @param $template path of the template file to load
	 * @return string the path to template to load
	 */
	public static function template_include( $template ) {
		global $post;
		// redirect to the pdf or 404
		if( $post && get_query_var( 'pdf' ) ) {
			$template = self::view_pdf();
		}
			
		return $template;
	}

	/**
	 * Create the pdf if it does not exist. If the pdf creation
	 * fails do a 404 redirect. If the pdf creation is a success
	 * redirect to the pdf.
	 */
	private static function view_pdf() {
		global $post;

		$basepath = self::get_upload_basepath($post);
		$baseurl = self::get_upload_baseurl($post);
		$filename = apply_filters('voce_post_pdfs_save_filename', $post->post_name . '.pdf');
		$file = $basepath . $filename;

		// create pdf if it does not exist
		if( ! file_exists( $file ) )
			self::save_pdf( $post );

		// redirect if the pdf exists
		if( file_exists( $file ) )
			wp_redirect( add_query_arg( 't', time(), $baseurl . $filename ));

		// 404 error if pdf does not exist
		return locate_template( array( '404.php' ), false, false );;
	}

	private static function get_upload_basepath($post) {
		$dir = wp_upload_dir();
		$basepath = $dir['basedir'] . '/pdf/';
		return apply_filters('voce_post_pdfs_upload_basepath', $basepath, $post);
	}

	private static function get_upload_baseurl($post) {
		$dir = wp_upload_dir();
		$baseurl = $dir['baseurl'] . '/pdf/';
		return apply_filters('voce_post_pdfs_upload_baseurl', $baseurl, $post);
	}

	/**
	 * @param $post
	 * @return int a numer indicating the number of bytes written or FALSE on failure.
	 */
	 public static function save_pdf( $post ) {

		$args = apply_filters( 'voce_post_pdfs_save_query_args', 
			array( 
				'p' => $post->ID, 
				'post_type' => $post->post_type, 
				'post_status' => 'publish' 
			), $post );

		// generate the html
		query_posts( $args );

		if( !have_posts() )
			return;

		ob_start();
		$path = str_replace( TEMPLATEPATH, '', __DIR__ );
		$template = apply_filters( 'voce_post_pdf_print_template', $path . '/' . self::TEMPLATE );
		locate_template( array($template), true, true );
		$content = ob_get_clean();

		wp_reset_query();
		if( empty( $content ) )
			return;

		// generate the pdf
		require_once( __DIR__ . '/dompdf/dompdf_config.inc.php' );
		$dompdf = new DOMPDF();
		$dompdf->load_html( $content );
		$dompdf->set_paper( 'letter', 'portrait' );
		$dompdf->render();

		// save the pdf
		$basepath = self::get_upload_basepath($post);
		if( ! is_dir( $basepath ) )
			mkdir( $basepath, 0777, true );

		$filename = apply_filters('voce_post_pdfs_save_filename', $post->post_name . '.pdf');
		$file = $basepath . $filename;
		return file_put_contents( $file, $dompdf->output() );
	}
}
add_action( 'init', array( 'Voce_Post_PDFS', 'initialize' ) );
