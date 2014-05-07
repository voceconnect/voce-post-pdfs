Voce Post PDFs
==============

Contributors: johnciacia, kevinlangleyjr, brockangelo  
Tags: printing, pdf  
Requires at least: 3.2  
Tested up to: 3.8.3  
Stable tag: 1.2.1  
License: GPLv2 or later  
License URI: http://www.gnu.org/licenses/gpl-2.0.html  

A WordPress plugin/module that generates a pdf from a post.

## Description
This plugin generates a PDF file from a post's content using the PHP dompdf library. Simply link to the post + '/pdf/' and a PDF will be generated. You have the ability to customize the upload path, the PDF template that is used, and the filename format through filters.

## Installation

### As standard plugin:
> See [Installing Plugins](http://codex.wordpress.org/Managing_Plugins#Installing_Plugins).

### As theme or plugin dependency:
> After dropping the plugin into the containing theme or plugin, add the following:
```php
if( ! class_exists( 'Voce_Post_PDFS' ) ) {
	require_once( $path_to_vpp . '/voce-post-pdfs.php' );
}
```

## Usage

Add a Download PDF link to `/pdf/`:
```php
$pdf_link = esc_url( trailingslashit( get_permalink() ) . 'pdf/' );
``` 

### Customize with filters

**To modify the logo used in the PDF header:**
```php
add_action( 'voce_post_pdfs_logo', function(){ 
  do_action('newsroom_custom_header_logo');
});
```
**Use your own template for the PDF, relative to the theme directory:**
```php
add_filter('voce_post_pdf_print_template', function($template){
  return 'print.php';
});
```

**Modify query args:**
```php
add_filter('voce_post_pdfs_save_query_args', function($args){
  if( isset( $_GET['lang'] ) )
     $args['lang'] = $_GET['lang'];
  return $args;
});
```

**Modify the default filename format (defaults to post-title.pdf):**
```php
add_filter('voce_post_pdfs_save_filename', function($filename) {
  $lang = ( isset( $_GET['lang'] )  ? '-' . sanitize_key( $_GET['lang'] ) : '');
  $filename = basename($filename, '.pdf');
  return $filename . $lang . '.pdf';
});
```

**Modify the upload location on the server:**
```php
add_filter('voce_post_pdfs_upload_basepath', function($basepath, $post){
  $uploads = wp_upload_dir();
  $basepath = $uploads['basedir'] . '/' . date('Y', strtotime($post->post_date) ) . '/' . date('m', strtotime($post->post_date) ) . '/pdf/';                
  return $basepath;
}, 10, 2);
```

**Modify the url to the pdf:**
```php
add_filter('voce_post_pdfs_upload_baseurl', function($baseurl, $post){
  $uploads = wp_upload_dir();
  $baseurl = $uploads['baseurl'] . '/' . date('Y', strtotime($post->post_date) ) . '/' . date('m', strtotime($post->post_date) ) . '/pdf/';                
  return $baseurl;
}, 10, 2);
```

# Changelog

**1.2.1**  
*Reorganizing the path declarations and pdf existance check in `save_post` to improve performace.*

**1.2**  
*Adding a parameter to `save_pdf` to not overwrite the PDF, if the PDF already exists. `get_upload_basepath` and `get_upload_baseurl` are now public, so other plugins can retrieve a PDF's location.*
