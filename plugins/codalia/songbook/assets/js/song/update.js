(function($) {

  // Run a function when the page is fully loaded including graphics.
  $(window).load(function() {

    $.fn.setMainCategory();

    $(document).on('ajaxSetup', function(event, context, data) {
      $.fn.setMainCategory();
    });
  });

  $.fn.setMainCategory = function() {
    let mainCategoryId = $('#Form-field-Song-category').val();
    // Loops through the checkbox inputs.
    $('.custom-checkbox').children('input').each(function(i, input) {
      if($(input).val() == mainCategoryId) {
	// Sets this category as main category.
	$(input).attr('checked', true);
	$(input).attr('disabled', true);
	$(input).addClass('main-category');
      }
      else {
	// Enables the checkbox.
	$(input).attr('disabled', false);
	// Unchecks the main category previously selected.
	if($(input).hasClass('main-category')) {
	  $(input).attr('checked', false);
	  $(input).removeClass('main-category');
	}
      }
    });
  }

})(jQuery);
