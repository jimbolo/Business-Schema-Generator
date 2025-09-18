# WP Geo - Schema.org LocalBusiness Generator

A high-performance PHP library that automatically generates complete Schema.org LocalBusiness JSON-LD with dynamic geographic coverage. Perfect for local businesses, real estate, and service companies needing professional SEO structured data.

## Why WP Geo?

🎯 **Instant SEO Boost** - Generate Google-compliant structured data in seconds  
🗺️ **Smart Geographic Coverage** - Automatically finds and includes nearby cities in your service area  
⚡ **High Performance** - Optimized algorithms process 36,177+ US locations instantly  
🔧 **Flexible Integration** - Works standalone, with WordPress/ACF, or as simple theme include  
✅ **Google Validated** - Generates Schema.org compliant JSON-LD that passes Google's Rich Results Test  

## What You Get

Your generated schema includes:
- **Complete Business Information** - Name, address, contact details, coordinates
- **Dynamic Service Area** - Automatically discovers nearby cities within your radius
- **SEO-Optimized Data** - Wikipedia links, proper Schema.org formatting
- **Professional Output** - 60,000+ character JSON-LD with 30+ nearby locations
- **Instant Results** - Generation completes in ~0.14 seconds

## Choose Your Implementation

### 🚀 **Option 1: Standalone** (Best for developers & testing)
Perfect for: CLI usage, development, non-WordPress sites, manual data management

**Pros:** Simple setup, no dependencies, full control  
**Cons:** Manual data entry for each location  



---

## Complete Implementation Guide

### Option 1: Standalone Implementation

**When to Use:**
- ✅ Testing and development
- ✅ CLI/server-side processing  
- ✅ Non-WordPress environments
- ✅ One-time schema generation
- ✅ Manual data management

**Setup:**
1. Upload files to your server
2. Ensure `schemas/geoapi-production.php` exists
3. Edit business data in `schemas/store-schema.php`

**Usage:**
```bash
# Navigate to schemas folder
cd schemas/

# Edit business information in store-schema.php
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
    'email' => 'info@business.com',
    'registerUrl' => 'https://your-registration-url.com',
    'registeredAgent' => true
];

# Generate schema
# View output in store-schema.json or store-schema.json-ld
php home.php
```


**How It Works:**
1. ✅ **Auto-Detection** - Only runs on posts with ACF fields
2. ✅ **Silent Operation** - Won't break page if fields missing  
3. ✅ **Clean Output** - Generates `<script type="application/ld+json">`
4. ✅ **Zero Maintenance** - Works automatically once set up

---

## Technical Specifications

### Performance Metrics
- **Execution Time:** ~0.14 seconds
- **Output Size:** 60,000+ characters  
- **Geographic Coverage:** 30+ nearby cities
- **Location Database:** 36,177+ US locations
- **Memory Usage:** Minimal overhead with production algorithms

### Core Components
- **Geographic Calculator** - Optimized Haversine distance formula
- **Schema Generator** - Complete LocalBusiness JSON-LD generation  
- **Location Database** - Pre-processed US city/coordinate data
- **Error Handling** - Comprehensive validation and logging

### File Structure
```
your-theme/
├── home.php                     # WordPress theme integration 
└── schemas/
    ├── store-schema.php          # Standalone implementation
    ├── schema-generator.php      # Core schema generation engine
    ├── geo-calculator.php        # Geographic distance calculations
    ├── geoapi-production.php      # Location database
```

### Sample Generated Schema
```json
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "@id": "https://your-website.com/location",
    "name": "Your Business - City Virtual Address",
    "address": {
        "@type": "PostalAddress",
        "streetAddress": "123 Main St",
        "addressLocality": "Los Angeles", 
        "addressRegion": "CA",
        "postalCode": "90029"
    },
    "areaServed": [
        {
            "@type": "City",
            "name": "Beverly Hills",
            "url": "https://en.wikipedia.org/wiki/Beverly_Hills,_California"
        }
    ],
    "geo": {
        "@type": "GeoCoordinates",
        "latitude": 34.0910833,
        "longitude": -118.3008766
    }
}
```

## Testing & Validation

### Test Your Implementation
```bash
# Option 1: Test standalone
php schemas/store-schema.php
```

### Validate Your Schema
1. Copy JSON from generated home.php output (see the browser source code)
2. Test at: https://search.google.com/test/rich-results
3. Verify all fields appear correctly
4. Check for validation warnings

## Requirements

### Minimum Requirements
- PHP 7.4+
- Valid business coordinates
- Location database (included: `geoapi-production.php`)

### WordPress Requirements  
- WordPress installation
- Advanced Custom Fields (ACF) plugin
- Configured ACF fields for business data
- WordPress post context for data collection

## Support & Contributing

### Getting Help
- Check the testing section for troubleshooting
- Review generated logs in `wp-acf-helper.log`  ((under construction))
- Validate schema output using Google's Rich Results Test

### License
Open source - see license file for details.

---

**Ready to boost your local SEO?** Choose your implementation above and get professional Schema.org structured data in minutes!
