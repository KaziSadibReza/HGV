# Elementor Loop Grid AJAX Search Feature

## Overview

This feature adds an AJAX-powered search shortcode that integrates seamlessly with Elementor's Loop Grid widget. It allows users to search and filter loop grid content without page reloads, using the loop grid's own template.

## Features

- ✅ AJAX search functionality (no page reload)
- ✅ Integrates with Elementor Loop Grid using Query ID
- ✅ Search by keyword (post title, content)
- ✅ Search by location using custom meta field
- ✅ Uses Loop Grid's existing template (no custom template needed)
- ✅ Responsive design
- ✅ Loading indicator
- ✅ Customizable placeholders and button text

## Installation

The feature is automatically loaded via `functions.php`. The following files have been created:

1. `include/frontend/elementor-loop-search-shortcode.php` - Main shortcode logic
2. `assets/js/elementor-loop-search.js` - AJAX functionality
3. `assets/css/elementor-loop-search.css` - Styling

## Usage

### Basic Usage

```
[elementor_loop_search query_id="my_query_id"]
```

### With Location Search

```
[elementor_loop_search query_id="my_query_id" location_meta_key="location_field"]
```

### Full Example with All Parameters

```
[elementor_loop_search
    query_id="jobs_listing"
    location_meta_key="job_location"
    placeholder_search="Search jobs..."
    placeholder_location="Enter location..."
    button_text="Find Jobs"
    show_location="yes"
]
```

## Shortcode Parameters

| Parameter              | Required | Default       | Description                                |
| ---------------------- | -------- | ------------- | ------------------------------------------ |
| `query_id`             | Yes      | -             | The Query ID from your Elementor Loop Grid |
| `location_meta_key`    | No       | -             | The meta field key for location filtering  |
| `placeholder_search`   | No       | "Search..."   | Placeholder text for search input          |
| `placeholder_location` | No       | "Location..." | Placeholder text for location input        |
| `button_text`          | No       | "Search"      | Text for the search button                 |
| `show_location`        | No       | "yes"         | Show location field ("yes" or "no")        |

## Setup Instructions

### Step 1: Create Your Elementor Loop Grid

1. In Elementor, add a Loop Grid widget to your page
2. Configure your posts query (post type, filters, etc.)
3. Design your loop item template as desired

### Step 2: Set Query ID

1. In the Loop Grid widget settings, go to **Query**
2. Scroll down to **Query ID**
3. Enter a unique ID (e.g., "jobs_listing", "properties_grid", etc.)
4. Save your page

### Step 3: Add the Search Shortcode

1. Add a Shortcode widget or Text Editor above your Loop Grid
2. Insert the shortcode with your Query ID:
   ```
   [elementor_loop_search query_id="jobs_listing"]
   ```
3. If you want location search, add the meta key parameter:
   ```
   [elementor_loop_search query_id="jobs_listing" location_meta_key="job_location"]
   ```

### Step 4: Publish and Test

1. Update/Publish your page
2. Test the search functionality on the frontend
3. The Loop Grid should filter dynamically without page reload

## Example Use Cases

### Jobs Listing with Location

```
[elementor_loop_search
    query_id="jobs"
    location_meta_key="job_location"
    placeholder_search="Job title or keyword..."
    placeholder_location="City or state..."
    button_text="Search Jobs"
]
```

### Properties Listing

```
[elementor_loop_search
    query_id="properties"
    location_meta_key="property_location"
    placeholder_search="Search properties..."
    placeholder_location="Location..."
    button_text="Find Properties"
]
```

### Simple Blog Search (No Location)

```
[elementor_loop_search
    query_id="blog_posts"
    show_location="no"
    placeholder_search="Search articles..."
    button_text="Search"
]
```

## Customization

### Styling

Edit `assets/css/elementor-loop-search.css` to customize:

- Colors and fonts
- Layout and spacing
- Button styles
- Responsive breakpoints

### JavaScript Behavior

Edit `assets/js/elementor-loop-search.js` to:

- Enable live search (search as you type)
- Modify AJAX behavior
- Add custom animations
- Handle additional events

### Live Search

To enable search-as-you-type, uncomment line 89 in `elementor-loop-search.js`:

```javascript
// Change this:
// $form.submit();

// To this:
$form.submit();
```

## Troubleshooting

### Search Not Working

1. Verify the Query ID matches exactly between the shortcode and Loop Grid
2. Check browser console for JavaScript errors
3. Ensure jQuery is loaded on your site

### Location Search Not Working

1. Verify the meta key is correct
2. Check that your posts have the location meta field populated
3. Ensure the meta field contains searchable text values

### Styling Issues

1. Clear browser cache and site cache
2. Check that CSS file is being loaded (view page source)
3. Adjust CSS specificity if theme styles are overriding

### Loop Grid Not Updating

1. Verify the Query ID is set in both the shortcode and Loop Grid
2. Check that the Loop Grid is using a dynamic posts query (not manual selection)
3. Ensure JavaScript is enabled in the browser

## Technical Notes

- The shortcode uses WordPress AJAX (wp_ajax) for secure requests
- Search queries use WP_Query with standard WordPress query arguments
- Location search uses meta_query with LIKE comparison
- The feature preserves Elementor's Loop Grid template and styling
- Works with any post type supported by Elementor Loop Grid

## Browser Compatibility

- Chrome/Edge (latest)
- Firefox (latest)
- Safari (latest)
- Mobile browsers (iOS Safari, Chrome Android)

## Requirements

- WordPress 5.0+
- Elementor Pro (for Loop Grid widget)
- PHP 7.0+
- jQuery (included with WordPress)
