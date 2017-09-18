document.addEventListener('DOMContentLoaded', function(){

  effcore = {};
  effcore.tokens = [];
  effcore.tokens['color_id'] = '%%_color_id';
  effcore.tokens['color_bg_id'] = '%%_color_bg_id';

  NodeList.prototype.each = function(func){
    for (var i = 0; i < this.length; i++) {
      func.call(this[i]);
    }
  };

/* this code activate hover state on ios devices */
  document.addEventListener('touchstart', function(){}, false);

});