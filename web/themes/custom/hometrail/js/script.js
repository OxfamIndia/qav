(function ($) {
  $(document).ready(function(){
    countryfix();
    $('#edit-mobile-number').focusout(function() {
      countryfix();
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
})(jQuery);
