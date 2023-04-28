document.addEventListener("DOMContentLoaded", function() {
    window.onscroll = function() {scrollFunction()};
    var btnSubir = document.getElementById("subir");
    btnSubir.style.display = "none";
    btnSubir.onclick = function() {
      document.body.scrollTop = 0;
      document.documentElement.scrollTop = 0;
    };
    
    function scrollFunction() {
      if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
        btnSubir.style.display = "block";
      } else {
        btnSubir.style.display = "none";
      }
    }
  });
  