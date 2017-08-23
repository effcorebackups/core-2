document.addEventListener('DOMContentLoaded', function(){

  document.querySelectorAll('input[type=range]')
          .each(function(){
    var c_value_box = document.createElement('div');
    this.parentNode.insertBefore(c_value_box, this.nextSibling);
    this.addEventListener('mousemove', function(){
      c_value_box.innerHTML = this.value;
    });
  });

});