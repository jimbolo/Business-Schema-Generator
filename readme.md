# WP Geo - Schema.org LocalBusiness Generator

A high-performance PHP library for generating Schema.org LocalBusiness JSON-LD with geographic area coverage.

## Features

- **Fast Geolocation Calculations** - Optimized Haversine distance formula
- **Schema.org Compliance** - Complete LocalBusiness structured data
- **Geographic Area Coverage** - Automatic areaServed generation with nearby cities
- **Data Validation** - Comprehensive input sanitization and validation
- **Performance Optimized** - Pre-calculated constants and efficient algorithms

## Core Components

### Geographic Calculator (`geo-calculator.php`)
- Haversine distance calculations between coordinates
- Batch distance processing for multiple locations
- Fast sorting algorithms for proximity searches

### Schema Generator (`schema-generator.php`)
- Complete Schema.org LocalBusiness JSON-LD generation
- Automatic areaServed population with nearby cities
- Wikipedia URL generation for enhanced SEO
- Comprehensive business data validation

### Location Data (`geoapi-optimized.php`)
- Pre-processed location database (36,177+ US locations)
- Optimized for fast loading and range queries
- Sorted by latitude for efficient geographic searches

## Quick Start

```php
require_once 'schemas/geo-calculator.php';
require_once 'schemas/schema-generator.php';

// Business information
$business_info = [
    'name' => 'Your Business Name',
    'latitude' => 34.0910833,
    'longitude' => -118.3008766,
    'address' => [
        'streetAddress' => '123 Main St',
        'addressLocality' => 'Los Angeles',
        'addressRegion' => 'CA',
        'postalCode' => '90029',
        'addressCountry' => 'US'
    ],
    'telephone' => '555-123-4567',
    'email' => 'info@business.com'
];

// Generate schema
$options = [
    'search_radius' => 30,  // miles
    'max_cities' => 25
];

$schema = schemaGenerator::generateSchemaJson($business_info, $location_data, $options);
echo $schema;
```

## Example Output

Generates complete Schema.org LocalBusiness with:
- Business details and contact information
- Geographic coordinates and address
- Area served with nearby cities and Wikipedia links
- GeoCircle for service area definition
- SEO-optimized structured data

## Performance

- **Distance Calculations**: Optimized Haversine formula with pre-calculated constants
- **Memory Efficient**: Minimal overhead for large location datasets
- **Fast Queries**: Geographic sorting enables efficient range searches
- **Validation**: Comprehensive data sanitization without performance impact

## Use Cases

- WordPress websites with local business listings
- Multi-location business directories
- Geographic service area mapping
- SEO optimization with structured data
- Local search enhancement

## Requirements

- PHP 7.4+
- Location data source (included)
- Valid business coordinates

## License

Open source - see license file for details.
