/**
 * Elementor Loop Search - AJAX functionality
 */
(function ($) {
  "use strict";

  $(document).ready(function () {
    // Handle search form submission
    $(".elementor-loop-search-form").on("submit", function (e) {
      e.preventDefault();

      var $form = $(this);
      var $wrapper = $form.closest(".elementor-loop-search-wrapper");
      var $loader = $wrapper.find(".search-loader");
      var $resultsContainer = $wrapper.find(".search-results-container");

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

      // Show loader
      $loader.show();

      // AJAX request
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
          $loader.hide();

          if (response.success) {
            // Update the Elementor loop grid or results container
            if ($loopGrid.length > 0) {
              // Find the container within the loop grid
              var $gridContainer = $loopGrid.find(
                ".elementor-loop-container, .elementor-posts-container, .elementor-container"
              );

              if ($gridContainer.length > 0) {
                // Replace content with new results
                $gridContainer.html(response.data.html);

                // Trigger Elementor's event for any animations or interactions
                if (typeof elementorFrontend !== "undefined") {
                  elementorFrontend.elementsHandler.runReadyTrigger(
                    $gridContainer
                  );
                }
              } else {
                // Fallback to results container
                $resultsContainer.html(response.data.html);
              }
            } else {
              // No loop grid found, use results container
              $resultsContainer.html(response.data.html);
            }

            // Scroll to results
            if ($loopGrid.length > 0) {
              $("html, body").animate(
                {
                  scrollTop: $loopGrid.offset().top - 100,
                },
                500
              );
            }

            // Show results count
            console.log("Found " + response.data.found_posts + " posts");
          } else {
            alert("Error: " + response.data.message);
          }
        },
        error: function (xhr, status, error) {
          $loader.hide();
          console.error("AJAX Error:", error);
          alert("An error occurred while searching. Please try again.");
        },
      });
    });

    // Optional: Live search (search as you type)
    var searchTimeout;
    $('.elementor-loop-search-form input[type="text"]').on(
      "keyup",
      function () {
        clearTimeout(searchTimeout);
        var $form = $(this).closest(".elementor-loop-search-form");

        searchTimeout = setTimeout(function () {
          // Uncomment the line below to enable live search
          // $form.submit();
        }, 500);
      }
    );

    // Clear search
    $(".elementor-loop-search-form").on("reset", function () {
      var $form = $(this);
      setTimeout(function () {
        $form.submit();
      }, 100);
    });
  });
})(jQuery);
