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
        <div class="search-fields">
            <div class="search-field search-keyword">
                <input type="text" name="search_keyword" class="search-input"
                    placeholder="<?php echo esc_attr($atts['placeholder_search']); ?>" value="">
            </div>

            <?php if ($atts['show_location'] === 'yes' && !empty($atts['location_meta_key'])): ?>
            <div class="search-field search-location">
                <input type="text" name="search_location" class="location-input"
                    placeholder="<?php echo esc_attr($atts['placeholder_location']); ?>" value="">
            </div>
            <?php endif; ?>

            <div class="search-field search-submit">
                <button type="submit" class="search-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </div>

        <input type="hidden" name="action" value="elementor_loop_search">
        <input type="hidden" name="query_id" value="<?php echo esc_attr($atts['query_id']); ?>">
        <input type="hidden" name="location_meta_key" value="<?php echo esc_attr($atts['location_meta_key']); ?>">
    </form>

    <div class="search-loader" style="display: none;">
        <span class="loader-spinner"></span>
    </div>

    <div class="search-results-container">
        <!-- Results will be loaded here via AJAX -->
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
        
        if ($query->have_posts()) {
            ob_start();
            
            while ($query->have_posts()) {
                $query->the_post();
                // Use Elementor's template rendering if available
                $this->render_post_item();
            }
            
            wp_reset_postdata();
            
            $html = ob_get_clean();
            
            wp_send_json_success(array(
                'html' => $html,
                'found_posts' => $query->found_posts,
                'query_id' => $query_id
            ));
        } else {
            wp_send_json_success(array(
                'html' => '<div class="no-results"><p>No results found.</p></div>',
                'found_posts' => 0,
                'query_id' => $query_id
            ));
        }
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
     * Render individual post item
     * This is a fallback template - Elementor's template will be used when available
     */
    private function render_post_item() {
        ?>
<div class="elementor-loop-item">
    <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
        <?php if (has_post_thumbnail()): ?>
        <div class="post-thumbnail">
            <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium'); ?>
            </a>
        </div>
        <?php endif; ?>

        <div class="post-content">
            <h3 class="post-title">
                <a href="<?php the_permalink(); ?>">
                    <?php the_title(); ?>
                </a>
            </h3>

            <div class="post-excerpt">
                <?php the_excerpt(); ?>
            </div>

            <a href="<?php the_permalink(); ?>" class="read-more">
                Read More
            </a>
        </div>
    </article>
</div>
<?php
    }
}

// Initialize the shortcode
new Elementor_Loop_Search_Shortcode();