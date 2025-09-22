---
applyTo: '**'
---

# SCHEMA AGENT

# JSON-LD Schema.org Guidelines and Implementation

## **AGENTS FILE ANALYSIS PROTOCOL**

### **Mandatory File Investigation Standards**
When examining files referenced in these instructions, AGENTS must follow rigorous analysis protocols to avoid incorrect assumptions.

### **PROHIBITED Behaviors:**
- **MAKING ANY CHANGES PROHIBITED** until you list the changes on the chat first and confirm it with me.
- **ASSUMPTIONS PROHIBITED** about file contents based on initial tool responses
- **JUMPING TO CONCLUSIONS PROHIBITED** without thorough investigation
- **UNCLEAR TOOL RESULTS AS DEFINITIVE PROHIBITED**

### **REQUIRED Investigation Sequence:**
When encountering unclear file read results, AGENTS must systematically:

1. **Verify File Properties**
   - Check file size and existence status
   - Confirm file permissions and accessibility
   - Validate file path accuracy

2. **Implement Progressive Reading Strategy**
   - Use `offset` and `limit` parameters for large files
   - Read files in manageable chunks if truncation suspected
   - Cross-reference multiple read attempts to verify consistency

3. **Analyze Response Patterns**
   - Distinguish between truly empty files vs. tool limitations
   - Identify whitespace-only content vs. actual emptiness
   - Recognize potential encoding or formatting issues

4. **Document Investigation Results**
   - Report specific findings rather than assumptions
   - Acknowledge uncertainties explicitly when encountered
   - Request clarification when file analysis remains inconclusive

### **Quality Assurance Requirement**
AGENTS must demonstrate thorough file investigation before making any statements about file contents, structure, or availability. Incomplete analysis violates compliance protocols.

---

## AI Agent Resources
- `full-schema-hierarchy-min.html` - Reference for complete schema.org vocabulary hierarchy
- `schema-data-types-min.html` - Reference for all supported data types and constraints
- `schema-all-jsonld.jsonld` - Contains the definition of all terms in, all sections of, the vocabulary
- Search for specific schema examples and usage patterns use PUPPETEER MCP and FETCH MCP and visit `https://schema.org/docs/search_results.html?q={SEARCH_TERM}` urls and include them in your analysis. You may need to scroll subsequent pages to find the most relevant results
- **ALWAYS and MUST** Scrape and Fetch the complete Schema.org Type specification to ensure you are validating ALL mandatory and optional properties and list them on chat and verify against the source content with me.

---

## **1. AI AGENT COMPLIANCE DIRECTIVES**

### **1.1 Absolute Requirements**
- **NO GUESSING PERMITTED** - AGENTS must verify every schema property and type against official schema.org specifications before implementation
- **100% SCHEMA.ORG COMPLIANCE** - No approximations, interpretations, or "close enough" implementations allowed
- **JSON-LD EXCLUSIVE** - AGENTS must only generate JSON-LD format structured data
- **MANDATORY VALIDATION** - AGENTS must validate all generated markup using official tools before providing output

### **1.2 Schema Context Rules**
- **ALWAYS and MUST** Scrape and Fetch the complete Schema.org Type specification to ensure you are validating ALL mandatory and optional properties and list them on chat and verify against the source content with me.
- **ALWAYS** use `"@context": "https://schema.org"` - never HTTP, never custom contexts
- **NEVER** generate data-vocabulary.org markup (deprecated since April 2020)
- **NEVER** create custom schema extensions without explicit authorization
- **VERIFY** context usage against current schema.org standards

### **1.3 AI Agent Research Protocol**

# Schema.org Content Extraction Protocol for AGENTS

## Overview
This protocol enables AGENTS to systematically extract essential Schema.org vocabulary documentation for immediate implementation use, storing only actionable content in organized markdown files.

## Prerequisites
- Access to FETCH MCP tool
- Write permissions to `/data/` directory
- Target search terms or schema requirements identified

## Step-by-Step Extraction Process

### Step 1: Initial Schema Discovery
```bash
# Navigate to Schema.org search
URL: https://schema.org/docs/search_results.html?q={SEARCH_TERM}
```

**Actions Required:**
- Replace {SEARCH_TERM} with your target keyword
- Execute FETCH MCP to retrieve search results page
- Parse HTML to extract all schema type links
- Identify the most specific applicable schema type
- Filter out deprecated or experimental schemas

**Expected Output:**
- List of relevant schema type URLs
- Primary schema type selection
- Secondary related schema types (if applicable)

