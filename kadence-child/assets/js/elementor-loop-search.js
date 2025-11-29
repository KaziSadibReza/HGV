/**
 * Elementor Loop Search - AJAX submission
 */
jQuery(document).ready(function ($) {
  // Initialize the form with current URL parameters
  initializeFormFromURL();

  // AJAX form submission
  $(".elementor-loop-search-form").on("submit", function (e) {
    e.preventDefault();

    var $form = $(this);
    var $wrapper = $form.closest(".elementor-loop-search-wrapper");
    var searchKeyword = $form.find('input[name="search_keyword"]').val();
    var queryId = $form.find('input[name="query_id"]').val();

    // Don't search if empty
    if (!searchKeyword || searchKeyword.trim() === "") {
      return false;
    }

    // Build clean URL and redirect
    var currentUrl = window.location.href.split("?")[0].split("#")[0];
    var params = new URLSearchParams();
    params.append("search_keyword", searchKeyword);
    params.append("query_id", queryId);

    // Redirect with clean URL
    window.location.href = currentUrl + "?" + params.toString();
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
  }
});
