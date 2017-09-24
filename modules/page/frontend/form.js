document.addEventListener('DOMContentLoaded', function(){

  document.querySelectorAll('input[type=range]').each(function(){
    var x_value = this.parentNode.querySelector('x-value');
    this.addEventListener('mousemove', function(){
      x_value.innerText = this.value;
    });
  });

});