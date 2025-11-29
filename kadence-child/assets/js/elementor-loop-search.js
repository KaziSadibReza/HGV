/**
 * Elementor Loop Search - Simple form submission
 * Matches the property filter approach
 */
jQuery(document).ready(function ($) {
  // Initialize the form with current URL parameters
  initializeFormFromURL();

  // Form submission handler - just submits normally (no AJAX, no validation)
  $(".elementor-loop-search-form").on("submit", function (e) {
    // Let the form submit normally to reload page with parameters
    // The Elementor widget will automatically filter based on URL parameters
  });

  function initializeFormFromURL() {
    const urlParams = new URLSearchParams(window.location.search);

    // Set search keyword input
    const searchKeyword = urlParams.get("search_keyword");
    if (searchKeyword) {
      $('input[name="search_keyword"]').val(searchKeyword);
    }

    // Set query_id hidden field if present
    const queryId = urlParams.get("query_id");
    if (queryId) {
      $('input[name="query_id"]').val(queryId);
    }

    // Set location_meta_key hidden field if present
    const locationMetaKey = urlParams.get("location_meta_key");
    if (locationMetaKey) {
      $('input[name="location_meta_key"]').val(locationMetaKey);
    }
  }
});
