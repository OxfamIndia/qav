(function ($) {
  $(document).ready(function(){
    countryfix();
    zipfix();
    $('#edit-mobile-number').focusout(function() {
      countryfix();
    });
    $('select.country').change(function() {
      zipfix();
    });
    $('#edit-zip-code').focusin(function() {
      zipfix();
    });
    $('#edit-actions-submit').click(function() {
      zipfix();
    });
    $('.faq_btn').click(function() {
      $(".dropdown").removeClass("open");
      if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
        var target = $(this.hash);
        target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
        if (target.length) {
          $('html,body').animate({
            scrollTop: target.offset().top
          }, 1000);
          return false;
        }
      }
    });
  });
  function countryfix() {
    var flag = $('#webform-submission-registration-edit-form .iti__selected-flag').attr("title");
    var flagData = flag.split(':');
    flagData = $.trim(flagData[1]);
    //console.log(flagData);

    var tel = $('#edit-mobile-number').val();
    tel = tel.replace(/ /g, '');
    //console.log(tel);
    var updatedMobile = tel.replace(flagData, flagData + ' ');
    //console.log(dd);
    $('#edit-mobile-number').val(updatedMobile);
  };

  function zipfix() {
    var countrycode = $('select.country').val();
    console.log(countrycode);
    if(countrycode == 'IN') {
      $('#edit-zip-code').attr('minlength', 6);
      $('#edit-zip-code').attr('maxlength', 6);
      $('#edit-zip-code').attr("pattern", '^[0-9]*$');
      $('#edit-zip-code').attr("data-msg-pattern", 'Only numebrs are allowed');
      $('#edit-zip-code').attr('data-msg-maxlength', 'Zip Code field has a maximum length of 6 for India.');
    } else {
      $('#edit-zip-code').removeAttr('minlength');
      $('#edit-zip-code').attr('maxlength', 255);
      $('#edit-zip-code').attr("pattern", '^[a-zA-Z0-9]*$');
      $('#edit-zip-code').attr("data-msg-pattern", 'Alpha Numeric is allowed');
      $('#edit-zip-code').attr('data-msg-maxlength', '');
      $('#edit-zip-code-error').css('display', 'none');
    }
  }
})(jQuery);
