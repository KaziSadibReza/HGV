<?php
/**
 * Elementor Loop Grid Search Filter (Title OR Meta)
 * * Features:
 * - Dynamic Query ID & Meta Key via Shortcode
 * - AJAX "No Reload" Submission
 * - Searches Title OR Content OR Custom Meta (ACF)
 */

if (!defined('ABSPATH')) {
    exit;
}

class Elementor_Loop_Search_Shortcode {
    
    public function __construct() {
        // Shortcode
        add_shortcode('elementor_loop_search', array($this, 'render_search_form'));
        
        // Scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        
        // Dynamic Hook Registration
        // We check if a query_id is present in the URL (from our form) and register the hook for IT specifically.
        add_action('wp', array($this, 'register_dynamic_query_hook'));
    }

    /**
     * dynamically adds the action for the specific query ID passed in the URL/AJAX
     */
    public function register_dynamic_query_hook() {
        if (isset($_GET['query_id']) && !empty($_GET['query_id'])) {
            $query_id = sanitize_text_field($_GET['query_id']);
            add_action("elementor/query/{$query_id}", array($this, 'filter_elementor_query'), 10, 2);
        }
    }
    
    public function enqueue_scripts() {

        wp_enqueue_script('jquery');
        
        // AJAX/JS Handler
        wp_add_inline_script('jquery', '
            jQuery(document).ready(function($) {
                // 1. Populate Input from URL on load
                const urlParams = new URLSearchParams(window.location.search);
                const keyword = urlParams.get("search_keyword");
                if(keyword) {
                    $(".job-search-input").val(keyword);
                }

                // 2. Handle Form Submit (AJAX)
                $(".elementor-loop-search-form").on("submit", function(e) {
                    e.preventDefault();
                    
                    var $form = $(this);
                    var $input = $form.find(".job-search-input");
                    var $container = $form.closest(".elementor-loop-search-wrapper");
                    
                    var searchVal = $input.val();
                    var queryId = $form.find("input[name=\'query_id\']").val();
                    var metaKey = $form.find("input[name=\'target_meta_key\']").val();
                    
                    // Build URL
                    var currentUrl = window.location.href.split("?")[0];
                    var newUrl = currentUrl;
                    
                    if (searchVal) {
                        newUrl += "?search_keyword=" + encodeURIComponent(searchVal) + 
                                  "&query_id=" + encodeURIComponent(queryId) + 
                                  "&target_meta_key=" + encodeURIComponent(metaKey);
                    }

                    // UI Loading State
                    $container.addClass("els-loading");
                    $(".elementor-widget-loop-grid").addClass("els-loading-grid");

                    // 3. Fetch HTML (Faux AJAX)
                    $.ajax({
                        url: newUrl,
                        type: "GET",
                        success: function(response) {
                            // Parse the HTML response
                            var $html = $(response);
                            
                            // Find the grid in the new HTML
                            // We look for the standard Elementor Loop Grid class
                            var $newGrid = $html.find(".elementor-widget-loop-grid");
                            var $currentGrid = $(".elementor-widget-loop-grid");

                            if ($newGrid.length && $currentGrid.length) {
                                // Replace content
                                $currentGrid.html($newGrid.html());
                            } else {
                                console.log("Grid not found in response");
                            }

                            // Update Browser URL (History API)
                            window.history.pushState({path: newUrl}, "", newUrl);
                        },
                        error: function() {
                            alert("Error loading results.");
                        },
                        complete: function() {
                            // Remove Loading State
                            $container.removeClass("els-loading");
                            $(".elementor-widget-loop-grid").removeClass("els-loading-grid");
                        }
                    });
                });
            });
        ');
    }
    
    public function render_search_form($atts) {
        $atts = shortcode_atts(array(
            'query_id' => '6969', // Default if not provided
            'location_meta_key' => 'location_', // Default ACF key
            'placeholder_search' => 'Search jobs by title or location...',
        ), $atts);
        
        ob_start();
        ?>
<div class="elementor-loop-search-wrapper">
    <form class="elementor-loop-search-form">
        <div class="job-search-container">
            <div class="search-icon">
                <svg viewBox="0 0 24 24">
                    <circle cx="11" cy="11" r="8"></circle>
                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                </svg>
            </div>

            <!-- Search Input -->
            <input type="text" name="search_keyword" class="job-search-input"
                placeholder="<?php echo esc_attr($atts['placeholder_search']); ?>" aria-label="Search">

            <!-- Hidden Config Fields -->
            <input type="hidden" name="query_id" value="<?php echo esc_attr($atts['query_id']); ?>">
            <input type="hidden" name="target_meta_key" value="<?php echo esc_attr($atts['location_meta_key']); ?>">
        </div>
    </form>
</div>
<?php
        return ob_get_clean();
    }
    
    /**
     * MAIN FILTER LOGIC
     */
    public function filter_elementor_query($query) {
        // 1. Safety Check
        if (!isset($_GET['search_keyword']) || empty($_GET['search_keyword'])) {
            return;
        }

        $keyword = sanitize_text_field($_GET['search_keyword']);
        
        // 2. Get Meta Keys from URL (passed via Shortcode hidden input)
        $base_meta_key = isset($_GET['target_meta_key']) ? sanitize_key($_GET['target_meta_key']) : 'location_';
        
        // We automatically assume ACF Address field structure (key + key_address)
        $meta_keys = [$base_meta_key, $base_meta_key . '_address']; 

        // 3. Set Custom Query Vars
        $query->set('custom_search_keyword', $keyword);
        $query->set('custom_search_meta_keys', $meta_keys);
        
        // 4. Hook into SQL
        add_filter('posts_join', array($this, 'modify_posts_join'), 10, 2);
        add_filter('posts_where', array($this, 'modify_posts_where'), 10, 2);
        
        // Cleanup after query
        add_action('elementor/query/after_query', function() {
            remove_filter('posts_join', array($this, 'modify_posts_join'), 10);
            remove_filter('posts_where', array($this, 'modify_posts_where'), 10);
        });
    }

    public function modify_posts_join($join, $query) {
        global $wpdb;
        if ($query->get('custom_search_keyword')) {
            $join .= " LEFT JOIN {$wpdb->postmeta} AS search_meta ON ({$wpdb->posts}.ID = search_meta.post_id) ";
        }
        return $join;
    }

    public function modify_posts_where($where, $query) {
        global $wpdb;
        
        $keyword = $query->get('custom_search_keyword');
        $meta_keys = $query->get('custom_search_meta_keys');

        if ($keyword) {
            $escaped_keyword = $wpdb->esc_like($keyword);
            $like_string = '%' . $escaped_keyword . '%';
            
            // Title OR Content
            $search_logic = "({$wpdb->posts}.post_title LIKE '{$like_string}') OR ({$wpdb->posts}.post_content LIKE '{$like_string}')";
            
            // OR Meta
            if (!empty($meta_keys)) {
                $meta_sql_parts = array();
                foreach ($meta_keys as $key) {
                    $meta_sql_parts[] = "(search_meta.meta_key = '{$key}' AND search_meta.meta_value LIKE '{$like_string}')";
                }
                if (!empty($meta_sql_parts)) {
                    $search_logic .= " OR " . implode(" OR ", $meta_sql_parts);
                }
            }

            $where .= " AND ({$search_logic}) ";
            add_filter('posts_groupby', array($this, 'modify_posts_groupby'));
        }
        return $where;
    }
    
    public function modify_posts_groupby($groupby) {
        global $wpdb;
        if( empty($groupby) ) {
            $groupby = "{$wpdb->posts}.ID";
        }
        return $groupby;
    }
}

new Elementor_Loop_Search_Shortcode();