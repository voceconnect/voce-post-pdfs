<?php

require_once __DIR__ . '/dompdf_config.custom.inc.php';

if( file_exists( __DIR__ . '/vendor/autoload.php' ) ){
	require_once  __DIR__ . '/vendor/autoload.php';
}

class Voce_Post_PDFS {

	const TEMPLATE = 'print.php';

	/**
	 *
	 */
	public static function initialize() {
		self::add_endpoints();

		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2 );
		add_filter( 'request', array( __CLASS__, 'request' ) );
		add_filter( 'template_include', array( __CLASS__, 'template_include' ) );
	}

	/**
	 * Add 'print' and 'pdf' endpoints
	 */
	public static function add_endpoints() {
		add_rewrite_endpoint( 'pdf', EP_PERMALINK );
	}

	/**
	 * Generate a pdf for the post when it is updated.
	 * @param $post_id
	 * @param $post
	 */
	public static function save_post( $post_id, $post ) {
		if(
			wp_is_post_autosave($post_id) ||
			wp_is_post_revision($post_id) ||
			! post_type_supports( $post->post_type, 'print_pdf' ) ||
			isset($_REQUEST['bulk_edit'])
		) {
			return $post_id;
		}

		self::save_pdf( $post );
	}

	/**
	 * If 'pdf' request var is set. Set the value to true.
	 *
	 * @param $vars array of request variables
	 * @return array of request variables
	 */
	public static function request( $vars ) {
		if ( isset( $vars['pdf'] ) )
			$vars['pdf'] = true;

		return $vars;
	}

	/**
	 * Override the default template
	 * @param $template path of the template file to load
	 * @return string the path to template to load
	 */
	public static function template_include( $template ) {

		// redirect to the pdf or 404
		if ( get_query_var( 'pdf' ) ) {

			do_action( 'voce_post_pdfs_before_view_pdf' );

			$template = self::view_pdf();

			do_action( 'voce_post_pdfs_after_view_pdf' );
		}

		return $template;
	}

	private static function get_404_template() {
		if ( ! $template = get_404_template() )
			$template = get_index_template();

		return $template;
	}

	/**
	 * Create the pdf if it does not exist. If the pdf creation
	 * fails do a 404 redirect. If the pdf creation is a success
	 * redirect to the pdf.
	 */
	private static function view_pdf() {
		global $post;

		if ( !post_type_supports( $post->post_type, 'print_pdf' ) )
			return self::get_404_template();

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

	public static function get_upload_basepath($post) {
		$dir = wp_upload_dir();
		$basepath = $dir['basedir'] . '/pdf/';
		return apply_filters('voce_post_pdfs_upload_basepath', $basepath, $post);
	}

	public static function get_upload_baseurl($post) {
		$dir = wp_upload_dir();
		$baseurl = $dir['baseurl'] . '/pdf/';
		return apply_filters('voce_post_pdfs_upload_baseurl', $baseurl, $post);
	}

	/**
	 * @param $post
	 * @param $overwrite boolean specify if the pdf should be overwritten, if it exists
	 * @return int a numer indicating the number of bytes written or FALSE on failure.
	 */
	 public static function save_pdf( $post, $overwrite = true ) {

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

		$basepath = self::get_upload_basepath($post);
		if( ! is_dir( $basepath ) )
			mkdir( $basepath, 0777, true );

		$filename = apply_filters('voce_post_pdfs_save_filename', $post->post_name . '.pdf');
		$file = $basepath . $filename;

		// check if pdf already exists
		if ( !$overwrite && file_exists( $file ) )
			return false;

		ob_start();
		$template_file = str_replace( TEMPLATEPATH, '', __DIR__ ) . DIRECTORY_SEPARATOR . self::TEMPLATE;
		load_template( locate_template( apply_filters( 'voce_post_pdf_print_template', $template_file ) ), false );
		$content = ob_get_clean();

		wp_reset_query();

		if( empty( $content ) )
			return;

		do_action( 'wp_load_dependency', 'dompdf/dompdf', 'dompdf_config.inc.php' );
		// generate the pdf
		$dompdf = new DOMPDF();
		$dompdf->load_html( $content );
		$dompdf->set_paper( 'letter', 'portrait' );
		$dompdf->render();

		// save the pdf
		return file_put_contents( $file, $dompdf->output() );
	}
}
add_action( 'init', array( 'Voce_Post_PDFS', 'initialize' ) );
