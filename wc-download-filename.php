<?php
/*
Plugin Name: WC Download Filename
Plugin URI:  https://github.com/buxit/wc-download-filename
Description: Names the download file (in Content-Disposition) after the given name.
Version:     0.0.1
Author:      Till Busch
Author URI:  http://bux.at
*/

class Bux_Wc_Download_Filename
{
    /**
     * Initialise the hooks at plugin initialisation.
     */
    public static function initialize()
    {
        add_filter('woocommerce_file_download_filename', array('Bux_Wc_Download_Filename', 'get_filename'), 10, 2);
    }

    /**
     * Fetch a product filename.
     */
    public static function get_filename($filename, $product_id) {
	print_r($filename);
	$_product      = wc_get_product( $product_id );
	$_files = $_product->get_files();
	if(isset($_files[$_GET['key']]) && isset($_files[$_GET['key']]['name'])) {
		return $_files[$_GET['key']]['name'];
	}

        return $filename;
    }
}

Bux_Wc_Download_Filename::initialize();
