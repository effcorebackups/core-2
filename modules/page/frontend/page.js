document.addEventListener('DOMContentLoaded', function(){

  effcore = {};
  effcore.tokens = [];
  effcore.tokens['color'] = '%%_color';
  effcore.tokens['color_bg'] = '%%_color_bg';

  NodeList.prototype.each = function(func){
    for (var i = 0; i < this.length; i++) {
      func.call(this[i]);
    }
  };

});