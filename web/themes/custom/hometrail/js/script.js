(function ($) {
  $(document).ready(function(){
	  
	  
	  
	  $("#edit-display-amount").val("₹1000");
	  $("#edit-nationality").on( "change", function() {
		 
	var nationality =	$(this).val();
	var slot =	$("#edit-challenge-slot").val();
		//alert('Jay Jain');
		if(nationality == 'Indian'){
			if(slot == 20){
				$("#edit-display-amount").val("₹2000");
			}else{
				$("#edit-display-amount").val("₹1000");
			}
		
		
			$("#edit-pan-card-number").attr("placeholder",'Pan Card is Required for Donation over INR 1000');
		}else if(nationality == 'Foreign'){
			//$("#edit-display-amount").val("₹7000");
			
			if(slot == 20){
				$("#edit-display-amount").val("₹14000");
			}else{
				$("#edit-display-amount").val("₹7000");
			}
			$("#edit-pan-card-number").attr("placeholder",'Optional');
			
			
		}else{
			$("#edit-display-amount").val("");
			$("#edit-pan-card-number").attr("placeholder",'Pan Card is Required');
		}
       /*  console.log('yes');
        if($('select.administrative-area.form-select').length) {
            console.log('here');
            $('select.administrative-area.form-select:contains("- None -")').text('Select State');
            console.log('here after');
        } */
    });
	  
	  
	  
	  
	  
	  
	  
	  
	  
    countryfix();
    zipfix();
    showleaderboard();
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
    if(flagData) {
      var flagData = flag.split(':');
      flagData = $.trim(flagData[1]);

      var tel = $('#edit-mobile-number').val();
      tel = tel.replace(/ /g, '');
      //console.log(tel);
      var updatedMobile = tel.replace(flagData, flagData + ' ');
      //console.log(dd);
      $('#edit-mobile-number').val(updatedMobile);
    }
  };

  function zipfix() {
    var countrycode = $('select.country').val();
    console.log(countrycode);
    if(countrycode == 'IN') {
      $('#edit-zip-code').attr('minlength', 6);
      $('#edit-zip-code').attr('maxlength', 6);
      $('#edit-zip-code').attr("pattern", '^[0-9]*$');
      $('#edit-zip-code').attr("data-msg-pattern", 'Only numbers are allowed');
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

  function showleaderboard() {
    $('.champion-leaderboard-sec li').click( function() {
      console.log($(this).attr('id'));
      $('.champion-leaderboard-sec li').removeClass('active');
      switch($(this).attr('id')) {
        case 'leaderboardtab1':
          $(this).addClass('active');
          $('#block-views-block-leaderboard25-block-1').css('display', 'block');
          $('#block-views-block-leaderboard25-block-2').css('display', 'none');
          $('#block-views-block-leaderboard25-block-3').css('display', 'none');
          break;
        case 'leaderboardtab2':
          $(this).addClass('active');
          $('#block-views-block-leaderboard25-block-1').css('display', 'none');
          $('#block-views-block-leaderboard25-block-2').css('display', 'block');
          $('#block-views-block-leaderboard25-block-3').css('display', 'none');
          break;
        case 'leaderboardtab3':
          $(this).addClass('active');
          $('#block-views-block-leaderboard25-block-1').css('display', 'none');
          $('#block-views-block-leaderboard25-block-2').css('display', 'none');
          $('#block-views-block-leaderboard25-block-3').css('display', 'block');
          break;
      }
    });
  }
})(jQuery);
