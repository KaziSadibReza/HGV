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
        
        // AJAX handler
        add_action('wp_ajax_elementor_loop_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_elementor_loop_search', array($this, 'ajax_search'));
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
            '1.3.0',
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
            // Get all possible location subfields from ACF
            $location_subfields = $this->get_location_subfields($this->location_meta_key);
            
            $meta_query = array(
                'relation' => 'OR',
            );
            
            // Search in each location subfield
            foreach ($location_subfields as $subfield) {
                $meta_query[] = array(
                    'key' => $subfield,
                    'value' => $search_keyword,
                    'compare' => 'LIKE'
                );
            }
            
            $query->set('meta_query', $meta_query);
        }
    }
    
    /**
     * Get all location subfields from ACF field structure
     * Similar to how property filter gets field structure
     */
    private function get_location_subfields($field_key) {
        $subfields = array($field_key); // Always include the main field
        
        // Method 1: Try to get field structure from ACF
        if (function_exists('acf_get_field')) {
            $field = acf_get_field($field_key);
            if ($field && isset($field['sub_fields']) && !empty($field['sub_fields'])) {
                foreach ($field['sub_fields'] as $subfield) {
                    $subfields[] = $field_key . '_' . $subfield['name'];
                }
                return $subfields;
            }
        }
        
        // Method 2: Try get_field_object with a sample post
        if (function_exists('get_field_object')) {
            $sample_post = get_posts(array(
                'post_type' => 'any',
                'posts_per_page' => 1,
                'post_status' => 'publish'
            ));
            
            if (!empty($sample_post)) {
                $field = get_field_object($field_key, $sample_post[0]->ID);
                if ($field && isset($field['sub_fields']) && !empty($field['sub_fields'])) {
                    foreach ($field['sub_fields'] as $subfield) {
                        $subfields[] = $field_key . '_' . $subfield['name'];
                    }
                    return $subfields;
                }
            }
        }
        
        // Method 3: Common location subfield patterns (fallback)
        // These are standard ACF location field subfields
        $common_subfields = array(
            $field_key . '_street_number',
            $field_key . '_street_name',
            $field_key . '_city',
            $field_key . '_state',
            $field_key . '_post_code',
            $field_key . '_country',
            $field_key . '_address', // Full address
        );
        
        return array_merge($subfields, $common_subfields);
    }
    
    /**
     * AJAX handler for search (optional - currently using redirect)
     */
    public function ajax_search() {
        check_ajax_referer('elementor_loop_search_nonce', 'nonce');
        
        $search_keyword = isset($_POST['search_keyword']) ? sanitize_text_field($_POST['search_keyword']) : '';
        $query_id = isset($_POST['query_id']) ? sanitize_text_field($_POST['query_id']) : '';
        
        wp_send_json_success(array(
            'redirect_url' => add_query_arg(array(
                'search_keyword' => $search_keyword,
                'query_id' => $query_id
            ), home_url($_SERVER['REQUEST_URI']))
        ));
    }
    
}

// Initialize the shortcode
new Elementor_Loop_Search_Shortcode();