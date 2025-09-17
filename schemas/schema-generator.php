<?php
/**
 * Fast Schema.org JSON-LD Generator
 * 
 * High-performance Schema.org structured data generator using all optimization techniques.
 * Generates complete LocalBusiness schema with geographic areaServed data.
 * 
 * @author Optimized GeoAPI
 * @version 1.0.0
 */

// require_once 'StreamingGeoAPI.php';
require_once __DIR__ . '/geo-calculator.php';
class schemaGenerator {
    
    // Pre-defined schema template for performance
    private static $base_schema_template = [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "@id" => "",
        "name" => "",
        "legalName" => "",
        "alternateName" => "",
        "url" => "",
        "image" => "",
        "logo" => "",
        "description" => "",
        "telephone" => "",
        "email" => "",
        "address" => [
            "@type" => "",
            "streetAddress" => "",
            "addressLocality" => "",
            "addressRegion" => "",
            "postalCode" => "",
            "addressCountry" => ""
        ],
        "registerUrl" => "",
        "registeredAgent" => "",
        // beloy you can change the  order of
        // "contactPoint" => [],
        // "geo" => [],
        // "areaServed" => []
    ];





    
    // Wikipedia URL cache for performance
    private static $wikipedia_url_cache = [];
    
    /**
     * Validate and sanitize business information
     * 
     * @param array $business_info Business information to validate
     * @return array Validated and sanitized business information
     * @throws Exception If validation fails
     */
    private static function validateBusinessInfo($business_info) {
        // Define all required fields with their validation rules
        $required_fields = [
            'latitude' => 'coordinate',
            'longitude' => 'coordinate', 
            '@id' => 'url',
            'name' => 'text_only',           // Changed to text_only for stricter validation
            'legalName' => 'text_only',      // Changed to text_only
            'alternateName' => 'text_only',  // Changed to text_only
            'url' => 'url',
            'image' => 'url',
            'logo' => 'url',
            'description' => 'text',         // Allows more characters for descriptions
            'telephone' => 'phone',
            'email' => 'email',
            'address' => 'array',
            'registerUrl' => 'url',
            'registeredAgent' => 'boolean' // true or false
        ];
        
        $validated_info = [];
        
        // Validate each required field
        foreach ($required_fields as $field => $type) {
            // For boolean fields, allow missing/null values (they'll be converted to false)
            if (!isset($business_info[$field])) {
                if ($type === 'boolean') {
                    $business_info[$field] = null; // Set to null so boolean validation can handle it
                } else {
                    throw new Exception("Missing required business info field: {$field}");
                }
            }
            
            $value = $business_info[$field];
            
            // Check for empty values (but allow empty for boolean fields since they convert to false)
            if ($type !== 'boolean' && is_string($value) && trim($value) === '') {
                throw new Exception("Required field '{$field}' cannot be empty");
            }
            
            // Validate and sanitize based on type
            switch ($type) {
                case 'coordinate':
                    $validated_info[$field] = self::validateCoordinate($value, $field);
                    break;
                    
                case 'url':
                    $validated_info[$field] = self::validateUrl($value, $field);
                    break;
                    
                case 'string':
                    $validated_info[$field] = self::sanitizeString($value, $field);
                    break;
                    
                case 'text_only':
                    $validated_info[$field] = self::sanitizeTextOnly($value, $field);
                    break;
                    
                case 'text':
                    $validated_info[$field] = self::sanitizeText($value, $field);
                    break;
                    
                case 'phone':
                    $validated_info[$field] = self::validatePhone($value, $field);
                    break;
                    
                case 'email':
                    $validated_info[$field] = self::validateEmail($value, $field);
                    break;
                    
                case 'array':
                    if ($field === 'address') {
                        $validated_info[$field] = self::validateAddress($value);
                    }
                    break;
                case 'boolean':
                    // Handle non-scalar values (arrays, objects) - these are invalid
                    if (is_array($value) || is_object($value)) {
                        throw new Exception("Field '{$field}' must be a boolean value (true/false, 1/0, 'true'/'false', or empty for false). Got: " . var_export($value, true));
                    }
                    // Convert value to string for comparison if it's not already
                    $string_value = is_string($value) ? $value : (string)$value;
                    // Handle falsy values - convert to false
                    if ($value === null || $value === '' || trim($string_value) === '' || 
                        $value === 0 || $value === '0' || 
                        strtolower(trim($string_value)) === 'false' || 
                        $value === false) {
                        $validated_info[$field] = false;
                        // echo "Field '{$field}' is set to false.\n";
                    }
                    // Handle truthy values - convert to true
                    elseif ($value === 1 || $value === '1' || 
                            strtolower(trim($string_value)) === 'true' || 
                            $value === true) {
                        $validated_info[$field] = true;
                        // echo "Field '{$field}' is set to true.\n";
                    }
                    // Invalid boolean value
                    else {
                        throw new Exception("Field '{$field}' must be a boolean value (true/false, 1/0, 'true'/'false', or empty for false). Got: " . var_export($value, true));
                    }
                    break;

            }
        }
        
        return $validated_info;
    }
    
    /**
     * Validate coordinate values (latitude/longitude)
     */
    private static function validateCoordinate($value, $field) {
        // Convert to float
        $coord = filter_var($value, FILTER_VALIDATE_FLOAT);
        
        if ($coord === false) {
            throw new Exception("Invalid {$field}: must be a valid number");
        }
        
        // Validate coordinate ranges
        if ($field === 'latitude') {
            if ($coord < -90 || $coord > 90) {
                throw new Exception("Invalid latitude: must be between -90 and 90");
            }
        } elseif ($field === 'longitude') {
            if ($coord < -180 || $coord > 180) {
                throw new Exception("Invalid longitude: must be between -180 and 180");
            }
        }
        
        return $coord;
    }
    
    /**
     * Validate URL format
     */
    private static function validateUrl($value, $field) {
        // For URLs, use lighter sanitization to preserve valid URL characters
        $sanitized = self::sanitizeUrlSpecific($value, $field);
        
        if (!filter_var($sanitized, FILTER_VALIDATE_URL)) {
            throw new Exception("Invalid URL format for field '{$field}': {$sanitized}");
        }
        
        // Ensure HTTPS for security (optional - can be removed if HTTP is needed)
        if (!preg_match('/^https?:\/\//', $sanitized)) {
            throw new Exception("URL for field '{$field}' must start with http:// or https://");
        }
        
        return $sanitized;
    }
    
    /**
     * URL-specific sanitization (preserves valid URL characters)
     */
    private static function sanitizeUrlSpecific($value, $field) {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        // Remove PHP tags more thoroughly
        $sanitized = preg_replace('/\<\?.*?\?\>/s', '', $value);  
        
        // Remove other dangerous patterns
        $dangerous_patterns = ['<script', '</script>', 'javascript:', 'eval(', 'alert(', 'onload='];
        foreach ($dangerous_patterns as $pattern) {
            $sanitized = str_ireplace($pattern, '', $sanitized);
        }
        
        // Remove control characters but preserve URL-valid characters
        $sanitized = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $sanitized);
        
        // Remove quotes and backslashes that can break JSON
        $sanitized = str_replace(['"', "'", '\\'], '', $sanitized);
        
        // Clean up any double spaces or odd characters left behind
        $sanitized = preg_replace('/\s+/', '', $sanitized); // Remove spaces from URLs
        
        // Trim whitespace
        $sanitized = trim($sanitized);
        
        if ($sanitized === '') {
            throw new Exception("Field '{$field}' is empty after sanitization");
        }
        
