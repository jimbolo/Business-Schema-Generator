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
        'latitude' => 34.0910833,
        'longitude' => -118.3008766,
        '@id' => 'https://www.postscanmail.com/a/5101-santa-monica-blvd-ste-8.html', // ID ( Web Page URL)
        'name' => 'PostScan Mail - Los Angeles Virtual Address and Mailbox', // replace Los Angeles with City Name
        'legalName' => 'PostScan Mail', // Do not modify
        'alternateName' => 'PostScan Mail Los Angeles CA', // replace  Los Angeles with City & State Abbreviations
        'url' => 'https://www.postscanmail.com/a/5101-santa-monica-blvd-ste-8.html', // ID ( Web Page URL)
        'image' => 'https://www.postscanmail.com/logo.png', // Do not modify
        'logo' => 'https://www.postscanmail.com/logo.png', // Do not modify
        'description' => 'Need a professional virtual address in Los Angeles? PostScan Mail provides real street addresses (not PO Boxes) that work for business registration, LLC formation, and IRS compliance. Perfect for small businesses, freelancers, and remote workers who need a professional business presence without a physical office.', // replace Los Angeles with City Name
        'telephone' => '+18006245866', // Do not modify
        'email' => 'support@postscanmail.com', // Do not modify
        'address' => [ // do not modify
            'type' => 'PostalAddress',// Do not modify
            'streetAddress' => '5101 Santa Monica Blvd Ste 8', // insert Street Name
            'addressLocality' => 'Los Angeles', // insert City Name
            'addressRegion' => 'CA', // insert State Abbreviations
            'postalCode' => '90029', // insert Zip Code
            'addressCountry' => 'United States' // insert Country (US or United Sates)
        ],
        'registerUrl' => 'https://app.postscanmail.com/registration?plan=10037&store=505&address=1591', // add Starter Monthly plan link
        'registeredAgent' => true // Indicates if the business has a registered agent
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

file_put_contents('store-schema.json', $schema_json);
echo "✓ Schema saved to store-schema.json\n\n";

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