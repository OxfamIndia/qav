(function ($) {
  $(document).ready(function(){
    countryfix();
    zipfix();
    $('#edit-mobile-number').focusout(function() {
      countryfix();
    });
    $('select.country').change(function() {
      console.log('here');
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
      $('#edit-pan-card-number').attr('minlength', 6);
      $('#edit-pan-card-number').attr('maxlength', 6);
      $('#edit-pan-card-number').attr("pattern", '^[0-9]*$');
      $('#edit-pan-card-number').attr("data-msg-pattern", 'Only numebrs are allowed');
      console.log('IN');
    } else {
      $('#edit-pan-card-number').removeAttr('minlength');
      $('#edit-pan-card-number').attr('maxlength', 255);
      $('#edit-pan-card-number').attr("pattern", '^[a-zA-Z0-9]*$');
      $('#edit-pan-card-number').attr("data-msg-pattern", 'Alpha Numeric is allowed');
      console.log('NOT IN');
    }
  }
})(jQuery);
