(function ($) {
  $(document).ready(function() {
    
      if (!getCookie('stopMusic') || getCookie('stopMusic') == "true") {
        var music = document.getElementById('audio1');
        music.pause();
        $('.sound-frame-class').toggleClass('play-sound');
      } if (!getCookie('stopMusic') || getCookie('stopMusic') == null) {
        music.play();
        $('.sound-frame-class').toggleClass('play-sound');
      }
    
  $('#sound-frame').click(function(){
      $('.sound-frame-class').toggleClass('play-sound');
      var music = document.getElementById('audio1');
      if (music.paused) {
        music.play();
        document.cookie = "stopMusic=false; path=/;";
      }
      else {
        music.pause();
        document.cookie = "stopMusic=true; path=/;";
      }
  });

  });

  
  function getCookie(cookieName) {
    var cookieVal = null;
    if (document.cookie) {
      var arr = document.cookie.split((escape(cookieName) + '=')); 
      if(arr.length >= 2) {
        var arr2 = arr[1].split(';');
        cookieVal  = unescape(arr2[0]);
      }
    }
    return cookieVal;
  }

$( document ).ready(function() {
  var obj = {};
  $('.sound-section').each(function(){
      var text = $.trim($(this).text());
      if(obj[text]){
          $(this).remove();
      } else {
          obj[text] = true;
      }
  })
});
}(jQuery));