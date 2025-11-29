/**
 * Elementor Loop Search - AJAX functionality
 * Integrates with Elementor Loop Grid using query filters
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Apply search filters on page load if URL parameters exist
    if (window.location.search) {
      var urlParams = new URLSearchParams(window.location.search);
      var searchKeyword = urlParams.get("search_keyword");
      var searchLocation = urlParams.get("search_location");

      // Populate search inputs with values from URL
      if (searchKeyword) {
        $('.job-search-input[name="search_keyword"]').val(searchKeyword);
      }
      if (searchLocation) {
        $('.job-search-input[name="search_location"]').val(searchLocation);
      }
    }

    // Handle search form submission
    $(".elementor-loop-search-form").on("submit", function (e) {
      e.preventDefault();

      var $form = $(this);
      var $wrapper = $form.closest(".elementor-loop-search-wrapper");
      var $loader = $wrapper.find(".search-loader");

      var queryId = $wrapper.data("query-id");
      var locationMeta = $wrapper.data("location-meta");
      var searchKeyword = $form.find('input[name="search_keyword"]').val();
      var searchLocation = $form.find('input[name="search_location"]').val();

      // Find the Elementor loop grid with matching query ID
      var $loopGrid = $(
        '[data-settings*="\\"posts_query_id\\":\\"' + queryId + '\\""]'
      ).first();

      if ($loopGrid.length === 0) {
        // Try alternative selectors
        $loopGrid = $('[data-widget_type*="loop-grid"]')
          .filter(function () {
            var settings = $(this).attr("data-settings");
            return settings && settings.indexOf(queryId) !== -1;
          })
          .first();
      }

      if ($loopGrid.length === 0) {
        console.error(
          "Could not find Elementor Loop Grid with query ID: " + queryId
        );
        alert(
          "Error: Could not find the results grid. Please check your query ID."
        );
        return;
      }

      // Show loader
      $loader.show();

      // Perform AJAX search to validate and get results count
      $.ajax({
        url: elementorLoopSearch.ajax_url,
        type: "POST",
        data: {
          action: "elementor_loop_search",
          nonce: elementorLoopSearch.nonce,
          query_id: queryId,
          search_keyword: searchKeyword,
          search_location: searchLocation,
          location_meta_key: locationMeta,
        },
        success: function (response) {
          if (response.success) {
            console.log(
              "Search results: " + response.data.found_posts + " posts found"
            );

            // Build URL parameters for page reload with filters
            var currentUrl = window.location.href.split("?")[0].split("#")[0];
            var params = new URLSearchParams();

            if (searchKeyword) {
              params.append("search_keyword", searchKeyword);
            }
            if (searchLocation) {
              params.append("search_location", searchLocation);
            }
            params.append("query_id", queryId);
            if (locationMeta) {
              params.append("location_meta_key", locationMeta);
            }

            // Reload page with search parameters
            // This allows Elementor to apply filters properly
            window.location.href = currentUrl + "?" + params.toString();
          } else {
            $loader.hide();
            alert("Error: " + (response.data.message || "Search failed"));
          }
        },
        error: function (xhr, status, error) {
          $loader.hide();
          console.error("AJAX Error:", error);
          alert("An error occurred while searching. Please try again.");
        },
      });
    });

    // Clear search button functionality
    $(document).on("click", ".clear-search-btn", function (e) {
      e.preventDefault();
      var currentUrl = window.location.href.split("?")[0].split("#")[0];
      window.location.href = currentUrl;
    });

    // Optional: Enter key submit for inputs
    $(".job-search-input").on("keypress", function (e) {
      if (e.which === 13) {
        e.preventDefault();
        $(this).closest("form").submit();
      }
    });
  });
})(jQuery);
