<?php
namespace CPTWEI_ExportTaxonmoy;
/**
 * Add taxonmoy to export
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}



class CPTWEI_ExportCustomTaxonmoy {
    private $taxonomies_id;
    private $taxonomies_name;
    private $tax_domain;
    private $content_data_filter;

    public function __construct($taxonomies_id, $taxonomies_name, $tax_domain = 'taxo')
    {
        $this->taxonomies_id = $taxonomies_id;
        $this->taxonomies_name = $taxonomies_name;
        $this->tax_domain = $tax_domain;

        // Add CSV columns name/Header Name for exporting extra data.
        add_filter('woocommerce_product_export_column_names', array($this, 'cptwei_add_columns'));
        add_filter('woocommerce_product_export_product_default_columns', array($this, 'cptwei_add_columns'));

        $this->content_data_filter = 'woocommerce_product_export_product_column_' . $this->taxonomies_id;
        // contents data column .
        add_filter($this->content_data_filter, array($this, 'cptwei_export_taxonomy'), 10, 2);
    }

    /**
     * Add CSV columns name for exporting extra data.
     *
     * @param array $columns
     * @return array  $columns
     */
    public function cptwei_add_columns($columns)
    {
        $columns[$this->taxonomies_id] = __($this->taxonomies_name, $this->tax_domain);
        return $columns;
    }

    /**
     * MnM contents data column content.
     *
     * @param mixed $value
     * @param WC_Product $product
     * @return mixed       $value
     */
    public function cptwei_export_taxonomy($value, $product)
    {

        $terms = get_the_terms($product->get_ID(), $this->taxonomies_id);
        $terms_name = '';
        if (!is_wp_error($terms)) {
            $data = array();
            $terms_name = join(', ', wp_list_pluck($terms, 'name'));

        }

        return $terms_name;
    }
}