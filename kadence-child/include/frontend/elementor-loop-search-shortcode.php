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
    
    private $registered_query_ids = array();
    
    public function __construct() {
        // Add the search shortcode
        add_shortcode('elementor_loop_search', array($this, 'render_search_form'));
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Hook into all Elementor queries
        add_action('elementor/query/query_args', array($this, 'filter_query_args'), 10, 2);
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
        
        // Store query_id for filtering
        $this->registered_query_ids[] = $atts['query_id'];
        
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
        <?php if (!empty($atts['location_meta_key'])): ?>
        <input type="hidden" name="location_meta_key" value="<?php echo esc_attr($atts['location_meta_key']); ?>">
        <?php endif; ?>
    </form>
</div>
<?php
        return ob_get_clean();
    }
    
    /**
     * Filter Elementor query arguments
     */
    public function filter_query_args($query_args, $widget) {
        // Check if we have search parameter in URL
        if (!isset($_GET['search_keyword']) || empty($_GET['search_keyword']) || !isset($_GET['query_id'])) {
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
        
        $search_keyword = sanitize_text_field($_GET['search_keyword']);
        $location_meta_key = isset($_GET['location_meta_key']) ? sanitize_text_field($_GET['location_meta_key']) : '';
        
        // Apply keyword search
        $query_args['s'] = $search_keyword;
        
        // If location meta key is provided, also search in that field
        if (!empty($location_meta_key)) {
            if (!isset($query_args['meta_query'])) {
                $query_args['meta_query'] = array();
            }
            
            $query_args['meta_query'][] = array(
                'key' => $location_meta_key,
                'value' => $search_keyword,
                'compare' => 'LIKE'
            );
        }
        
        return $query_args;
    }
    
}

// Initialize the shortcode
new Elementor_Loop_Search_Shortcode();