### Step 2: Core Schema Page Extraction
**For Primary Schema Type:**
- Navigate to main schema type page (e.g., https://schema.org/Product)
- Extract using FETCH MCP
- Parse and collect:
  - Schema type definition
  - Parent type hierarchy
  - All associated properties
  - Property value types and constraints
  - Available JSON-LD examples

**Content Filtering Rules:**
- ✅ Include: Active properties, parent relationships, working examples
- ❌ Exclude: Deprecated elements, experimental features, legacy documentation

### Step 3: Property Deep-Dive Extraction
**For Each Relevant Property:**
- Follow property links (e.g., https://schema.org/name)
- Extract detailed specifications:
  - Expected value types
  - Usage constraints
  - Inheritance relationships
  - Implementation examples

**Quality Criteria:**
- Property must be actively used in web standards
- Clear implementation value for AGENTS
- Complete specification available

### Step 4: JSON-LD Example Collection
**Target Sources:**
- Official schema.org example sections
- Complete, executable JSON-LD blocks
- Real-world implementation patterns
- Multi-scenario usage examples

**Validation Requirements:**
- JSON-LD must be syntactically valid
- Examples must be production-ready
- Cover common implementation scenarios

### Step 5: File Creation and Storage
**File Naming Convention:**
```
/data/{schema-type}-implementation.md
```

**Markdown Template:**
```markdown
# {Schema Type} Implementation Guide

## Schema Overview
- **Type:** {SchemaTypeName}
- **Parent:** {DirectParentType}
- **Status:** Active
- **Source URL:** {schema.org URL}
- **Extraction Date:** {Current Date}

## Essential Properties
### Required Properties
- **propertyName**: ExpectedValueType | Usage description

### High-Value Optional Properties
- **propertyName**: ExpectedValueType | Implementation benefit

## Implementation Examples

### Basic JSON-LD Structure
```json
{
  "@context": "https://schema.org",
  "@type": "SchemaType",
  "requiredProperty": "value",
  "optionalProperty": "value"
}
```

### Advanced Implementation
```json
{
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "@id": "https://www.postscanmail.com/a/5101-santa-monica-blvd-ste-8.html",
    "name": "PostScan Mail - Los Angeles Virtual Address and Mailbox",
    "legalName": "PostScan Mail",
    "alternateName": "PostScan Mail Los Angeles CA",
    "url": "https://www.postscanmail.com/a/5101-santa-monica-blvd-ste-8.html"
}
```

## Parent Relationships
- **Direct Parent:** ParentSchemaType
- **Root Parent:** Thing
- **Sibling Types:** RelatedSchemaType1, RelatedSchemaType2

## Implementation Notes
- Key implementation considerations
- Common pitfalls to avoid
- Best practices for AI agent usage

## Cross-References
- Related schema types for comprehensive implementation
- Dependent properties from parent schemas
- Extension opportunities
```

### Step 6: Quality Assurance Checklist
**Before Saving Each File:**
- [ ] Schema type is actively maintained (not deprecated)
- [ ] Properties have clear implementation value
- [ ] JSON-LD examples are complete and valid
- [ ] Content addresses specific AI agent requirements
- [ ] File size is under 10KB for optimal processing
- [ ] All URLs and references are functional

### Step 7: File Management Protocol
**Storage Rules:**
- Maximum 1 implementation file per schema type
- Overwrite existing files with updated content
- Maintain consistent naming convention
- Store only production-ready schemas

**Directory Structure:**
```
/data/
├── product-implementation.md
├── organization-implementation.md
├── person-implementation.md
└── localbusiness-implementation.md
```

### Error Handling
**Common Issues and Solutions:**
- 404 URLs: Log error, skip extraction, note in summary
- Deprecated Schemas: Flag for review, do not save
- Invalid JSON-LD: Correct syntax or exclude example
- Missing Properties: Note incomplete extraction in file header

### Validation Commands
**Post-Extraction Verification:**
```bash
# Verify file creation
ls -la /data/*.md

# Check file sizes
du -sh /data/*-implementation.md

# Validate JSON-LD syntax (if JSON validator available)
jsonlint /data/extracted-examples.json
```

### Success Metrics
- All relevant schema types successfully extracted
- JSON-LD examples validated and executable
- Property relationships accurately mapped
- Files optimized for AI agent consumption
- Zero deprecated or experimental content included

### Usage Notes for AGENTS
- Use extracted files as authoritative Schema.org reference
- Implement JSON-LD examples directly in applications
- Reference property specifications for validation rules
- Leverage parent relationships for inheritance planning
- Update extractions periodically to maintain currency

---

## **2. JSON-LD GENERATION REQUIREMENTS**

### **2.1 Script Generation Standards**
AGENTS generating schema markup must:
- Generate valid `<script type="application/ld+json">` tags only
- Ensure perfect JSON syntax (no trailing commas, proper escaping, double quotes)
- Validate JSON structure before output
- Include proper character encoding and escape sequences

### **2.2 Placement Decision Logic**
AGENTS must determine placement based on content type:
- **Page metadata**: Generate for `<head>` placement
- **Content-specific data**: Generate for `<body>` placement near relevant content
- **Site-wide data**: Generate for `<head>` placement on primary pages
- **Multiple entities**: Generate separate script blocks for distinct entities

### **2.3 Property Implementation Protocol**
For each schema implementation, AGENTS must:
- Include ALL required properties as defined in schema.org documentation
- Include recommended properties when source data is available
- Only include optional properties when data is accurate and relevant
- Ensure all property values match exact expected data types
- Implement proper nested object structures with correct @type declarations

---

## **3. VALIDATION ENFORCEMENT FOR AGENTS**

### **3.1 Mandatory Validation Sequence**
AGENTS must validate generated markup using this exact sequence:

**Step 1: Schema Markup Validator** (https://validator.schema.org/)
- Purpose: Verify complete schema.org compliance
- Requirement: ZERO errors before proceeding
- Usage: Test all generated markup regardless of intended use case

### **3.2 Error Response Protocol**
When validation fails, AGENTS must:
- **STOP generation** immediately upon detecting errors
- **IDENTIFY** specific error causes using validation tool feedback
- **RESEARCH** correct implementation using official documentation
- **REGENERATE** markup with corrections
- **REVALIDATE** until zero errors achieved
- **NEVER** provide non-validating markup to users

### **3.3 Quality Assurance Automation**
AGENTS must implement automated checks for:
- JSON syntax validation before output
- Required property completeness verification
- Data type constraint compliance
- URL accessibility and format validation
- Date format ISO 8601 compliance
- Enumeration value accuracy against schema.org lists

---

## **4. SCHEMA TYPE SELECTION ALGORITHM**

### **4.1 Type Selection Decision Tree**
AGENTS must follow this decision process:

**Step 1: Content Analysis**
- Parse source content to identify primary entity type
- Determine if content represents multiple entity types
- Identify all properties that can be extracted from content

**Step 2: Hierarchy Navigation**
- Access `full-schema-hierarchy-min.html` for complete type structure
- Identify most specific applicable type in hierarchy
- Verify type supports all required properties for available content
- Confirm type is not deprecated or in pending status

**Step 3: Property Validation**
- Cross-reference `schema-data-types-min.html` for property constraints
- Verify all available content matches expected property formats
- Confirm required properties can be populated from source content
- Validate optional properties enhance schema without introducing errors

**Step 4: JSON-LD vocabulary**
- Access `schema-all-jsonld.jsonld` for JSON-LD specific requirements
- Ensure all schema markup is expressed in JSON-LD format
- Use `@context` to define the schema.org namespace
- Include `@type` for all entities and properties

**Step 5: Implementation Verification**
- Generate schema markup using selected type
- Validate against all required validation tools
- Confirm markup accurately represents source content

### **4.2 Type Specificity Requirements**
AGENTS must always:
- Select the most specific applicable schema type available
- Avoid generic types when specific subtypes exist
- Use multiple @type values only when content legitimately fits multiple categories
- Research domain-specific extensions for specialized content types

### **4.3 Deprecated Type Avoidance**
AGENTS must never generate:
- Any schema types listed in schema.org/Attic
- ProfessionalService type (use specific business subtypes)
- Any pending schema types without explicit authorization
- Any data-vocabulary.org schema markup
- Custom or non-standard schema extensions

---

## **5. PROPERTY IMPLEMENTATION ALGORITHMS**

### **5.1 Required Property Algorithm**
For each schema implementation, AGENTS must:
1. **IDENTIFY** all required properties from schema.org documentation
2. **EXTRACT** corresponding data from source content
3. **VALIDATE** extracted data against property constraints
4. **FORMAT** data according to expected data types
5. **VERIFY** all required properties are included before generation

### **5.2 Data Type Enforcement Rules**
AGENTS must strictly enforce data typing:

**Text Properties:**
- Generate plain text or HTML only where explicitly permitted
- Escape special characters according to JSON standards
- Respect character limits where specified

**URL Properties:**
- Generate only absolute URLs with valid protocols
- Prefer HTTPS over HTTP where possible
- Validate URL accessibility when possible

**Numeric Properties:**
- Generate numeric values without quotation marks
- Respect decimal precision requirements
- Use appropriate number formats (integer vs. float)

**Date Properties:**
- Generate ISO 8601 format dates exclusively
- Include timezone information when available
- Use appropriate precision (date vs. datetime)

**Boolean Properties:**
- Generate JSON boolean values (true/false)
- Never use string representations of booleans

**Enumeration Properties:**
- Use exact enumeration values from schema.org documentation
- Never approximate or substitute enumeration values
- Validate against current enumeration lists

### **5.3 Nested Object Generation Protocol**
When generating nested objects, AGENTS must:
- Include proper @type declarations for all nested entities
- Implement complete property sets for nested types
- Use @id references for external entity relationships
- Maintain logical consistency across object relationships
- Validate nested structures independently

---

## **6. AI AGENT QUALITY CONTROL PROTOCOLS**

### **6.1 Pre-Output Validation Checklist**
Before providing schema markup, AGENTS must verify:
- [ ] Schema type is most specific applicable type from official hierarchy
- [ ] All required properties included per schema.org specifications
- [ ] All property values conform to expected data types and constraints
- [ ] Generated markup passes Schema Markup Validator with zero errors
- [ ] Generated markup passes Google Rich Results Test with zero errors (when applicable)
- [ ] JSON-LD syntax validates without errors
- [ ] Schema content accurately represents source content
- [ ] No deprecated or pending schema types used
- [ ] All URLs are absolute and properly formatted
- [ ] All dates follow ISO 8601 format standards

### **6.2 Error Prevention Algorithms**
AGENTS must implement preventive measures:
- **Schema verification**: Cross-reference every property against current schema.org vocabulary
- **Type validation**: Confirm schema types exist and are not deprecated
- **Data constraint checking**: Validate all values against property constraints
- **Syntax validation**: Parse JSON for syntax errors before output
- **Content matching**: Ensure schema represents visible content accurately

### **6.3 Continuous Compliance Monitoring**
AGENTS must maintain compliance by:
- **Vocabulary updates**: Monitor schema.org for vocabulary changes and updates
- **Deprecation tracking**: Check for newly deprecated types and properties
- **Validation tool updates**: Adapt to changes in official validation tools
- **Best practice evolution**: Incorporate new implementation guidelines
- **Error pattern analysis**: Learn from validation failures to prevent recurrence

---

## **7. AI AGENT RESPONSE PROTOCOLS**

### **7.1 Successful Implementation Response**
When generating valid schema markup, AGENTS must:
- Provide complete, validated JSON-LD markup
- Include placement recommendations (head vs. body)
- Confirm validation status against all required tools
- Document any optional properties included or excluded
- Explain schema type selection rationale when relevant

### **7.2 Implementation Failure Response**
When unable to generate valid markup, AGENTS must:
- Clearly state inability to create compliant schema
- Identify specific barriers to implementation (missing required data, unsupported content type, etc.)
- Suggest alternative approaches or additional data requirements
- Never provide invalid or non-compliant markup as workaround
- Offer to research specific schema requirements if needed

### **7.3 Validation Failure Response**
When generated markup fails validation, AGENTS must:
- Report validation failures immediately
- Identify specific errors from validation tools
- Regenerate markup addressing all identified issues
- Revalidate until zero errors achieved
- Never compromise on validation requirements

---

## **8. AI AGENT RESEARCH AND REFERENCE PROTOCOLS**

### **8.1 Documentation Research Requirements**
AGENTS must always:
- Reference official schema.org documentation as primary source
- Use provided `full-schema-hierarchy-min.html` for type selection
- Use provided `schema-data-types-min.html` for property constraint verification
- Use provided `schema-all-jsonld.jsonld` for JSON-LD specific requirements
- Cross-reference multiple sources for complex implementations
- Verify current status of schema types and properties

### **8.2 Knowledge Update Protocols**
AGENTS must maintain current knowledge by:
- Recognizing when schema.org vocabulary may have updated
- Requesting current documentation when knowledge cutoff limitations apply
- Acknowledging uncertainty rather than generating potentially outdated markup
- Researching current best practices for complex implementation scenarios

### **8.3 Uncertainty Handling**
When facing uncertainty, AGENTS must:
- Explicitly state knowledge limitations
- Request additional information rather than approximating
- Suggest manual verification of complex or edge cases
- Provide researched alternatives when primary approach is uncertain
- Never generate schema markup without confidence in accuracy

---

## **COMPLIANCE ENFORCEMENT STATEMENT**

**AGENTS operating under these guidelines are bound by absolute compliance requirements:**

1. **ZERO TOLERANCE** for non-compliant markup generation
2. **MANDATORY VALIDATION** using all specified tools before output
3. **STRICT ADHERENCE** to schema.org vocabulary and specifications  
4. **IMMEDIATE CORRECTION** of any validation failures
5. **CONTINUOUS LEARNING** from validation feedback and errors

**Any AI agent generating schema markup that fails validation or deviates from these guidelines has failed its directive and must immediately correct course. No exceptions or compromises are permitted in schema.org compliance.**