# Business Schema Generator

A high-performance PHP library that generates Schema.org LocalBusiness JSON-LD structured data with dynamic geographic coverage for local businesses and service companies.

## Features

- Generates Google-compliant Schema.org JSON-LD structured data
- Automatically discovers nearby cities within configurable radius
- High-performance geographic calculations using Haversine formula
- Complete LocalBusiness schema with address and contact information
- Dynamic areaServed generation with Wikipedia links
- Optimized for 36,177+ US locations

## Quick Start

1. Include the main file: `php wp-page.php`
2. Configure business data in `schemas/local-business-schema.php`
3. Generated test schema outputs to `local-business-schema.json`

## File Structure

```
├── wp-page.php                    # Main entry point
├── local-business-schema.json     # Generated test schema output
└── schemas/
    ├── local-business-schema.php  # Business data configuration
    ├── schema-generator.php       # Core schema generation
    ├── geo-calculator.php         # Geographic calculations
    └── geoapi-production.php      # Location database
```

## Requirements

- PHP 7.4+
- Valid business coordinates (latitude/longitude)

## Validation

Test your generated schema at: https://search.google.com/test/rich-results
