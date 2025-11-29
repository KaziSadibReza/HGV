<?php
/**
 * Elementor Loop Grid AJAX Search Shortcode
 * 
 * Adds a search bar with AJAX functionality to filter Elementor Loop Grid
 * Supports search by keyword and location (meta field)
 * 
 * Usage: [elementor_loop_search query_id="your_query_id" location_meta_key="location_field"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Loop_Search_Shortcode {
    
    public function __construct() {
        add_shortcode('elementor_loop_search', array($this, 'render_search_form'));
        add_action('wp_ajax_elementor_loop_search', array($this, 'ajax_search'));
        add_action('wp_ajax_nopriv_elementor_loop_search', array($this, 'ajax_search'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Apply search filters to Elementor Loop Grid queries
        add_filter('elementor/query/query_args', array($this, 'apply_search_filters'), 10, 2);
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts() {
        wp_enqueue_style(
            'elementor-loop-search',
            get_stylesheet_directory_uri() . '/assets/css/elementor-loop-search.css',
            array(),
            '1.0.0'
        );
        
        wp_enqueue_script(
            'elementor-loop-search',
            get_stylesheet_directory_uri() . '/assets/js/elementor-loop-search.js',
            array('jquery'),
            '1.0.0',
            true
        );
        
        wp_localize_script('elementor-loop-search', 'elementorLoopSearch', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('elementor_loop_search_nonce')
        ));
    }
    
    /**
     * Render search form shortcode
     */
    public function render_search_form($atts) {
        $atts = shortcode_atts(array(
            'query_id' => '',
            'location_meta_key' => '',
            'placeholder_search' => 'Search...',
            'placeholder_location' => 'Location...',
            'button_text' => 'Search',
            'show_location' => 'yes'
        ), $atts);
        
        if (empty($atts['query_id'])) {
            return '<p style="color: red;">Error: query_id parameter is required for elementor_loop_search shortcode.</p>';
        }
        
        $unique_id = uniqid('els_');
        
        ob_start();
        ?>
<div class="elementor-loop-search-wrapper" data-query-id="<?php echo esc_attr($atts['query_id']); ?>"
    data-location-meta="<?php echo esc_attr($atts['location_meta_key']); ?>" id="<?php echo esc_attr($unique_id); ?>">
    <form class="elementor-loop-search-form" method="get">
        <!-- Job Search Container -->
        <div class="job-search-container">
            <div class="search-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>
            <input type="text" name="search_keyword" class="job-search-input"
                placeholder="<?php echo esc_attr($atts['placeholder_search']); ?>" aria-label="Search jobs" value="">
        </div>

        <?php if ($atts['show_location'] === 'yes' && !empty($atts['location_meta_key'])): ?>
        <!-- Location Search Container -->
        <div class="job-search-container" style="margin-top: 16px;">
            <div class="search-icon">
                <svg viewBox="0 0 24 24">
                    <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                    <circle cx="12" cy="10" r="3"></circle>
                </svg>
            </div>
            <input type="text" name="search_location" class="job-search-input"
                placeholder="<?php echo esc_attr($atts['placeholder_location']); ?>" aria-label="Search location"
                value="">
        </div>
        <?php endif; ?>

        <input type="hidden" name="action" value="elementor_loop_search">
        <input type="hidden" name="query_id" value="<?php echo esc_attr($atts['query_id']); ?>">
        <input type="hidden" name="location_meta_key" value="<?php echo esc_attr($atts['location_meta_key']); ?>">
    </form>

    <div class="search-loader" style="display: none;">
        <span class="loader-spinner"></span>
    </div>
</div>
<?php
        return ob_get_clean();
    }
    
    /**
     * AJAX search handler
     */
    public function ajax_search() {
        check_ajax_referer('elementor_loop_search_nonce', 'nonce');
        
        $query_id = isset($_POST['query_id']) ? sanitize_text_field($_POST['query_id']) : '';
        $search_keyword = isset($_POST['search_keyword']) ? sanitize_text_field($_POST['search_keyword']) : '';
        $search_location = isset($_POST['search_location']) ? sanitize_text_field($_POST['search_location']) : '';
        $location_meta_key = isset($_POST['location_meta_key']) ? sanitize_text_field($_POST['location_meta_key']) : '';
        
        if (empty($query_id)) {
            wp_send_json_error(array('message' => 'Query ID is required'));
        }
        
        // Build query arguments
        $args = $this->build_query_args($search_keyword, $search_location, $location_meta_key);
        
        // Apply Elementor query filter
        add_filter('elementor/query/query_args', function($query_args, $widget) use ($query_id, $args) {
            if (isset($widget->get_settings()['posts_query_id']) && $widget->get_settings()['posts_query_id'] === $query_id) {
                $query_args = array_merge($query_args, $args); 
            }
            return $query_args;
        }, 10, 2);
        
        // Get the results
        $query = new WP_Query($args);
        
        // Return query data - Elementor Loop Grid will handle the rendering
        wp_send_json_success(array(
            'found_posts' => $query->found_posts,
            'query_id' => $query_id,
            'query_args' => $args,
            'has_posts' => $query->have_posts()
        ));
        
        wp_reset_postdata();
    }
    
    /**
     * Build query arguments
     */
    private function build_query_args($search_keyword, $search_location, $location_meta_key) {
        $args = array(
            'post_type' => 'any',
            'posts_per_page' => -1,
            'post_status' => 'publish'
        );
        
        // Add search keyword
        if (!empty($search_keyword)) {
            $args['s'] = $search_keyword;
        }
        
        // Add location meta query
        if (!empty($search_location) && !empty($location_meta_key)) {
            $args['meta_query'] = array(
                array(
                    'key' => $location_meta_key,
                    'value' => $search_location,
                    'compare' => 'LIKE'
                )
            );
        }
        
        return $args;
    }
    
    /**
     * Apply search filters to Elementor Loop Grid queries
     * This is triggered when the page loads with search parameters in the URL
     */
    public function apply_search_filters($query_args, $widget) {
        // Check if we have search parameters in the URL
        if (!isset($_GET['query_id']) || empty($_GET['query_id'])) {
            return $query_args;
        }
        
        $url_query_id = sanitize_text_field($_GET['query_id']);
        
        // Get the widget's query ID
        $widget_settings = $widget->get_settings();
        $widget_query_id = isset($widget_settings['posts_query_id']) ? $widget_settings['posts_query_id'] : '';
        
        // Only apply filters if query IDs match
        if ($widget_query_id !== $url_query_id) {
            return $query_args;
        }
        
        // Get search parameters from URL
        $search_keyword = isset($_GET['search_keyword']) ? sanitize_text_field($_GET['search_keyword']) : '';
        $search_location = isset($_GET['search_location']) ? sanitize_text_field($_GET['search_location']) : '';
        $location_meta_key = isset($_GET['location_meta_key']) ? sanitize_text_field($_GET['location_meta_key']) : '';
        
        // Apply keyword search
        if (!empty($search_keyword)) {
            $query_args['s'] = $search_keyword;
        }
        
        // Apply location meta search
        if (!empty($search_location) && !empty($location_meta_key)) {
            if (!isset($query_args['meta_query'])) {
                $query_args['meta_query'] = array();
            }
            
            $query_args['meta_query'][] = array(
                'key' => $location_meta_key,
                'value' => $search_location,
                'compare' => 'LIKE'
            );
        }
        
        return $query_args;
    }
    
}

// Initialize the shortcode
new Elementor_Loop_Search_Shortcode();