(function ($) {
  Drupal.behaviors.myModuleBehavior = {
    attach: function (context, settings) {
      $(context).find('.click-me').once('myCustomBehavior').click(function () {
        alert('Hello, World!');
      });
    }
  };
  console.log('js loaded');
})(jQuery);
