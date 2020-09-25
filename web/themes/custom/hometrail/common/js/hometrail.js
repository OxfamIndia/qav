
/* $('.owl-carousel').owlCarousel({
    loop:true,
    autoplay:true,
    margin:50,
    nav:false,
    responsive:{
        0:{
            items:1
        },
        600:{
            items:2
        },
        1000:{
            items:2
        }
    }
})
*/


$(document).ready(function () {	
        setInterval(function()
{
    $.ajax({
        type: "get",
        url: "https://virtualtrailwalker.oxfamindia.org/cron/N_sBk33mwlywIwpaeWuhzgSsBjs2U6gk_XYMMtyCpteL1JqKDHBtXx2hot8z9ocaHglUzEamuQ",
        success:function(data)
        {
            //console.log the response
            console.log(data);
        }
    });
}, 240000); //10000 milliseconds = 10 seconds
	 $("#edit-field-designation-0-value--2").removeAttr("required");
	 $("#edit-field-designation-wrapper--2").hide();
	  $("#edit-field-pan-card-0-value--2").attr('placeholder', 'PAN Card (Optional)');
	 $('#edit-field-country-0--2 .js-form-required').html('Select Country Below');
	   $("#edit-field-pincode-0-value--2").attr('placeholder', 'Pin Code (Optional)');
	   $("#edit-field-pincode-0-value").attr('placeholder', 'Pin Code (Optional)');
	   $("#edit-mail").attr('placeholder', 'Company Email Address');
	   
	 /* $('#edit-field-pincode-0-value--2').attr('type', 'text');
	 
	  $("#edit-field-pincode-0-value--2").removeAttr("aria-required");
	 $("#edit-field-pincode-0-value--2").removeAttr("aria-invalid");
	 $("#edit-field-pincode-0-value--2").removeClass("required"); */
	

$('#edit-field-mobile-number-0-value--2').keyup(function () {
    this.value = this.value.replace(/[^0-9\.]/g,'');
});
    $('.donation-form fieldset:first-child').fadeIn('slow');
    $("#edit-day1-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day2-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day3-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day4-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day5-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day6-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day7-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day8-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day9-walk-distance").attr("placeholder","Enter Kms");
    $("#edit-day10-walk-distance").attr("placeholder","Enter Kms");
    $('.donation-form input[type="radio"]').on('focus', function () {
        $(this).removeClass('input-error');
    });

    $("#edit-field-date-of-birth-0-value-date--2").attr("placeholder","MM/DD/YYYY");
    $(".field--name-field-t-c").parent().parent().addClass('termsdev');
    $(".field--name-field-gdp").parent().parent().addClass('termsdev gdp');
    $(".field--name-field-gdpr").parent().parent().addClass('termsdev gdpr');

    $(".field--name-field-pan-card").parent().parent().addClass('fixstate panstate');
    $(".field--name-field-city").parent().parent().addClass('fixstate');
    $(".field--name-field-country").parent().parent().addClass('fixCountry');


    $('#edit-field-event-name--2>option[value=_none]').insertBefore('#edit-field-event-name--2>option[value=3]');
    $('#edit-field-event-name>option[value=_none]').insertBefore('#edit-field-event-name>option[value=3]');

    $("select.country.form-select").prop('required', true);
$('select.country.form-select option:first').text('- Select Country -');
    /*if($('select.country.form-select').length) {
        $('select.country.form-select').append($('<option></option>').val('_none').text('Select Country'));
        $('select.country.form-select>option[value=_none]').insertBefore('select.country.form-select>option[value=AF]');
        if($('select.country.form-select').val() == 'AF') {
            $('select.country.form-select').val('_none');
        }
    }*/

    $("#edit-field-date-of-birth-0-value-date--2").on('focus', function() {
        $("#edit-field-date-of-birth-0-value--2--description").css('display', 'none');
        $("#edit-field-date-of-birth-0-value-date--2").css('color', '#888');
    });

    $("#edit-field-date-of-birth-0-value-date").on('focus', function() {
        $("#edit-field-date-of-birth-0-value--description").css('display', 'none');
        $("#edit-field-date-of-birth-0-value-date").css('color', '#888');
    });

    $("#edit-field-date-of-birth-0-value-date--2").on('focusout', function() {
        if($("#edit-field-date-of-birth-0-value-date--2").val() == '') {
            $("#edit-field-date-of-birth-0-value--2--description").css('display', 'block');
            $("#edit-field-date-of-birth-0-value-date--2").css('color', '#fff');
        }
    });

    $("#edit-field-date-of-birth-0-value-date").on('focusout', function() {
        if($("#edit-field-date-of-birth-0-value-date").val() == '') {
            $("#edit-field-date-of-birth-0-value--description").css('display', 'block');
            $("#edit-field-date-of-birth-0-value-date").css('color', '#fff');
        }
    });

    $("#edit-field-date-of-birth-0-value--2--description").on('click', function() {
        $("#edit-field-date-of-birth-0-value-date--2").focus();
    });

    $("#edit-field-date-of-birth-0-value--description").on('click', function() {
        $("#edit-field-date-of-birth-0-value-date").focus();
    });

    $("#edit-field-nationality--2").change(function() {
        if($("#edit-field-nationality--2").val() == 'indian') {
            $('.termsdev.gdp').css('display', 'block');
            $("#edit-field-gdpr-value--2").prop("checked", false);
        } else if ($("#edit-field-nationality--2").val() == 'foreigner') {
            $('.termsdev.gdp').css('display', 'block');
            $('.termsdev.gdpr').css('display', 'block');
            $("#edit-field-gdpr-value--2").prop("checked", true);
            $("#edit-field-gdpr-value--2").prop("checked", true);
        } else {
            $('.termsdev.gdp').css('display', 'none');
            $('.termsdev.gdpr').css('display', 'none');
            $("#edit-field-gdpr-value--2").prop("checked", false);
            $("#edit-field-gdpr-value--2").prop("checked", false);
        }
    });

    $(".country.form-select").on( "change", function() {
        console.log('yes');
        if($('select.administrative-area.form-select').length) {
            console.log('here');
            $('select.administrative-area.form-select:contains("- None -")').text('Select State');
            console.log('here after');
        }
    });

    if($('select.administrative-area.form-select').length) {
        console.log('here');
        if($('select.administrative-area.form-select').val() == '') {
            $('select.administrative-area.form-select:contains("- None -")').text('Select State');
        } else {
            $('select.administrative-area.form-select').addClass('nonEmptyState');
        }
        console.log('here after');
    }


    // next step
    $('.donation-form .btn-next').on('click', function () {
        var parent_fieldset = $(this).parents('fieldset');
        var next_step = true;

        parent_fieldset.find('input[type="radio"],input[type="email"]').each(function () {
            if ($(this).val() == "") {
                $(this).addClass('input-error');
                next_step = false;
            } else {
                $(this).removeClass('input-error');
            }
        });

        if (next_step) {
            parent_fieldset.fadeOut(400, function () {
                $(this).next().fadeIn();
            });
        }

    });

    // previous step
    $('.donation-form .btn-previous').on('click', function () {
        $(this).parents('fieldset').fadeOut(400, function () {
            $(this).prev().fadeIn();
        });
    });

    // submit
    $('.donation-form').on('submit', function (e) {

        $(this).find('input[type="text"],input[type="email"]').each(function () {
            if ($(this).val() == "") {
                e.preventDefault();
                $(this).addClass('input-error');
            } else {
                $(this).removeClass('input-error');
            }
        });

    });

   
});

//Smooth scrolling script:
   $(function() {
  $('.donate_btn').click(function() {
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
}); // end
