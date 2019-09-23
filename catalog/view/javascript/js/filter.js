(function ($) {
  "use strict";

  var filters = function () {

    $('.b-filters-check input[type="checkbox"]').on('change', function () {

      var favorite = [];
      $.each($("input[name='n']:checked"), function(){
        favorite.push($(this).val());
      });
      var data = {
        filter_id : $(this).data('filter-id'),
        checked : favorite,
        is_path: $('#is_path').val()
      };
      $.ajax({
        url: "index.php?route=extension/module/filterapp/filter_products",
        type: "GET",
        data: data,
        success: function (response) {
          console.log(response)
          if( response == '{"error":"error"}' ){
              window.location.reload();
          }else{
              $('[data-content-id="b-items"] > .row div').remove();
              $('[data-content-id="b-items"] > .row').hide().append(response).fadeIn('normal');
          }
        }
      });
    });
  }

  $(function() {
    filters();
  });
})(jQuery)