        return $sanitized;
    }
    
    /**
     * Comprehensive security sanitization - removes all potentially dangerous content
     */
    private static function securitySanitize($value, $field) {
        if (!is_string($value)) {
            $value = (string) $value;
        }
        
        // First pass: Remove URL encoded characters that could hide malicious content
        $sanitized = urldecode($value);
        $sanitized = rawurldecode($sanitized);
        
        // Remove HTML entities that could hide malicious content
        $sanitized = html_entity_decode($sanitized, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Remove PHP code patterns using string replacement (safer than regex in arrays)
        $sanitized = str_replace('<?php', '', $sanitized);
        $sanitized = str_replace('<?=', '', $sanitized);
        $sanitized = str_replace('<?', '', $sanitized);
        $sanitized = str_replace('?>', '', $sanitized);
        $sanitized = str_ireplace('php:', '', $sanitized);
        $sanitized = str_ireplace('data:', '', $sanitized);
        $sanitized = str_ireplace('file:', '', $sanitized);
        
        // Remove JavaScript patterns
        $js_keywords = ['<script', '</script>', 'javascript:', 'onload=', 'onclick=', 'onerror=', 
                       'onmouseover=', 'onfocus=', 'onblur=', 'eval(', 'setTimeout(', 'setInterval(',
                       'alert(', 'confirm(', 'prompt(', 'document.', 'window.'];
        foreach ($js_keywords as $keyword) {
            $sanitized = str_ireplace($keyword, '', $sanitized);
        }
        
        // Remove HTML/XML tags
        $sanitized = strip_tags($sanitized);
        
        // Remove SQL injection patterns
        $sql_patterns = ['union select', 'select from', 'insert into', 'update set', 
                        'delete from', 'drop table', 'drop database', 'create table'];
        foreach ($sql_patterns as $pattern) {
            $sanitized = str_ireplace($pattern, '', $sanitized);
        }
        
        // Remove comment patterns
        $sanitized = preg_replace('/\/\*.*?\*\//s', '', $sanitized);  // /* ... */
        $sanitized = preg_replace('/\/\/.*$/m', '', $sanitized);      // // comments
        $sanitized = preg_replace('/--.*$/m', '', $sanitized);        // -- comments
        $sanitized = preg_replace('/#.*$/m', '', $sanitized);         // # comments
        
        // Remove command injection patterns
        $sanitized = str_replace(['`', '$(', '${', '|', '&&', '||', ';'], '', $sanitized);
        
        // Remove hash codes and percent-encoded characters
        $sanitized = preg_replace('/%[0-9A-Fa-f]{2}/', '', $sanitized);
        $sanitized = preg_replace('/#[0-9A-Fa-f]+/', '', $sanitized);
        
        // Remove dangerous characters that can break JSON or cause security issues
        $dangerous_chars = ['<', '>', '{', '}', '[', ']', '"', "'", '\\', '$', '&'];
        $sanitized = str_replace($dangerous_chars, '', $sanitized);
        
        // Remove all control characters and non-printable characters
        $sanitized = preg_replace('/[\x00-\x1F\x7F-\x9F]/', '', $sanitized);
        
        return $sanitized;
    }

    /**
     * Sanitize string values for JSON compatibility
     */
    private static function sanitizeString($value, $field) {
        // Apply security sanitization first
        $sanitized = self::securitySanitize($value, $field);
        
        // Trim whitespace
        $sanitized = trim($sanitized);
        
        // Check if empty after sanitization
        if ($sanitized === '') {
            throw new Exception("Field '{$field}' is empty after sanitization");
        }
        
        // Ensure valid UTF-8
        if (!mb_check_encoding($sanitized, 'UTF-8')) {
            throw new Exception("Field '{$field}' contains invalid UTF-8 characters");
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize text-only values (names, titles) - very strict
     */
    private static function sanitizeTextOnly($value, $field) {
        // Apply security sanitization first
        $sanitized = self::securitySanitize($value, $field);
        
        // Only allow letters, numbers, spaces, and very basic punctuation for names
        $sanitized = preg_replace('/[^a-zA-Z0-9\s\-\.\,\(\)\&\'\:\!]/', '', $sanitized);
        
        // Clean up multiple spaces
        $sanitized = preg_replace('/\s+/', ' ', $sanitized);
        
        // Trim whitespace
        $sanitized = trim($sanitized);
        
        // Check if empty after sanitization
        if ($sanitized === '') {
            throw new Exception("Field '{$field}' is empty after sanitization");
        }
        
        // Ensure valid UTF-8
        if (!mb_check_encoding($sanitized, 'UTF-8')) {
            throw new Exception("Field '{$field}' contains invalid UTF-8 characters");
        }
        
        return $sanitized;
    }
    
    /**
     * Sanitize text values (allows more characters than text-only)
     */
    private static function sanitizeText($value, $field) {
        // Apply security sanitization first
        $sanitized = self::securitySanitize($value, $field);
        
        // Allow more characters for descriptions but still be safe
        $sanitized = preg_replace('/[^a-zA-Z0-9\s\-\.\,\(\)\&\'\:\!\?\@\#\%\+\=\/]/', '', $sanitized);
        
        // Trim whitespace
        $sanitized = trim($sanitized);
        
        // Check if empty after sanitization
        if ($sanitized === '') {
            throw new Exception("Field '{$field}' is empty after sanitization");
        }
        
        // Ensure valid UTF-8
        if (!mb_check_encoding($sanitized, 'UTF-8')) {
            throw new Exception("Field '{$field}' contains invalid UTF-8 characters");
        }
        
        return $sanitized;
    }
    
    /**
     * Validate phone number format
     */
    private static function validatePhone($value, $field) {
        $sanitized = self::sanitizeString($value, $field);
        
        // Basic phone validation - allows various formats
        if (!preg_match('/^[\+]?[0-9\-\(\)\s\.]{7,}$/', $sanitized)) {
            throw new Exception("Invalid phone format for field '{$field}': {$sanitized}");
        }
        
        return $sanitized;
    }
    
    /**
     * Validate email format
     */
    private static function validateEmail($value, $field) {
        $sanitized = self::sanitizeString($value, $field);
        
        if (!filter_var($sanitized, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format for field '{$field}': {$sanitized}");
        }
        
        return $sanitized;
    }
    
    /**
     * Validate address structure
     */
    private static function validateAddress($address) {
        if (!is_array($address)) {
            throw new Exception("Address must be an array");
        }
        
        $required_address_fields = [
            'type' => 'text_only',
            'streetAddress' => 'text_only',
            'addressLocality' => 'text_only',
            'addressRegion' => 'text_only',
            'postalCode' => 'text_only',
            'addressCountry' => 'text_only'
        ];
        
        $validated_address = [];
        
        foreach ($required_address_fields as $field => $type) {
            if (!isset($address[$field])) {
                throw new Exception("Missing required address field: {$field}");
            }
            
            $value = $address[$field];
            
            if (is_string($value) && trim($value) === '') {
                throw new Exception("Required address field '{$field}' cannot be empty");
            }
            
            if ($type === 'text_only') {
                $validated_address[$field] = self::sanitizeTextOnly($value, "address.{$field}");
            } else {
                $validated_address[$field] = self::sanitizeString($value, "address.{$field}");
            }
        }
        
        // Validate postal code format (basic validation)
        if (!preg_match('/^[A-Za-z0-9\-\s]{3,10}$/', $validated_address['postalCode'])) {
            throw new Exception("Invalid postal code format: " . $validated_address['postalCode']);
        }
        
        // Validate country  (regular expression min 2 or more letters, text only, example US or United States should be valid)
        if (!preg_match('/^[A-Za-z\s]{2,}$/', $validated_address['addressCountry'])) {
            throw new Exception("Invalid country format: " . $validated_address['addressCountry']);
        }
        return $validated_address;
    }



    /**
     * Generate complete Schema.org LocalBusiness structure
     * 
     * @param array $business_info Business information
     * @param mixed $data_source Location data source
     * @param array $options Generation options
     * @return array Complete schema structure
     */
    public static function generateSchema($business_info, $data_source, $options = []) {
        $start_time = microtime(true);
        
        // Default options
        $options = array_merge([
            'include_geocircle' => true,     // add GeoCircle to areaServed
            'cache_wikipedia_urls' => true,  // cache Wikipedia URL generation
            'validate_schema' => true,      // validate output schema
            'optimize_for_seo' => false       // SEO optimizations
        ], $options);
        
        // Validate and sanitize business information
        $business_info = self::validateBusinessInfo($business_info);
        
        // Extract coordinates (already validated)
        $business_lat = $business_info['latitude'];
        $business_lng = $business_info['longitude'];
        
        // Direct distance calculation - no bounding box filtering
        $nearby_cities = [];
        foreach ($data_source as $location) {
            $distance = GeoCalculator::haversineDistance(
                $business_lat, $business_lng,
                $location['latitude'], $location['longitude']
            );
            
            if ($distance <= $options['search_radius']) {
                $location['distance'] = round($distance, 2);
                $nearby_cities[] = $location;
            }
        }
        
        // Sort by distance
        usort($nearby_cities, function($a, $b) {
            return $a['distance'] <=> $b['distance'];
        });
        
        // Limit results
        $nearby_cities = array_slice($nearby_cities, 0, $options['max_cities']);
        $performance_stats = ['strategy_used' => 'direct_calculation'];
        
        // Build base schema structure
        $schema = self::buildBaseSchema($business_info);
        
        // Generate areaServed section
        $schema['areaServed'] = self::buildAreaServed(
            $nearby_cities, 
            $business_lat, 
            $business_lng, 
            $options
        );
        
        // Apply SEO optimizations if requested
        if ($options['optimize_for_seo']) {
            $schema = self::applySeoOptimizations($schema, $nearby_cities);
        }
        
        // Validate schema if requested
        if ($options['validate_schema']) {
            $validation_result = self::validateSchema($schema);
            if (!$validation_result['valid']) {
                throw new Exception("Schema validation failed: " . $validation_result['error']);
            }
        }
        
        // Add generation metadata
        $generation_time = microtime(true) - $start_time;
        $schema['_meta'] = [
            'generated_at' => date('c'),
            'generation_time' => round($generation_time, 4),
            'cities_found' => count($nearby_cities),
            'search_radius' => $options['search_radius'],
            'performance' => $performance_stats
        ];
        
        return $schema;
    }
    
   



    
    /**
     * Build areaServed section with cities and optional GeoCircle
     * 
     * @param array $nearby_cities Nearby city data
     * @param float $business_lat Business latitude
     * @param float $business_lng Business longitude
     * @param array $options Generation options
     * @return array areaServed structure
     */
    private static function buildAreaServed($nearby_cities, $business_lat, $business_lng, $options) {
        $areaServed = [];
        
        // Add city entries
        foreach ($nearby_cities as $city) {
            $city_entry = [
                "@type" => "City",
                "name" => $city['city']
            ];
            
            // Add Wikipedia URLs if available
            $wikipedia_urls = self::generateWikipediaUrls($city['city'], $city['state']);
            if ($wikipedia_urls['city_url']) {
                $city_entry['sameAs'] = $wikipedia_urls['city_url'];
            }
            
            // Add state information
            $city_entry['containedInPlace'] = [
                "@type" => "AdministrativeArea",
                "name" => $city['state']
            ];
            
            if ($wikipedia_urls['state_url']) {
                $city_entry['containedInPlace']['sameAs'] = $wikipedia_urls['state_url'];
            }
            // ++++++++++++++++++++++++++++++++++++++++++++++++//
            // Add distance information as additional property
            // it is for testing and debugging purposes for now
            // Near featur You can add proprties for SEO
            // like population, demographics, etc.
            // ++++++++++++++++++++++++++++++++++++++++++++++++//
            // if (isset($city['distance'])) {
            //     $city_entry['distance'] = [
            //         "@type" => "QuantitativeValue",
            //         "value" => $city['distance'],
            //         "unitText" => "miles"
            //     ];
            // }
            
            $areaServed[] = $city_entry;
        }
        
        // Add GeoCircle if requested
        if ($options['include_geocircle'] && !empty($nearby_cities)) {
            $closest_city = $nearby_cities[0];
            
            $geocircle = [
                "@type" => "GeoCircle",
                "name" => $closest_city['city'] . " Area Service Zone",
                "description" => "Service area covering " . $closest_city['city'] . " and surrounding communities within " . $options['search_radius'] . " miles",
                "geoMidpoint" => [
                    "@type" => "GeoCoordinates",
                    "latitude" => $business_lat,
                    "longitude" => $business_lng,
                    "address" => [
                        "@type" => "PostalAddress",
                        "addressLocality" => $closest_city['city'],
                        "addressRegion" => $closest_city['state'],
                        "addressCountry" => "US"
                    ]
                ],
                "geoRadius" => strval($options['search_radius'] * 1609.34) // Convert miles to meters
            ];
            
            $areaServed[] = $geocircle;
        }
        
        return $areaServed;
    }
    
    /**
     * Generate Wikipedia URLs with caching
     * 
     * @param string $city City name
     * @param string $state State name
     * @return array URLs for city and state
     */
    private static function generateWikipediaUrls($city, $state) {
        $cache_key = $city . '|' . $state;
        
        if (isset(self::$wikipedia_url_cache[$cache_key])) {
            return self::$wikipedia_url_cache[$cache_key];
        }
        
        // Clean and format names for Wikipedia URLs
        $clean_city = self::cleanForWikipedia($city);
        $clean_state = self::cleanForWikipedia($state);
        
        $urls = [
            'city_url' => $clean_city ? "https://en.wikipedia.org/wiki/{$clean_city}_{$clean_state}" : null,
            'state_url' => $clean_state ? "https://en.wikipedia.org/wiki/{$clean_state}" : null
        ];
        
        self::$wikipedia_url_cache[$cache_key] = $urls;
        return $urls;
    }
    
    /**
     * Clean text for Wikipedia URL format
     * 
     * @param string $text Text to clean
     * @return string Cleaned text
     */
    private static function cleanForWikipedia($text) {
        // Remove special characters and replace spaces with underscores
        $clean = trim($text);
        $clean = str_replace(' ', '_', $clean);
        $clean = preg_replace('/[^a-zA-Z0-9_]/', '', $clean);
        return $clean;
    }
    
    /**
     * Apply SEO optimizations to schema
     * 
     * @param array $schema Schema structure
     * @param array $nearby_cities City data
     * @return array Optimized schema
     */
    private static function applySeoOptimizations($schema, $nearby_cities) {
        // Add comprehensive description with city names for SEO
        if (!empty($nearby_cities)) {
            $city_names = array_slice(array_column($nearby_cities, 'city'), 0, 5);
            $city_list = implode(', ', $city_names);
            
            if (strlen($schema['description']) > 0) {
                $schema['description'] .= " We proudly serve {$city_list} and surrounding areas.";
                echo "SEO description enhanced: " . $schema['description'] . "\n";
            }
        }
        
        // Add keywords as additionalProperty for SEO
        if (!empty($nearby_cities)) {
            $all_cities = array_column($nearby_cities, 'city');
            $states = array_unique(array_column($nearby_cities, 'state'));
            
            $schema['keywords'] = implode(', ', array_merge($all_cities, $states));
            echo "SEO keywords added: " . $schema['keywords'] . "\n";
        }
        
        return $schema;
    }
    
    /**
     * Validate generated schema structure
     * 
     * @param array $schema Schema to validate
     * @return array Validation result
     */
    private static function validateSchema($schema) {
        // Check required Schema.org properties
        $required_props = ['@context', '@type', 'name', 'address'];
        
        foreach ($required_props as $prop) {
            if (!isset($schema[$prop])) {
                return ['valid' => false, 'error' => "Missing required property: {$prop}"];
            }
        }
        
        // Validate address structure
        if (!isset($schema['address']['@type']) || $schema['address']['@type'] !== 'PostalAddress') {
            return ['valid' => false, 'error' => "Invalid address structure"];
        }
        
        // Validate areaServed structure
        if (isset($schema['areaServed']) && !is_array($schema['areaServed'])) {
            return ['valid' => false, 'error' => "areaServed must be an array"];
        }
        
        return ['valid' => true];
    }
    





 /**
     * Build base schema structure from business information
     * 
     * @param array $business_info Business details
     * @return array Base schema structure
     */
    private static function buildBaseSchema($business_info) {
        $schema = self::$base_schema_template;
        
        // Map business info to schema
        $schema['@id'] = $business_info['@id'] ?? '';
        $schema['name'] = $business_info['name'];
        $schema['legalName'] = $business_info['legalName'] ?? '';
        $schema['alternateName'] = $business_info['alternateName'] ?? '';
        $schema['url'] = $business_info['url'] ?? '';
        $schema['image'] = $business_info['image'] ?? '';
        $schema['logo'] = $business_info['logo'] ?? '';
        $schema['description'] = $business_info['description'] ?? '';
        $schema['telephone'] = $business_info['telephone'] ?? '';
        $schema['email'] = $business_info['email'] ?? '';
        
        // Map address information
        if (isset($business_info['address'])) {
            $address = $business_info['address'];
            $schema['address']['@type'] = $address['type'] ?? 'PostalAddress';
            $schema['address']['streetAddress'] = $address['streetAddress'] ?? '';
            $schema['address']['addressLocality'] = $address['addressLocality'] ?? '';
            $schema['address']['addressRegion'] = $address['addressRegion'] ?? '';
            $schema['address']['postalCode'] = $address['postalCode'] ?? '';
            $schema['address']['addressCountry'] = $address['addressCountry'] ?? '';
        }

        if (isset($business_info['registerUrl'])) {
            $registerUrl = $business_info['registerUrl'];
        }

        $schema['contactPoint'] = [
            [
                "@type" => "ContactPoint",
                "telephone" => $business_info['telephone'] ?? '',
                "email" => $business_info['email'] ?? '',
                "contactType" => "customer service",
                "availableLanguage" => ["English", "Spanish"]
            ]
        ];



        $schema['geo'] = [
            "@type" => "GeoCoordinates",
            "latitude" => $business_info['latitude'],
            "longitude" => $business_info['longitude']
        ];


        $schema['hasMap'] = $business_info['hasMap'] ?? '';
        $schema['priceRange'] = $business_info['priceRange'] ?? '$';
        $schema['currenciesAccepted'] = $business_info['currenciesAccepted'] ?? 'USD';
        $schema['paymentAccepted'] = $business_info['paymentAccepted'] ?? 'Credit Card, PayPal';
        $schema['openingHoursSpecification'] = $business_info['openingHoursSpecification'] ?? [
            [
                "@type" => "OpeningHoursSpecification",
                "dayOfWeek" => ["Monday", "Tuesday", "Wednesday", "Thursday", "Friday"],
                "opens" => "09:00",
                "closes" => "17:00"
            ]
        ];
        $schema['sameAs'] = $business_info['sameAs'] ?? [
            "https://www.facebook.com/PostScanMail",
            "https://x.com/postscanmail",
            "https://www.linkedin.com/company/postscan-mail"
        ];
        $schema['branchOf'] = $business_info['branchOf'] ?? [
            "@type" => "Organization",
            "name" => "PostScan Mail, Inc.",
            "url" => "https://www.postscanmail.com"
        ];
        $schema['mainEntityOfPage'] = $business_info['mainEntityOfPage'] ?? [
            "@type" => "WebPage", 
            "@id" => $business_info['@id'] ?? ''
        ];
    

        $has_certification = [
            "@type" => "Certification",
            "name" => "USPS Commercial Mail Receiving Agency (CMRA) Authorization",
            "certificationIdentification" => "CMRA-CA-2024-001",
            "certificationStatus" => "CertificationActive",
            "issuedBy" => [
                "@type" => "GovernmentOrganization",
                "name" => "United States Postal Service",
                "url" => "https://faq.usps.com/s/article/Commercial-Mail-Receiving-Agency-CMRA"
            ]
        ];
        $schema['hasCertification'] = $has_certification;


        // Parent Organization
        $parent_org = [
            "@type" => "Organization",
            "url" => "https://www.postscanmail.com",
            "image" => "https://www.postscanmail.com/wp-content/uploads/2019/03/Postscan-mail.jpg",
            "logo" => "https://www.postscanmail.com/wp-content/uploads/2020/05/cropped-cropped-fav-psm-192x192.png",
            "name" => "PostScan Mail, Inc.",
            "description" => "PostScan Mail is a leading provider of virtual mailbox and business address services, offering CMRA-compliant solutions for individuals and businesses.",
            "email" => "support@postscanmail.com",
            "telephone" => "+18006245866",
            "publishingPrinciples" => "https://www.postscanmail.com/terms-of-use.html",
            "ethicsPolicy" => "https://www.postscanmail.com/privacy.html",
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => "1950 W Corporate Way",
                "addressLocality" => "Anaheim",
                "addressCountry" => "USA",
                "addressRegion" => "CA",
                "postalCode" => "92801"
            ],
            "taxID" => "",
            "duns" => "",
            "naics" => "561499",
            "member" => [
                [
                    "@type" => "Organization",
                    "@id" => "https://www.bbb.org",
                    "name" => "Better Business Bureau",
                    "url" => "https://www.bbb.org"
                ],
                [
                    "@type" => "Organization",
                    "@id" => "https://www.usps.com",
                    "name" => "United States Postal Service - CMRA Network",
                    "url" => "https://faq.usps.com/s/article/Commercial-Mail-Receiving-Agency-CMRA"
                ]
            ],
            "aggregateRating" => [
                "@type" => "AggregateRating",
                "name" => "PostScan Mail Customer Reviews and Ratings",
                "url" => "https://www.shopperapproved.com/reviews/postscanmail.com",
                "ratingValue" => "4.8",
                "reviewCount" => "15384",
                "bestRating" => "5",
                "worstRating" => "1",
                "itemReviewed" => [
                    "@type" => "Organization",
                    "@id" => "https://www.postscanmail.com",
                    "name" => "PostScan Mail"
                ],
                "author" => [
                    "@type" => "Organization",
                    "name" => "Shopper Approved",
                    "url" => "https://www.shopperapproved.com"
                ]
            ],
                

        ];
        $schema['parentOrganization'] = $parent_org;

        // Area Served default to US if not provided
        $schema['areaServed'] = $business_info['areaServed'] ?? 'US';

        // KnowsAbout
        $knows_about = [
            [
                "@type" => "Thing",
                "name" => "Virtual Business Address Solutions",
                "description" => "Need a professional business address without renting office space? Our virtual mailbox gives your business a real street address that works for LLC registration, bank accounts, and official filings. Get CMRA-compliant services with digital mail management, Form 1583 processing, and USPS commercial receiving - all handled professionally so your business looks established from day one.",
                "url" => "https://www.postscanmail.com/services/virtual-business-address.html"
            ],
            [
                "@type" => "Thing", 
                "name" => "E-commerce and Dropshipping Solutions",
                "description" => "Running an online store but drowning in returns and vendor packages? We handle the messy parts - managing returns from multiple vendors, processing customer returns, storing inventory, and providing that crucial U.S. business address for your Amazon FBA or Shopify store. Cross-border shipping, package consolidation, everything an online business needs to run smoothly.",
                "url" => "https://www.postscanmail.com/client-solutions/dropshipping.html"
            ],
            [
                "@type" => "Thing",
                "name" => "International and Expat Services", 
                "description" => "Living abroad but need to stay connected to your U.S. life? We're your American address for everything important - tax documents from the IRS, Social Security checks, Medicare correspondence, and banking mail. Voter registration, immigration paperwork, insurance claims - we forward everything globally or scan it immediately so nothing gets lost while overseas.",
                "url" => "https://www.postscanmail.com/client-solutions/expats-international-users.html"
            ],
            [
                "@type" => "Thing",
                "name" => "Registered Agent Services",
                "description" => "Starting an LLC or corporation and need someone to receive legal documents? We serve as your official registered agent, handling lawsuits, subpoenas, and state notices professionally. Multi-state business registration, legal compliance, privacy protection - all with instant digital notifications when something important arrives that needs immediate attention.",
                "url" => "https://www.postscanmail.com/client-solutions/registered-agent.html"
            ],
            [
                "@type" => "Thing",
                "name" => "Small Business Mail Solutions",
                "description" => "Small business owner tired of using your home address everywhere? Get a professional image with real street addresses in prime business locations. Perfect for freelancers, solopreneurs, and growing teams who need credible addresses for Google Business profiles, business cards, and building commercial credit without expensive office leases.",
                "url" => "https://www.postscanmail.com/client-solutions/virtual-mailbox-for-small-business.html"
            ],
            [
                "@type" => "Thing",
                "name" => "Enterprise Digital Mailroom Solutions",
                "description" => "Enterprise handling thousands of mail pieces monthly? Our SOC 2 and HIPAA compliant digital mailroom processes up to 3,000 items with custom workflows for healthcare, finance, and legal industries. Support up to 30 users, advanced encryption, rigorous access controls - enterprise pricing from $100-$600/month scales with your volume.",
                "url" => "https://www.postscanmail.com/client-solutions/digital-mailroom.html"
            ],
            [
                "@type" => "Thing",
                "name" => "Shipping and Logistics Services",
                "description" => "Tired of expensive shipping costs eating your profits? We provide real-time quotes from USPS, FedEx, UPS, DHL, and ARAMEX so each package gets the best rate. Package consolidation saves money, international shipping compliance handled, holiday pricing protection - everything needed to optimize shipping costs and delivery reliability.",
                "url" => "https://www.postscanmail.com/services/package-forwarding.html"
            ],
            [
                "@type" => "Thing",
                "name" => "Digital Mail Management Technology",
                "description" => "Want your mail completely digital and accessible anywhere? Our SOC 2 compliant platform runs on AWS with 24/7 access, automated processing, and native mobile apps for iOS and Android. Real-time notifications, advanced encryption, digital archives, even mobile check deposit - comprehensive digital mail management from your pocket.",
                "url" => "https://www.postscanmail.com/services/virtual-mailbox.html"
            ]
        ];
        $schema['knowsAbout'] = $knows_about;

        // makesOffer
        $makes_offer =  [
            [
                "@type" => "Offer",
                "itemOffered" => [
                    "@type" => "Service",
                    "name" => "Virtual Mailbox",
                    "serviceType" => "Virtual Mailbox for Anyone",
                    "provider" => [
                        "@type" => "Organization",
                        "@id" => "https://www.postscanmail.com",
                        "name" => "PostScan Mail"
                    ],
                    "serviceOutput" => [
                        [
                            "@type" => "DigitalDocument",
                            "name" => "Can I Access My Mail Anytime?",
                            "description" => "Yes! You can access your personal digital mailbox anytime, anywhere. View photos of mail and packages instantly, decide to open and scan contents, forward to any address, or securely shred unwanted mail - all from your smartphone or computer."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Why Do I Need a Real Street Address?",
                            "description" => "You get a real U.S. street address (not a PO Box) that you can use for personal mail, online shopping, subscriptions, and services. Perfect when you're living in an apartment, moving frequently, traveling often, or want to keep your home address private."
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "How Do I Receive Packages?",
                            "description" => "You can receive packages from all carriers - USPS, UPS, FedEx, DHL - right at your virtual address. Then you choose: forward them to wherever you are in the world, combine multiple packages to save on shipping, or hold them for pickup whenever it's convenient for you."
                        ],
                        [
                            "@type" => "DigitalDocument",
                            "name" => "Need Digital Copies of Your Important Mail?",
                            "description" => "You never have to worry about losing important documents again. We scan your tax papers, insurance forms, bank statements, and government mail in high resolution. You can view them from anywhere and get instant notifications when something critical arrives."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Want to Keep Your Home Address Private?",
                            "description" => "You can keep your personal address completely secret while still receiving all your mail and packages. Perfect when you're shopping online, using dating apps, doing freelance work, or any time you don't want strangers knowing where you live."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Need a U.S. Address While Living Elsewhere?",
                            "description" => "You can keep a permanent U.S. address no matter where you are in the world. Whether you're traveling constantly, living abroad, or moving frequently, we forward your important documents, subscriptions, and government mail to wherever you are right now."
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "Want to Shop Online Without Delivery Problems?",
                            "description" => "You can shop from any U.S. retailer and send everything to your virtual address. We combine multiple packages to save you money, ship internationally, and store everything securely. No more missed deliveries or stolen packages from your doorstep."
                        ],
                        [
                            "@type" => "Product",
                            "name" => "Where to Find Personal Short-Term Storage?",
                            "description" => "You can secure storage for personal belongings, seasonal items, important documents, and packages. Flexible short-term or long-term storage."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Tired of Changing Your Address?",
                            "description" => "You can use your virtual address for magazine subscriptions, streaming services, bank accounts, and online services. When you move or travel, everything keeps coming to the same address - no more address change headaches."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "How Do You Know When Critical Mail Arrives?",
                            "description" => "You get instant notifications the moment important mail shows up - tax documents, legal notices, insurance letters, government correspondence. Then you can review it, forward it, or file it away with just one click."
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "Want to Consolidate Multiple Packages?",
                            "description" => "You can save money by having us combine multiple packages into one shipment. We'll hold your packages, repack them together, and send everything in one box to save you on shipping costs - especially helpful for international shipping."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Tired of Dealing with Junk Mail?",
                            "description" => "You can set up smart filters that automatically sort your mail before you even see it. Block specific senders, automatically recycle junk mail, or set certain mail to forward directly to another address - all without you having to lift a finger."
                        ]
                    ],
                    "audience" => [
                        [
                            "@type" => "Audience",
                            "audienceType" => "Individuals and Personal Mail Users",
                            "description" => "Personal consumers needing comprehensive virtual mailbox solutions with real street addresses for online shopping, subscriptions, and personal correspondence. You can access your mailbox 24/7 through our cloud-based platform, view scanned mail photos instantly, and choose to open-and-scan, forward worldwide, or securely shred unwanted items. Perfect for apartment dwellers avoiding package theft, frequent movers maintaining address continuity, and privacy-conscious individuals."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "U.S. Citizens Living Abroad and Expatriates",
                            "description" => "American expatriates requiring permanent U.S. mailing addresses for banking relationships, IRS correspondence, voting ballots, and Social Security communications. Our international mail forwarding service ensures you receive critical documents anywhere globally, with digital scanning for immediate access and secure forwarding for physical delivery. Essential for maintaining U.S. financial accounts, government benefits, and citizenship obligations while living overseas."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Foreign Residents and International Students",
                            "description" => "International students, visa holders, and foreign nationals establishing U.S. presence through legitimate street addresses for university applications, banking services, and government documentation. Receive mail from home countries, establish credit history, and manage educational correspondence through our multi-carrier package reception (USPS, UPS, FedEx, DHL) and professional mail scanning services."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Digital Nomads and Remote Workers",
                            "description" => "Location-independent professionals requiring stable virtual addresses for client communications, tax documents, and professional correspondence while traveling. Our automated mail management system provides 24/7 access to scanned mail, smart filtering to prioritize important documents, and flexible forwarding to current locations. Maintain professional credibility with a prestigious U.S. address regardless of travel schedule."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Seasonal Residents and Snowbirds",
                            "description" => "Seasonal residents managing mail between multiple residences through automated forwarding rules and intelligent mail sorting. Set up smart filters to forward only essential mail to your current location while storing non-urgent items for pickup or later forwarding. Perfect for snowbirds splitting time between northern and southern homes, with seamless mail management during transitions."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Privacy-Conscious Individuals",
                            "description" => "Security-focused individuals protecting personal information through confidential mail handling and secure document storage. Use our real street address for online purchases, dating apps, and service signups while keeping your home address private. Features encrypted digital storage, professional mail processing facilities, and complete control over mail handling decisions through our secure online platform."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Independent Professionals and Freelancers",
                            "description" => "Solo professionals establishing business credibility through prestigious virtual addresses without business registration requirements. Receive client payments, professional correspondence, and tax documents through our professional mail management system. Separate business and personal mail streams while maintaining a professional image with real street addresses in major business centers."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "College Students and Young Adults",
                            "description" => "University students managing mail continuity during academic years with frequent housing changes between dorms, apartments, and family homes. Receive financial aid documents, transcripts, family packages, and personal mail through our student-friendly virtual mailbox service. Parents can monitor important educational correspondence while students maintain independence through 24/7 digital access."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Urban Apartment Dwellers",
                            "description" => "City residents solving package theft and delivery issues through secure virtual mailbox reception at our staffed facilities. Receive packages from all carriers at real street addresses, avoid missed deliveries, and access package consolidation services for multiple orders. Perfect for high-rise apartments with delivery limitations and shared housing situations requiring secure mail management."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Seasonal and Migrant Workers",
                            "description" => "Mobile workforce following employment opportunities across regions and seasons, including agricultural workers, resort staff, and construction crews. Maintain consistent mail delivery for employment documents, tax forms, union correspondence, and family communications while moving between temporary work locations. Essential for receiving W-2s, benefits information, and maintaining permanent address requirements for employment verification."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Healthcare Professionals and Medical Staff",
                            "description" => "Medical professionals managing licensing renewals, continuing education certificates, and professional association correspondence through centralized virtual mailbox services. Critical for physicians, nurses, and healthcare workers maintaining multiple state licenses, receiving CME documentation, and managing regulatory compliance mail while working demanding hospital schedules or traveling assignments."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Travel Nurses and Medical Locum Workers",
                            "description" => "Healthcare professionals on temporary assignments requiring reliable mail management for credentialing documents, licensing boards, and professional correspondence. Our automated forwarding ensures critical medical licensing renewals, hospital credentialing packets, and professional communications reach you at current assignment locations nationwide, maintaining compliance across multiple healthcare facilities."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Real Estate Investors and Property Managers",
                            "description" => "Property investment professionals centralizing mail management for multiple rental properties, tenant communications, and investment documentation. Streamline property management operations by receiving rental applications, legal notices, HOA documents, and tenant correspondence at one virtual address. Organize investment-related mail through smart filtering and digital scanning for efficient property portfolio management."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Senior Citizens and Elderly Care Recipients",
                            "description" => "Older adults simplifying mail management through user-friendly virtual mailbox services with family oversight capabilities. Perfect for managing Medicare correspondence, Social Security communications, prescription deliveries, and insurance documents. Family members can access scanned documents to assist with healthcare decisions while seniors maintain independence through easy online mail management and automated forwarding services."
                        ]
                    ]
                ],
                "priceSpecification" => [
                    [
                        "@type" => "UnitPriceSpecification",
                        "price" => "10.00",
                        "priceCurrency" => "USD",
                        "unitCode" => "MON",
                        "unitText" => "monthly",
                        "billingIncrement" => 1,
                        "priceType" => "https://schema.org/Subscription"
                    ],
                    [
                        "@type" => "UnitPriceSpecification",
                        "price" => "100.00",
                        "priceCurrency" => "USD",
                        "unitCode" => "ANN",
                        "unitText" => "annually",
                        "billingIncrement" => 12,
                        "priceType" => "https://schema.org/Subscription"
                    ]
                ]
            ],
            [
                "@type" => "Offer",
                "itemOffered" => [
                    "@type" => "Service",
                    "name" => "Virtual Mailing Address for LLC",
                    "serviceType" => "Virtual Mailbox for small or large LLC businesses",

                    "provider" => [
                        "@type" => "Organization",
                        "@id" => "https://www.postscanmail.com",
                        "name" => "PostScan Mail"
                    ],
                    "serviceOutput" => [
                        [
                            "@type" => "DigitalDocument",
                            "name" => "24/7 Postal Mail Management",
                            "description" => "You can access your mailbox 24/7 and see photos of envelopes and packages. You're always in control and can decide when to open and digitized mail and when to forward, shred or recycle them."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Run Your Online Business",
                            "description" => "You can run your online business without a permanent address. PostScan Mail frees you from the hassles of paper processing, allowing you to manage your mail and packages online just as efficiently as you manage your business."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Virtual Business Address Service",
                            "description" => "Real U.S. street address (not a PO Box) for business registration, banking, and professional credibility that works for LLC registration, IRS use, and vendor verification."
                        ],
                        [
                            "@type" => "Product",
                            "name" => "Secure Storage Solutions",
                            "description" => "Physical storage for packages, inventory, personal items, and business materials with 7 days free storage."
                        ],
                        [
                            "@type" => "DigitalDocument",
                            "name" => "Mail Scanning & Digital Archive",
                            "description" => "High-resolution scans of mail contents, digital mail management, and secure document storage accessible from anywhere worldwide."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "International Expat Mail Services",
                            "description" => "Permanent U.S. mailing address for expats with global mail forwarding, banking correspondence, IRS notices, and Social Security check management."
                        ],
                        [
                            "@type" => "Service",
                            "name" => "Dropshipping Business Support",
                            "description" => "Business address for dropshipping operations, vendor correspondence management, return processing, and U.S. business presence maintenance."
                        ],
                        [
                            "@type" => "Product",
                            "name" => "Secure Business Storage Solutions",
                            "description" => "Physical storage for business inventory, marketing materials, equipment, and documents. Flexible pricing perfect for seasonal inventory and business equipment."
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "Mail Forwarding Service",
                            "description" => "Physical forwarding of mail to customer's preferred address"
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "Multi-Carrier Business Package Reception",
                            "description" => "Receive packages from USPS, UPS, FedEx, DHL, and ARAMEX with instant notifications. Package consolidation, international forwarding, and 7-day free storage. Perfect for e-commerce, dropshipping, and remote businesses."
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "Package Forwarding & Consolidation Service",
                            "description" => "Receive packages from all carriers (USPS, UPS, FedEx, DHL), consolidate multiple packages, and forward internationally with real-time shipping quotes."
                        ],
                        [
                            "@type" => "ParcelDelivery",
                            "name" => "E-commerce Fulfillment Support",
                            "description" => "Handle customer returns, vendor shipments, and inventory management. Specialized support for Amazon FBA, Shopify stores, and dropshipping operations with package inspection and forwarding services."
                        ]
                    ],
                    "audience" => [
                        [
                            "@type" => "Audience",
                            "audienceType" => "Legal Firms and Law Practices",
                            "description" => "Law firms managing confidential client communications, court documents, and time-sensitive legal notices through secure virtual mailbox services with 24/7 digital mail management. Access your mailbox anytime to view photos of envelopes and packages, decide when to open and scan legal documents, or securely shred confidential materials. Our CMRA-compliant facility ensures privileged attorney-client correspondence remains confidential with high-resolution mail scanning & digital archive, instant digital scanning alerts for urgent legal documents, and secure mail forwarding service to current case locations."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Real Estate Professionals and Property Managers",
                            "description" => "Real estate agents, brokers, and property managers operating across multiple locations requiring centralized mail management with 24/7 postal mail management for property listings, client contracts, and investment documentation. Run your online business without a permanent address while maintaining professional credibility through virtual business address service that works for LLC registration, IRS use, and vendor verification. Our secure storage solutions provide physical storage for marketing materials and documents with 7 days free storage, while mail forwarding service ensures property transactions reach you anywhere."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "E-commerce Businesses and Online Retailers",
                            "description" => "Online retailers leveraging e-commerce fulfillment support to handle customer returns, vendor shipments, and inventory management with specialized support for Amazon FBA, Shopify stores, and dropshipping operations. Access multi-carrier business package reception from USPS, UPS, FedEx, DHL, and ARAMEX with instant notifications, while package forwarding & consolidation service consolidates multiple packages and forwards internationally with real-time shipping quotes. Run your online business without a permanent address using our virtual business address service for professional credibility and secure business storage solutions for inventory management."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Freelancers and Independent Consultants",
                            "description" => "Solo professionals separating business and personal mail streams while establishing professional credibility through virtual business address service that works for business registration, banking, and professional credibility. Access 24/7 digital mail management to view photos of envelopes and packages instantly, decide when to open and scan client payments and contracts, or forward important documents during travel. Run your online business without a permanent address while maintaining mail scanning & digital archive for secure document storage accessible from anywhere worldwide."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Financial Services and Investment Firms",
                            "description" => "Financial advisors, investment firms, and financial consultants requiring secure document handling for client portfolios, regulatory compliance, and confidential financial correspondence. Access 24/7 postal mail management to view photos of sensitive financial documents instantly, with mail scanning & digital archive providing high-resolution scans and secure document storage accessible worldwide. Our CMRA-authorized facility ensures SEC and banking regulation compliance with virtual business address service for professional credibility, plus secure storage solutions for compliance documentation with flexible pricing"
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Marketing and Advertising Agencies",
                            "description" => "Creative agencies and marketing firms centralizing client communications, campaign materials, and vendor correspondence through 24/7 digital mail management. View photos of envelopes and packages instantly, decide when to open and scan promotional materials and campaign proofs, while secure business storage solutions provide physical storage for marketing materials, equipment, and documents. Run your online business efficiently with virtual business address service for professional credibility and mail forwarding service to reach remote agency teams regardless of location."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Non-Profit Organizations and Charities",
                            "description" => "Non-profit organizations operating with limited resources benefiting from cost-effective virtual business address service for donor correspondence, grant applications, and regulatory filings. Access 24/7 postal mail management to view photos of funding correspondence instantly, with mail scanning & digital archive providing immediate access to time-sensitive funding opportunities. Affordable monthly plans enable professional mailing addresses for fundraising campaigns, while secure storage solutions offer physical storage for volunteer materials and documents with 7 days free storage, reducing operational overhead."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Education and Coaching Services",
                            "description" => "Online educators, course creators, and business coaches establishing professional credibility through virtual business address service for student communications and course material distribution. Run your online business without a permanent address while managing student applications, certification documents, and educational materials through 24/7 digital mail management. Access mail scanning & digital archive for course enrollment paperwork and credential verification, with secure storage solutions providing physical storage for educational materials and equipment with flexible pricing perfect for seasonal inventory."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Tradespeople and Service Providers",
                            "description" => "Contractors, technicians, and mobile service providers requiring virtual business address service for licensing, permits, and customer communications without revealing personal home addresses. Access 24/7 postal mail management to view photos of work orders and permit documentation instantly, with mail forwarding service ensuring critical business correspondence reaches you at current job locations. Maintain professional image while utilizing secure storage solutions for equipment and business materials with 7 days free storage."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Digital Content Creators and Influencers",
                            "description" => "YouTubers, podcasters, social media influencers, and content creators needing virtual business address service separate from personal locations for brand partnerships, fan mail, and monetization paperwork. Access multi-carrier business package reception from USPS, UPS, FedEx, DHL, and ARAMEX with instant notifications for sponsor packages and collaboration materials. Run your online business while protecting personal privacy through 24/7 digital mail management, with secure storage solutions for merchandise and equipment storage perfect for seasonal inventory management."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Patent and Intellectual Property Professionals",
                            "description" => "IP attorneys, patent agents, and innovation consultants handling sensitive intellectual property documents requiring secure mail management and confidential document processing. Access 24/7 postal mail management to view photos of patent applications and trademark correspondence instantly, with mail scanning & digital archive providing high-resolution scans and secure document storage accessible worldwide. Our CMRA-compliant facility ensures immediate digital scanning notifications for time-sensitive patent office communications, while secure storage solutions protect confidential innovation documentation with professional-grade security."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Insurance Agents and Brokers",
                            "description" => "Insurance professionals operating in multiple markets requiring virtual business address service without physical presence for client policy management and carrier correspondence. Access 24/7 digital mail management to view photos of policy documents and claims correspondence instantly, with mail forwarding service enabling market expansion across diverse geographic regions. Streamline policy documentation and claims processing through mail scanning & digital archive, while maintaining licensing compliance with professional business addresses in key markets."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Import/Export Businesses and International Trade",
                            "description" => "International trading companies requiring reliable international expat mail services for customs documentation, shipping paperwork, and trade compliance correspondence. Access multi-carrier business package reception from USPS, UPS, FedEx, DHL, and ARAMEX with instant notifications, while package forwarding & consolidation service forwards internationally with real-time shipping quotes. Maintain U.S. business presence through virtual business address service for banking and vendor verification, with mail scanning & digital archive ensuring critical trade documents are accessible from anywhere worldwide."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Cryptocurrency and Fintech Startups",
                            "description" => "Blockchain companies, cryptocurrency businesses, and fintech startups requiring secure virtual business address service for regulatory compliance and banking relationships. Access 24/7 postal mail management to view photos of sensitive regulatory correspondence and banking documentation instantly, with mail scanning & digital archive providing secure document storage accessible worldwide. Run your online business while maintaining professional credibility for investor communications and establishing legitimate business presence required by financial institutions and regulatory authorities in the rapidly evolving fintech sector."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Online Course Creators and EdTech Entrepreneurs",
                            "description" => "Digital education entrepreneurs creating online courses, educational platforms, and e-learning businesses needing virtual business address service for student enrollment and certification processes. Run your online business without a permanent address while managing student correspondence and accreditation paperwork through 24/7 digital mail management. Access mail scanning & digital archive for educational licensing documentation, with secure storage solutions providing physical storage for course materials and educational equipment perfect for seasonal inventory management and institutional partnerships."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Dropshipping Entrepreneurs and E-commerce Retailers",
                            "description" => "Dropshipping businesses operating without physical inventory locations requiring dropshipping business support for vendor correspondence management, return processing, and U.S. business presence maintenance. Access e-commerce fulfillment support specialized for dropshipping operations with package inspection and forwarding services, while multi-carrier business package reception from USPS, UPS, FedEx, DHL, and ARAMEX provides instant notifications. Utilize package forwarding & consolidation service to optimize shipping costs for multiple vendor orders, with secure business storage solutions for inventory management perfect for seasonal inventory and business equipment."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Remote Customer Service Companies",
                            "description" => "Virtual call centers, customer support services, and remote customer service teams requiring virtual business address service for client contracts and operational correspondence. Run your online business without a permanent address while centralizing client communications and service agreements through 24/7 postal mail management. Access mail scanning & digital archive for operational documentation and B2B service contracts, maintaining professional credibility for remote-first customer service businesses without physical office locations."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Digital Marketing Consultants and SEO Specialists",
                            "description" => "Digital marketing experts, SEO consultants, and online advertising specialists serving clients across different geographic markets requiring virtual business address service for professional local presence in target markets. Run your online business while accessing 24/7 digital mail management to handle client contracts, campaign materials, and performance reports instantly. Utilize mail forwarding service for centralized mail management and secure storage solutions for marketing materials and equipment, enabling market expansion without physical office investments."
                        ],
                        [
                            "@type" => "Audience",
                            "audienceType" => "Subscription Box Services and Recurring Commerce",
                            "description" => "Subscription box businesses managing customer communications, returns processing, and vendor relationships through multi-carrier business package reception from USPS, UPS, FedEx, DHL, and ARAMEX with instant notifications. Access e-commerce fulfillment support to handle customer returns and vendor shipments efficiently, while package forwarding & consolidation service enables vendor sample coordination and subscriber return processing. Utilize secure business storage solutions for inventory management with 7 days free storage, perfect for seasonal inventory and business equipment, ensuring customer communications reach management teams regardless of operational location."
                        ]
                    ]
                ],
                "priceSpecification" => [
                    [
                        "@type" => "UnitPriceSpecification",
                        "price" => "10.00",
                        "priceCurrency" => "USD",
                        "unitCode" => "MON",
                        "unitText" => "monthly",
                        "billingIncrement" => 1,
                        "priceType" => "https://schema.org/Subscription"
                    ],
                    [
                        "@type" => "UnitPriceSpecification",
                        "price" => "100.00",
                        "priceCurrency" => "USD",
                        "unitCode" => "ANN",
                        "unitText" => "annually",
                        "billingIncrement" => 12,
                        "priceType" => "https://schema.org/Subscription"
                    ]
                ]
            ],
            [
                "@type" => "Offer",
                "itemOffered" => [
                    "@type" => "ParcelDelivery",
                    "name" => "Mail Package Consolidation Forwarding Service",
                    "provider" => [
                        "@type" => "Organization",
                        "name" => "PostScan Mail, Inc."
                    ],
                    "carrier" => [
                        "@type" => "Organization",
                        "name" => "USPS, UPS, FedEx, DHL, ARAMEX"
                    ],
                    "deliveryAddress" => [
                        [
                            "@type" => "PostalAddress",
                            "addressCountry" => "US",
                            "name" => "Domestic Shipping"
                        ],
                        [
                            "@type" => "PostalAddress",
                            "addressCountry" => "INTERNATIONAL",
                            "name" => "International Shipping"
                        ]
                    ]
                ]
            ]
        ];
         $schema['makesOffer'] = $makes_offer;


        // Add registered agent offer if applicable
        if ($business_info['registeredAgent'] ?? false) {
            $makes_offer[] = [
                "@type" => "Offer",
                "itemOffered" => [
                    "@type" => "Service",
                    "name" => "Business Registration Agent Service",
                    "serviceType" => "Registered Agent",
                    "description" => "Acting as official registered agent for business formation and compliance",
                    "provider" => [
                        "@type" => "Organization",
                        "@id" => "https://www.postscanmail.com",
                        "name" => "PostScan Mail"
                    ],
                    "serviceOutput" => [
                        [
                            "@type" => "GovernmentService",
                            "name" => "Service of Process Receipt",
                            "description" => "Professional receipt and forwarding of lawsuits, summons, subpoenas, and legal notices"
                        ],
                        [
                            "@type" => "DigitalDocument",
                            "name" => "Legal Document Scanning and Upload",
                            "description" => "Immediate scanning and secure upload of all legal notices with instant email alerts"
                        ],
                        [
                            "@type" => "GovernmentService",
                            "name" => "Government Compliance Notice Receipt",
                            "description" => "Receipt of tax documents, annual report reminders, and state regulatory communications"
                        ]
                    ],
                    "audience" => [
                        "@type" => "Audience",
                        "audienceType" => "Business Entities Requiring Registered Agent Services",
                        "description" => "LLCs, corporations, partnerships, and business entities requiring professional registered agent services for legal compliance and service of process receipt. Our CMRA-authorized facility ensures reliable receipt of lawsuits, summons, subpoenas, government notices, and regulatory correspondence with immediate digital scanning and secure document forwarding. Essential for businesses maintaining good standing, compliance deadlines, and professional legal representation without revealing personal addresses for business registration requirements."
                    ]
                ],
                "priceSpecification" => [
                    [
                        "@type" => "UnitPriceSpecification",
                        "price" => "10.00",
                        "priceCurrency" => "USD",
                        "unitCode" => "MON",
                        "unitText" => "monthly",
                        "billingIncrement" => 1,
                        "priceType" => "https://schema.org/Subscription"
                    ],
                    [
                        "@type" => "UnitPriceSpecification",
                        "price" => "100.00",
                        "priceCurrency" => "USD",
                        "unitCode" => "ANN",
                        "unitText" => "annually",
                        "billingIncrement" => 12,
                        "priceType" => "https://schema.org/Subscription"
                    ]
                ]
            ];
        $schema['makesOffer'] = $makes_offer;
        }





       

        // potentialAction
        $potential_action = [
            "@type" => "BuyAction",
            "name" => "Starter Plan",
            "description" => "Signup this location's virtual mailbox and address service online.",
            "actionStatus" => "https://schema.org/PotentialActionStatus",
            "target" => [
                "@type" => "EntryPoint",
                "urlTemplate" => $registerUrl,
                "inLanguage" => "en",
                "actionPlatform" => [
                    "http://schema.org/DesktopWebPlatform",
                    "https://schema.org/GenericWebPlatform",
                    "https://schema.org/AndroidPlatform",
                    "https://schema.org/IOSPlatform",
                    "http://schema.org/MobileWebPlatform"
                ]
            ],
            "price" => "10.00",
            "priceCurrency" => "USD"
        ];

        $schema['potentialAction'] = $potential_action;







        return $schema;
    }




    public static function generateSchemaJson($business_info, $data_source, $options = [], $pretty_print = true) {
        $schema = self::generateSchema($business_info, $data_source, $options);
        
        // Remove metadata for production output
        unset($schema['_meta']);
        
        $json_flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE;
        if ($pretty_print) {
            $json_flags |= JSON_PRETTY_PRINT;
        }
        
        return json_encode($schema, $json_flags);
    }
    
    /**
     * Quick schema generation with sensible defaults
     * 
     * @param string $business_name Business name
     * @param float $lat Latitude
     * @param float $lng Longitude
     * @param string $city Business city
     * @param string $state Business state
     * @param mixed $data_source Location data
     * @return string JSON schema
     */
    // public static function quickGenerate($business_name, $lat, $lng, $city, $state, $data_source) {
    //     $business_info = [
    //         'name' => $business_name,
    //         'latitude' => $lat,
    //         'longitude' => $lng,
    //         'address' => [
    //             'city' => $city,
    //             'state' => $state
    //         ]
    //     ];
    //     return self::generateSchemaJson($business_info, $data_source);
    // }
}