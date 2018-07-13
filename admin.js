var $ = jQuery;
$( document ).ready(function() {
  $(".filter-json").on("click", function(){
    $.post(
      "admin-ajax.php", 
      {
          'action': 'filter',
          'data': {
            low_date: $('.low_date').val(),
            high_date: $('.high_date').val(),
            key_word: $('.key_word').val(),
          }
      }, 
      function(response) {
        var result = JSON.parse(response);
        result.forEach(function(element) {
          $(".list-result").append("<div>" + element.title.rendered  + "</div>")
        });
      }
  );
  })
});
  