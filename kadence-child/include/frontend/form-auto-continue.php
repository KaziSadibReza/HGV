<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
/**
 * Custom Elementor Form Auto-Advance Script
 */
add_action('wp_footer', 'custom_elementor_form_autoadvance_script');
function custom_elementor_form_autoadvance_script() {
    ?>
<script>
jQuery(document).ready(function($) {

    // Target the radio inputs specifically in your form
    // Using 'body' delegation ensures it works even if the form loads via AJAX
    $('body').on('change', '.custom_banner_form input[type="radio"]', function() {

        // Find the specific step container this radio button belongs to
        var $currentStep = $(this).closest('.e-form__step');

        // Wait 300ms for visual effect, then find the Next button ONLY inside this step
        setTimeout(function() {
            $currentStep.find('.e-form__buttons__wrapper__button-next').click();
        }, 300);

    });

});
</script>
<?php
}