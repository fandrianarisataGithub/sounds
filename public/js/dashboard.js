/*global $, console*/
/*
  By Mostafa Omar
	https://www.facebook.com/MostafaOmarIbrahiem
*/
/* pour l'annimation de loader */ 


$(document).ready(function(){
  setTimeout(function(){
    $('body').addClass('loaded');
  }, 2000);
})

/* fin loader */


$(function () {

  'use strict';

  (function () {

    var aside = $('.side-nav'),

        showAsideBtn = $('.show-side-btn'),

        contents = $('#contents');

    showAsideBtn.on("click", function () {

      $("#" + $(this).data('show')).toggleClass('show-side-nav');

      contents.toggleClass('margin');

    });

    if ($(window).width() <= 767) {

      aside.addClass('show-side-nav');

    }
    $(window).on('resize', function () {

      if ($(window).width() > 767) {

        aside.removeClass('show-side-nav');

      }

    });

    // dropdown menu in the side nav
    var slideNavDropdown = $('.side-nav-dropdown');

    $('.side-nav .categories li').on('click', function () {

      $(this).toggleClass('opend').siblings().removeClass('opend');

      if ($(this).hasClass('opend')) {

        $(this).find('.side-nav-dropdown').slideToggle('fast');

        $(this).siblings().find('.side-nav-dropdown').slideUp('fast');

      } else {

        $(this).find('.side-nav-dropdown').slideUp('fast');

      }

    });

    $('.side-nav .close-aside').on('click', function () {

      $('#' + $(this).data('close')).addClass('show-side-nav');
      contents.removeClass('margin');

    });


  }());

  // hover for li 

  $("#aside2 > ul > li").hover(
    function() {
      var img_actus = $(this).children().children().children(".nav_icone") ;
      var img_cache = $(this).children().children().children(".nav_icone_hover") ;
      var src_nav_icone_a = img_actus.attr("src") ;
      var src_nav_icone_h = img_cache.attr("src") ;
      var t = src_nav_icone_a ;
      src_nav_icone_a = src_nav_icone_h ;
      src_nav_icone_h = t ;
      img_actus.attr('src',src_nav_icone_a) ;
      img_cache.attr('src',src_nav_icone_h) ;
      
    } ,
    function() {
       var img_actus = $(this).children().children().children(".nav_icone") ;
      var img_cache = $(this).children().children().children(".nav_icone_hover") ;
      var src_nav_icone_a = img_actus.attr("src") ;
      var src_nav_icone_h = img_cache.attr("src") ;
      var t = src_nav_icone_a ;
      src_nav_icone_a = src_nav_icone_h ;
      src_nav_icone_h = t ;
      img_actus.attr('src',src_nav_icone_a) ;
      img_cache.attr('src',src_nav_icone_h) ;
    }

    )
  $("#aside2 > ul > li#li_sitting").hover(
      function(){
        $(this).children().children(".fa").css("color","#d29e00") ;
      } ,
        function(){
        $(this).children().children(".fa").css("color","#fff") ;
      }
    )
  $("#aside2 > ul > li.li_autre").hover(
      function(){
        $(this).children().children(".fa").css("color","#d29e00") ;
      } ,
        function(){
        $(this).children().children(".fa").css("color","#fff") ;
      }
    )

  // dropdown dans contentss avec tableau
 $('.dropdown-menu li').on('click', function() {
    var getValue = $(this).text();
    $(this).parent().siblings("button").children('.date_selected').text(getValue);
  });

 if($(window).width()<=480) {
  $('.dropdown-menu').css( "min-width" ,"100px !important") ;
 }
  // fin dropdown dans contentss avec tableau

  /* animation suppl pour le modal de suppression client */

   $("#modif_date_modal").click(function(e){
      e.preventDefault();
      $("#modal_form").modal("hide") ;

    })

  /* fin animation suppl pour le modal de suppression client */

  /*Animation lien historique */
 

  $(' #li_historique a').on("click", function(e) {
     
      if($('#li_historique a span:last-child').hasClass('fa-angle-down')) {
        //alert('nisy')
        $('#li_historique a span:last-child').removeClass('fa-angle-down') ;
        $('#li_historique a span:last-child').addClass('fa-angle-up') ;
        _liste() ;
      }
      else if($('#li_historique a span:last-child').hasClass('fa-angle-up')) {
        //alert('nisy')
        $('#li_historique a span:last-child').removeClass('fa-angle-up') ;
        $('#li_historique a span:last-child').addClass('fa-angle-down') ;
        _liste() ;
      }

  })


  function _liste() {
    $('.ul_historique').slideToggle() ;
  }

  /*fin animation historique */

  



});