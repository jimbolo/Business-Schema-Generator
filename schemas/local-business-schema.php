<?php
/**
 * PostScan Mail Schema.org LocalBusiness Generator
 * 
 * Generates complete Schema.org LocalBusiness JSON-LD structured data for PostScan Mail 
 * virtual mailbox locations with dynamic geographic areaServed mapping.
 * 
 * Key Features:
 * - High-performance schemaGenerator with optimized geo calculations
 * - Dynamic areaServed generation based on nearby cities within configurable radius
 * - Complete LocalBusiness schema with virtual address service offerings
 * - Business data validation including boolean field handling
 * - Performance monitoring and error handling
 * - CMRA-compliant virtual mailbox service schema
 * 
 * Dependencies:
 * - geo-calculator.php (Optimized geographic distance calculations)
 * - schema-generator.php (Schema.org generator with geo calculations)
 * - geoapi-optimized.php (Pre-processed city/coordinate data)
 * - convert_data.php (Data optimization prerequisite)
 * - store-schema.php (Main schema generation script)
 * 
 * Output:
 * - Complete Schema.org LocalBusiness JSON-LD
 * - Geographic service area with nearby cities
 * - Virtual mailbox service offerings and pricing
 * - Performance metrics and validation results
 * 
 * @package PostScan Mail Single Store Schema Generator
 * @description Schema.org LocalBusiness Generator with Dynamic Geographic areaServed
 * @version 1.2.0
 * @license MIT
 * @author Cem Avsar
 * @since 2025-09-16
 * 
 * @example
 * php store-schema.php
 * // Generates store-schema-test.json with complete LocalBusiness schema
 * 
 * @uses schemaGenerator::generateSchemaJson()
 * @uses schemaGenerator::validateBusinessInfo()
 * @requires ./geoapi-production.php
 * @requires ./convert_data.php (prerequisite)
 * 
 * @see https://schema.org/LocalBusiness
 * @see https://developers.google.com/search/docs/appearance/structured-data/local-business
 */


// satrt timer
$_SERVER["REQUEST_TIME_FLOAT"] = microtime(true);


// Create Generic script to pull all the default WP post/page fields from database table
echo '<script type="text/javascript">' . "\n";
// function get_wp_post_fields_content() {
//     global $wpdb;
//     // get ID from current page
//     $post_id = get_the_ID();
//     $post = $wpdb->get_row("SELECT * FROM {$wpdb->posts} WHERE ID = $post_id", 'ARRAY_A');
//     return $post;
// }
// echo json_encode(get_wp_post_fields_content(), JSON_PRETTY_PRINT);
function get_acf_fields() {
    // Check if WordPress functions are available
    if (!function_exists('get_the_ID') || !function_exists('get_fields')) {
        return [];
    }
    
    // Check if ACF plugin is active
    if (!function_exists('get_fields')) {
        return [];
    }
    
    return get_fields(get_the_ID());
}
// map ACF fields with their content
function map_acf_fields() {
    $acf_fields = get_acf_fields();
    $mapped_fields = [];
    if (empty($acf_fields) || !is_array($acf_fields)) {
        return [];
    }
    foreach ($acf_fields as $field_name => $field_value) {
        $mapped_fields[$field_name] = $field_value;
    }
    return $mapped_fields;
}
// Only output if we're in a WordPress context
if (function_exists('get_the_ID') && function_exists('get_fields')) {
    echo json_encode(map_acf_fields(), JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'WordPress or ACF not available'], JSON_PRETTY_PRINT);
}
echo '</script>' . "\n";
// Map ACF fields to schema
$mapped_fields = map_acf_fields();



require_once __DIR__ . '/schema-generator.php';


