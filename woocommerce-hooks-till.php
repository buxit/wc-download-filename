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
        add_filter('woocommerce_rest_prepare_product_object', array('Bux_Wc_Download_Filename', 'woocommerce_rest_prepare_product_object'), 10, 3);
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
    // woocommerce/includes/api/class-wc-rest-products-controller.php
    /**
     * Get the downloads for a product or product variation.
     *
     * @param WC_Product|WC_Product_Variation $product Product instance.
     * @return array
     */
    protected static function get_downloads( $product ) {
        $downloads = array();

        if ( $product->is_downloadable() ) {
            foreach ( $product->get_downloads() as $file_id => $file ) {
                $downloads[] = array(
                        //'id'   => $file_id, // MD5 hash.
                        'name' => $file['name'],
                        'file' => $file['file'],
                        );
            }
        }

        return $downloads;
    }
    protected static function get_attribute_taxonomy_name( $slug, $product ) {
        $attributes = $product->get_attributes();

        if ( ! isset( $attributes[ $slug ] ) ) {
            return str_replace( 'pa_', '', $slug );
        }

        $attribute = $attributes[ $slug ];

        // Taxonomy attribute name.
        if ( $attribute->is_taxonomy() ) {
            $taxonomy = $attribute->get_taxonomy_object();
            return $taxonomy->attribute_label;
        }

        // Custom product attribute name.
        return $attribute->get_name();
    }


    public static function woocommerce_rest_prepare_product_object($response, $object, $request) {
        if(is_array($response->data['meta_data']))
        {
            $md = array();
            foreach($response->data['meta_data'] as $m)
            {
                if(!preg_match('/^_oembed/', $m->get_data()['key']))
                {
                    $md[] = $m;
                }
            }
            $response->data['meta_data'] = $md;
        }
        unset($response->data['variations']);
        foreach($object->get_children() as $child_id)
        {
            $child = wc_get_product( $child_id );
            $attributes = array();
            $attr = $child->get_attributes();
            foreach($attr as $slug => $value) {
                $attributes[] = array( 'name' => Bux_Wc_Download_Filename::get_attribute_taxonomy_name($slug, $object), 'option' => $value);
            }
            $v = array(
                    'id'                    => $child->get_id(),
                    'sku'                   => $child->get_sku(),
                    'regular_price'         => $child->get_regular_price(),
                    'virtual'               => $child->is_virtual(),
                    'downloadable'          => $child->is_downloadable(),
                    'downloads'             => Bux_Wc_Download_Filename::get_downloads($child),
                    'in_stock'              => $child->is_in_stock(),
                    'attributes'            => $attributes,
                    //		'child'			=> var_export($child, true)
                    );
            if(!$child->is_downloadable()) {
                unset($v['downloads']);
            }
            $response->data['variations'][] = $v;
        }
        return $response;
    }
}

Bux_Wc_Download_Filename::initialize();
