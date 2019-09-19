$(document).ready(function () {

$('.b-slider').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  fade: false,
  variableWidth: false,
  adaptiveHeight: true,
  arrows: false,
  dots: true,
});


$('.menu-icon').click(function(){
  $('.b-header-content__wrap').toggleClass('active');
  $('.menu-icon').toggleClass('active');
}); 

$(document).on('click', function(event) {
  if (!$(event.target).closest(".menu-icon , .b-header-content__wrap").length) {
    $('.b-header-content__wrap').removeClass('active');
    $('.menu-icon').removeClass('active');
  }
  event.stopPropagation();
});


function moveMenu(){
  if ($(window).width() < 991) {
         $(function(){ 

          $('.b-search').appendTo('.b-xs-search');
          $('.b-lang').appendTo('.xs-lang');
          $('.b-favorites').appendTo('.xs-fav');
          $('.b-lk').appendTo('.xs-lk'); 

        })            
  } else{
         $(function(){ 

          $('.b-search').appendTo('.b-search-wrapper'); 
          $('.b-lang').appendTo('.b-lang-wrapper');
          $('.b-favorites').appendTo('.b-favorites-wrap');
          $('.b-lk').appendTo('.b-lk-wrap');

           
     })        
  }
}
moveMenu();

$(window).resize(function(){
    moveMenu();
});

$('.b-filters-top').click(function(){
  $(this).next().slideToggle('active');
  $(this).toggleClass('active');
});


$('.add-cart').on('click', function(){

  var that = $(this).closest('.b-item').find('.add-cart');
  var bascket = $(".b-cart");
  var w = that.width();
  
       that.clone()
           .css({'width' : w,
    'position' : 'absolute',
    'z-index' : '9999',
    top: that.offset().top,
    left:that.offset().left})
           .appendTo("body")
           .animate({opacity: 0.05,
               left: bascket.offset()['left'],
               top: bascket.offset()['top'],
               width: 20}, 1000, function() { 
        $(this).remove();
      });
});


$('.b-manufacture-slider').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  fade: false,
  variableWidth: false,
  adaptiveHeight: true,
  arrows: false,
  dots: true,
});


$('.b-card-slider__big').slick({
  slidesToShow: 1,
  slidesToScroll: 1,
  fade: false,
  arrows: false,
  asNavFor: '.b-card-slider__pager',
  responsive: [
  {
    breakpoint: 1200,
    settings: {
      slidesToShow: 1,
      slidesToScroll: 1
    }
  },
  {
    breakpoint: 992,
    settings: {
      slidesToShow: 1,
      slidesToScroll: 1
    }
  },
  {
    breakpoint: 768,
    settings: {
      slidesToShow: 1,
      slidesToScroll: 1
    }
  }
  // You can unslick at a given breakpoint now by adding:
  // settings: "unslick"
  // instead of a settings object
]
});
$('.b-card-slider__pager').slick({
  slidesToShow: 4,
  slidesToScroll: 1,
  arrows: false,
  dots: false,
  asNavFor: '.b-card-slider__big',
  focusOnSelect: true,
  variableWidth: false,
  responsive: [
    {
      breakpoint: 1200,
      settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 992,
      settings: {
        slidesToShow: 4,
        slidesToScroll: 1
      }
    },
    {
      breakpoint: 768,
      settings: {
        slidesToShow: 3,
        slidesToScroll: 1
      }
    }
    // You can unslick at a given breakpoint now by adding:
    // settings: "unslick"
    // instead of a settings object
  ]
});


$('.b-card-slider__next').on("click", function() {
    $('.b-card-slider__big').slick("slickNext"); 
})
$('.b-card-slider__prev').on("click", function() {
    $('.b-card-slider__big').slick("slickPrev"); 
})


 $('.b-quantity .minus').click(function() {
    var $input = $(this).parent().find('input');
    var count = parseInt($input.val()) - 1;
    count = count < 1 ? 1 : count;
    $input.val(count);
    $input.change();
    return false;
});
$('.b-quantity .plus').click(function() {
    var $input = $(this).parent().find('input');
    $input.val(parseInt($input.val()) + 1);
    $input.change();
    return false;
}); 

 $('.star-rating').rating(); 

$('.b-order-item__top').click(function(){
  $(this).next().slideToggle('active');
  $(this).toggleClass('active');
});


// google maps

// When the window has finished loading create our google map below
google.maps.event.addDomListener(window, 'load', init2);

function init2() {
    // Basic options for a simple Google Map
    // For more options see: https://developers.google.com/maps/documentation/javascript/reference#MapOptions
    var mapOptions = {
        // How zoomed in you want the map to start at (always required)
        zoom: 15,
        disableDefaultUI: true,

        // The latitude and longitude to center the map (always required)

        center: new google.maps.LatLng(55.772643, 37.678384), // ZP

        // How you would like to style the map. 
        // This is where you would paste any style found on Snazzy Maps.
    };

    // Get the HTML DOM element that will contain your map 
    // We are using a div with id="map" seen below in the <body>
    var mapElement = document.getElementById('map');

    // Create the Google Map using our element and options defined above
    var map = new google.maps.Map(mapElement, mapOptions);

    // Let's also add a marker while we're at it
    var marker = new google.maps.Marker({ 
    position: new google.maps.LatLng(55.772643, 37.678384),
    map: map,
/*    title: 'Snazzy!',
            icon: {
        url: "images/logo.svg",
        scaledSize: new google.maps.Size(60, 60),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(40, 80)
    }*/

    }); 
}



/*
 $("#n5").change(function() {
    if(this.checked) {
      $("#t3").addClass('active');
    }
    else{
      $("#t3").removeClass('active');
    }
});


 $("#n1").change(function() {
    if(this.checked) {
      $("#t3").removeClass('active');
    }
    else{
       $("#t3").addClass('active');
    }
});

 $("#n2").change(function() {
    if(this.checked) {
      $("#t3").removeClass('active');
    }
    else{
       $("#t3").addClass('active');
    }
});
  $("#n3").change(function() {
    if(this.checked) {
      $("#t3").removeClass('active');
    }
    else{
       $("#t3").addClass('active');
    }
});
   $("#n4").change(function() {
    if(this.checked) {
      $("#t3").removeClass('active');
    }
    else{
       $("#t3").addClass('active');
    }
});


$('.b-cart-form__top ul li a').click(function(){
      $('#t3').removeClass('active');
});*/
 




 




});


