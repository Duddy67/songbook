(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    $.fn.setMainCategory();

    // Triggered before the request is formed.
    $(document).on('ajaxSetup', function(event, context, data) {
      $.fn.setMainCategory();
      // Enables the checkbox to get its value taken into account when saving.
      $('input:checkbox.main-category').attr('disabled', false);
    });

    // Triggered finally if the AJAX request was successful.
    $(document).on('ajaxDone', function() {
      // Disables the checkbox again.
      $('input:checkbox.main-category').attr('disabled', true);
    });
  });

  $.fn.setMainCategory = function() {
    let mainCategoryId = $('#Form-field-Song-category').val();
    // Loops through the checkbox inputs.
    $('.custom-checkbox').children('input').each(function(i, input) {
      if($(input).val() == mainCategoryId) {
	// Forces the main category to be checked.
	$(input).attr('checked', true);
	$(input).attr('disabled', true);
	$(input).addClass('main-category');
      }
      // Checks for the main category previously selected (if any).
      else if($(input).hasClass('main-category')) {
	// Enables then unchecks the checkbox.
	$(input).attr('disabled', false);
	$(input).attr('checked', false);
	$(input).removeClass('main-category');
      }
    });
  }

})(jQuery);
