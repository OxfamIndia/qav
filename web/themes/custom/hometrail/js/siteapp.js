(function($){
  $('#edit-day1-walk-distance, #edit-day2-walk-distance, #edit-day3-walk-distance, #edit-day4-walk-distance, #edit-day5-walk-distance, #edit-day6-walk-distance, #edit-day7-walk-distance, #edit-day8-walk-distance, #edit-day9-walk-distance, #edit-day10-walk-distance').keyup(function(){
    var val = $(this).val();
    if(isNaN(val)){
      val = val.replace(/[^0-9\.]/g,'');
      if(val.split('.').length>2) 
      val =val.replace(/\.+$/,"");
    }
    $(this).val(val);
  });
  Drupal.behaviors.clientside_validation = {
    attach: function (context) {
      // $(context).find('form').validate();
      $(context).find('form').each(function() {
        $.validator.addMethod(
          "regex",
          function(value, element, regexp) {
          var re = new RegExp(regexp);
          return this.optional(element) || re.test(value);
          },
          "Please check your input."
        );
        $(this).validate({
          rules: {
            'day1_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day2_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day3_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day4_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day5_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day6_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day7_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day8_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day9_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
            'day10_walk_distance': {
              required: true,
              regex: "^[0-9]+(\.[0-9]{1,2})?$",
              max: 999,
              maxlength: 5,
            },
          },
          messages: {
            'day1_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day2_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day3_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day4_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day5_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day6_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day7_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day8_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day9_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
            'day10_walk_distance': {
              required: "Please enter distance.",
              regex: "Please enter up to 2 decimal place." ,
              maxlength: "Please enter the shorter number<br>",
            },
          },
          highlight: function(element) {
            $(element).addClass("field-error");
          },
          unhighlight: function(element) {
            $(element).removeClass("field-error");
          },
          errorPlacement: function(error, element) {
            console.log(element)
            var placement = $(element).data('error');
            if (placement) {
              $(placement).append(error)
            } else {
              if($(element).hasClass('form-radio')){
                $(element).parent().parent().parent().append(error);
              }else if($(element).parent().parent().hasClass('form-type-checkbox')){
                $(element).parent().parent().parent().append(error);
              }else{
                $(element).after(error);
                // $(element).parent().append(error);
              } 
            }
          }
        });
      });
    }
  }
})(jQuery);