try {
    // Step 1: Load production data
    define('GEO_API_ACCESS', true);
    
    // Check if production data exists
    if (!file_exists(__DIR__ . '/geoapi-production.php')) {
        echo "⚠️  Please first to create geo location data file.\n";
        echo "exp: [ 'city'=>'Frederiksted','state'=>'Virgin Islands','latitude'=>17.7122,'longitude'=>-64.8812,]\n";
        exit(1);
    }

    $locations = include __DIR__ . '/geoapi-production.php';

    // Step 2: Define business info for a specific PostScan Mail location
    // Modify these details for each location as needed
    // Use WP Custom Fields or Database to populate dynamically if needed
    $business_info = [
        // "mapbox_api_lat
        'latitude' => $mapped_fields['mapbox_api_lat'] ?? '',
        'longitude' => $mapped_fields['mapbox_api_lon'] ?? '',
        '@id' => ''. (get_permalink() ?? '') . '',
        'name' => 'PostScan Mail - ' . ($mapped_fields['mapbox_api_city'] ?? '') . ' Virtual Address and Mailbox',
        'legalName' => 'PostScan Mail', // Do not modify
        'alternateName' => 'PostScan Mail ' . ($mapped_fields['mapbox_api_city'] ?? '') . ' ' . ($mapped_fields['mapbox_api_state'] ?? ''), 
        'url' => ''. (get_permalink() ?? '') . '',
        'image' => 'https://www.postscanmail.com/logo.png', // Do not modify
        'logo' => 'https://www.postscanmail.com/logo.png', // Do not modify
        'description' => 'Need a professional virtual address in ' . ($mapped_fields['mapbox_api_city'] ?? '').'? PostScan Mail provides real street addresses (not PO Boxes) that work for business registration, LLC formation, and IRS compliance. Perfect for small businesses, freelancers, and remote workers who need a professional business presence without a physical office.',
        'telephone' => '+18006245866', // Do not modify
        'email' => 'support@postscanmail.com', // Do not modify
        'address' => [
            'type' => 'PostalAddress',// Do not modify
            'streetAddress' => '' . ($mapped_fields['mapbox_api_address'] ?? '') . '',
            'addressLocality' => '' . ($mapped_fields['mapbox_api_city'] ?? '') . '',
            'addressRegion' => '' . ($mapped_fields['mapbox_api_state'] ?? '') . '',
            'postalCode' => '' . ($mapped_fields['mapbox_api_zip_code'] ?? '') . '',
            'addressCountry' => '' . ($mapped_fields['mapbox_api_country'] ?? '') . ''
        ],
        'checkoutUrl' => ''. ($mapped_fields['mapbox_api_url'] ?? '') . '',
        'registeredAgent' => ($mapped_fields['mapbox_is_registered_agent'] ?? '')
    ];

    // Generate areaServed schema
    $schema_json = schemaGenerator::generateSchemaJson(
        $business_info, $locations,
        [
            'search_radius' => 10,
            'max_cities' => 2,

        ]
    );


// echo schema preview
// echo "✓ Schema generated successfully!\n";
// echo "Schema preview (first 500 characters):\n";
// echo substr($schema_json, 0, 500) . "...\n";

file_put_contents('local-business-schema.json', $schema_json);
echo "✓ Schema saved to local-business-schema.json\n\n";

// just embed the result in <script json-ld > tag and save to file
$schema_js = "";
$schema_js .= "<script type=\"application/ld+json\">\n";
$schema_js .= $schema_json;
$schema_js .= "\n</script>\n";

echo $schema_js;



    $end_time = microtime(true);
    $execution_time = $end_time - $_SERVER["REQUEST_TIME_FLOAT"];
    echo "Script execution time: " . round($execution_time, 2) . " seconds\n";
    echo "<br>";

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "\nMake sure to:\n";
    echo "1. Ensure geoapi-optimized.php and schema-generator.php and geo-calculator.php are exist in the parent directory\n";
    echo "2. Check file permissions for the schemas directory\n";
}
        // opcache_reset();
        // echo "oPcache cleared!";
?>