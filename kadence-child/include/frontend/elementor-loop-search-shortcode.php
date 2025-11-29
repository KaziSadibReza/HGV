<?php
/**
 * Elementor Loop Grid Search Filter
 * 
 * Simple search filter that integrates with Elementor Loop Grid
 * Searches in post title, content, and custom meta fields
 * 
 * Usage: [elementor_loop_search query_id="your_query_id" location_meta_key="location_field"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Loop_Search_Shortcode {
    
    private $location_meta_key = '';
    
    public function __construct() {
        // Add the search shortcode
        add_shortcode('elementor_loop_search', array($this, 'render_search_form'));
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Hook into Elementor query for query ID 6969 (add more as needed)
        add_action('elementor/query/6969', array($this, 'filter_elementor_query'), 10, 2);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'elementor-loop-search',
            get_stylesheet_directory_uri() . '/assets/css/elementor-loop-search.css',
            array(),
            '1.2.0'
        );
        
        wp_enqueue_script(
            'elementor-loop-search',
            get_stylesheet_directory_uri() . '/assets/js/elementor-loop-search.js',
            array('jquery'),
            '1.2.0',
            true
        );
        
        // Localize script for AJAX
        wp_localize_script('elementor-loop-search', 'elementorLoopSearchAjax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('elementor_loop_search_nonce')
        ));
    }
    
    /**
     * Render the search form
     */
    public function render_search_form($atts) {
        $atts = shortcode_atts(array(
            'query_id' => '',
            'location_meta_key' => '',
            'placeholder_search' => 'Search jobs by title or location...',
        ), $atts);
        
        if (empty($atts['query_id'])) {
            return '<p style="color: red;">Error: query_id parameter is required for elementor_loop_search shortcode.</p>';
        }
        
        // Store location_meta_key for use in filter
        if (!empty($atts['location_meta_key'])) {
            $this->location_meta_key = $atts['location_meta_key'];
        }
        
        ob_start();
        ?>
<div class="elementor-loop-search-wrapper">
    <form class="elementor-loop-search-form" method="GET">
        <!-- Job Search Container -->
        <div class="job-search-container">
            <div class="search-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <input type="text" name="search_keyword" class="job-search-input"
                placeholder="<?php echo esc_attr($atts['placeholder_search']); ?>" aria-label="Search"
                value="<?php echo esc_attr(isset($_GET['search_keyword']) ? $_GET['search_keyword'] : ''); ?>">
            <button type="submit" style="display: none;" aria-hidden="true"></button>
        </div>

        <input type="hidden" name="query_id" value="<?php echo esc_attr($atts['query_id']); ?>">
    </form>
</div>
<?php
        return ob_get_clean();
    }
    
    /**
     * Filter the Elementor query for widget ID 6969
     * Searches in post title, content, AND location meta fields
     */
    public function filter_elementor_query($query, $widget) {
        // Check if we have search parameter
        if (!isset($_GET['search_keyword']) || empty($_GET['search_keyword'])) {
            return;
        }
        
        $search_keyword = sanitize_text_field($_GET['search_keyword']);
        
        // Apply keyword search for title and content
        $query->set('s', $search_keyword);
        
        // Build meta query to search in location fields (like property filter)
        if (!empty($this->location_meta_key)) {
            $meta_query = array(
                'relation' => 'OR',
                // Search in location meta field
                array(
                    'key' => $this->location_meta_key,
                    'value' => $search_keyword,
                    'compare' => 'LIKE'
                ),
                // Add more location-related fields if needed
                // Similar to property filter searching in street, city, region, country
            );
            
            $query->set('meta_query', $meta_query);
        }
    }
    
}

// Initialize the shortcode
new Elementor_Loop_Search_Shortcode();