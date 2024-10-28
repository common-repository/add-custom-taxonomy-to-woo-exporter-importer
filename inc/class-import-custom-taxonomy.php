<?php
namespace CPTWEI_ImportTaxonmoy;
/**
 * Import
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

class CPTWEI_ImportCustomTaxonmoy
{
    private $taxonomies_id;
    private $taxonomies_name;
    private $tax_domain;

    public function __construct($taxonomies_id, $taxonomies_name, $tax_domain = 'taxo')
    {
        $this->taxonomies_id = $taxonomies_id;
        $this->taxonomies_name = $taxonomies_name;
        $this->tax_domain = $tax_domain;

        // Register the 'Custom Column' column in the importer.
        add_filter('woocommerce_csv_product_import_mapping_options', array($this, 'cptwei_map_columns'));

        // Add automatic mapping support for custom columns.
        add_filter('woocommerce_csv_product_import_mapping_default_columns', array($this, 'cptwei_add_columns_to_mapping_screen'));


        // Decode data items and parse string NAME.
        add_filter('woocommerce_product_importer_parsed_data', array($this, 'cptwei_parse_taxonomy_string'), 10, 2);

        //Set taxonomy.
        add_filter('woocommerce_product_import_inserted_product_object', array($this, 'cptwei_set_taxonomy'), 10, 2);
    }


    /**
     * Register the 'Custom Column' column in the importer.
     *
     * @param array $columns
     * @return array  $columns
     */
    function cptwei_map_columns($columns)
    {
        $columns[$this->taxonomies_id] = __($this->taxonomies_name, $this->tax_domain);
        return $columns;
    }

    /**
     * Add automatic mapping support for custom columns.
     *
     * @param array $columns
     * @return array  $columns
     */
    function cptwei_add_columns_to_mapping_screen($columns)
    {

        $columns[__($this->taxonomies_name, $this->tax_domain)] = $this->taxonomies_id;

        // Always add English mappings.
        $columns[$this->taxonomies_name] = $this->taxonomies_id;

        return $columns;
    }

    /**
     * Decode data items and parse string NAME.
     *
     * @param array $parsed_data
     * @param WC_Product_CSV_Importer $importer
     * @return array
     */
    function cptwei_parse_taxonomy_string($parsed_data, $importer)
    {

        if (!empty($parsed_data[$this->taxonomies_id])) {

            //$data = json_decode( $parsed_data[ 'products_trending_item' ], true );
            $data = explode(",", $parsed_data[$this->taxonomies_id]);
            unset($parsed_data[$this->taxonomies_id]);

            if (is_array($data)) {

                $parsed_data[$this->taxonomies_id] = array();

                // foreach ( $data as $term_id ) {
                // 	$parsed_data[ 'products_trending_item' ][] = $term_id;
                // }
                foreach ($data as $term_name) {
                    $term = get_term_by('name', $term_name, $this->taxonomies_id);
                    if ($term)
                        $parsed_data[$this->taxonomies_id][] = $term->term_id;
                }
            }
        }

        return $parsed_data;
    }

    /**
     * Set taxonomy.
     *
     * @param array $parsed_data
     * @return array
     */
    function cptwei_set_taxonomy($product, $data)
    {

        if (is_a($product, 'WC_Product')) {

            if (!empty($data[$this->taxonomies_id])) {
                wp_set_object_terms($product->get_id(), (array)$data[$this->taxonomies_id], $this->taxonomies_id);
            }

        }

        return $product;
    }
}