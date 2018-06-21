/**
 * Template functions
 *
 * @author web-master72
 * @copyright SEE /NOTICE.md
 */

import jQuery from 'jquery'

const $ = jQuery

/* ---------------------------------------------- /*
* Preloader
/* ---------------------------------------------- */

document.addEventListener('load', () => {
  document.querySelector('body > .layout').classList.add('fade-in')
}, { once: true })

$(document).ready(() => {
  /* ---------------------------------------------- /*
  * Sticky Sidebar Portfolio
  /* ---------------------------------------------- */

  $('.sticky-sidebar').imagesLoaded(function () {
    $('.sticky-sidebar').stick_in_parent({
      offset_top: 80,
      recalc_every: 1
    })
      .on('sticky_kit:bottom', function (e) {
        $(this).parent().css('position', 'relative')
      })
      .on('sticky_kit:unbottom', function (e) {
        $(this).parent().css('position', 'relative')
      }).scroll()
  })

  /* ---------------------------------------------- /*
  * Mobile detect
  /* ---------------------------------------------- */

  var mobileTest

  if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
    mobileTest = true
  } else {
    mobileTest = false
  }

  /* ---------------------------------------------- /*
  * Header animation
  /* ---------------------------------------------- */

  var header = $('.header')

  $(window).scroll(function () {
    var scroll = $(window).scrollTop()
    if (scroll >= 20) {
      header.addClass('header-small')
      header.addClass('header-shadow')
    } else {
      header.removeClass('header-small')
      header.removeClass('header-shadow')
    }
    if (($('.module-header').length <= 0) && ($('.module-slides').length <= 0) && ($('.flexslider').length <= 0)) {
      header.addClass('header-small')
    };
  }).scroll()

  /* ---------------------------------------------- /*
  * Light/dark header
  /* ---------------------------------------------- */

  var moduleHeader = $('.module-header')

  if (moduleHeader.length >= 0) {
    if (moduleHeader.hasClass('bg-dark')) {
      header.addClass('header-light')
    } else {
      header.removeClass('header-light')
    }
  }

  /* ---------------------------------------------- /*
  * One Page Nav
  /* ---------------------------------------------- */

  $('.onepage-nav').singlePageNav({
    currentClass: 'active',
    filter: ':not(.external)'
  })

  /* ---------------------------------------------- /*
  * Collapse navbar on click
  /* ---------------------------------------------- */

  $(document).on('click', '.inner-navigation.show', function (e) {
    if ($(e.target).is('a') && !$(e.target).parent().hasClass('menu-item-has-children')) {
      $(this).collapse('hide')
    }
  })

  /* ---------------------------------------------- /*
  * Ripples
  /* ---------------------------------------------- */

  if (!mobileTest) {
    if ($('.ripples').length > 0) {
      $('.ripples').each(function () {
        $(this).ripples($.extend({
          resolution: 500,
          dropRadius: 30,
          perturbance: 0.04
        }, $(this).data('ripples-options')))
      })
    }
  }

  /* ---------------------------------------------- /*
  * Intro slider setup
  /* ---------------------------------------------- */

  if ($('.slides-container li').length > 1) {
    $('.module-slides').superslides({
      play: 10000,
      animation: 'fade',
      animation_speed: 800,
      pagination: true
    })

    $(document).on('animated.slides', function () {
      heroSuperSliderLight()
    })
  } else if ($('.slides-container li').length === 1) {
    $('.module-slides').superslides()
    heroSuperSliderLight()
  }

  function heroSuperSliderLight () {
    var currentSlide = $('.module-slides').superslides('current')
    var $this = $('.slides-container li').eq(currentSlide)
    $('.slides-container li').removeClass('active-slide')
    $this.addClass('active-slide')
    if ($('.slides-container li.bg-dark').hasClass('active-slide')) {
      header.addClass('header-light')
      $('.module-slides').removeClass('dark-nav')
    } else {
      header.removeClass('header-light')
      $('.module-slides').addClass('dark-nav')
    }
  }

  /* ---------------------------------------------- /*
  * Flexslider
  /* ---------------------------------------------- */

  if ($('.flexslider').length > 0) {
    $('.flexslider').flexslider({
      animation: 'fade',
      animationSpeed: 1000,
      slideshowSpeed: 9000,
      animationLoop: true,
      prevText: ' ',
      nextText: ' ',
      start: function (slider) {
        heroSliderLight()
      },
      before: function (slider) {
        if (!mobileTest) {
          $('.flexslider .container').fadeOut(500).animate({ opacity: '0' }, 500)
          slider.slides.eq(slider.currentSlide).delay(500)
          slider.slides.eq(slider.animatingTo).delay(500)
        }
      },
      after: function (slider) {
        heroSliderLight()
        if (!mobileTest) {
          $('.flexslider .container').fadeIn(500).animate({ opacity: '1' }, 500)
        }
      },
      useCSS: true
    })
  };

  function heroSliderLight () {
    if ($('li.bg-dark').hasClass('flex-active-slide')) {
      header.addClass('header-light')
      $('.flexslider').removeClass('dark-nav')
    } else {
      header.removeClass('header-light')
      $('.flexslider').addClass('dark-nav')
    }
  }

  /* ---------------------------------------------- /*
  * Rotate
  /* ---------------------------------------------- */

  $('[data-effect=rotate]').textrotator({
    animation: 'dissolve',
    separator: '|',
    speed: 3000
  })

  /* ---------------------------------------------- /*
  * Setting background of modules
  /* ---------------------------------------------- */

  $('[data-background]').each(function () {
    $(this).css('background-image', 'url(' + $(this).attr('data-background') + ')')
  })

  $('[data-background-color]').each(function () {
    $(this).css('background-color', $(this).attr('data-background-color'))
  })

  $('[data-color]').each(function () {
    $(this).css('color', $(this).attr('data-color'))
  })

  $('[data-mY]').each(function () {
    $(this).css('margin-top', $(this).attr('data-mY'))
  })

  /* ---------------------------------------------- /*
  * Slides
  /* ---------------------------------------------- */

  $('.clients-carousel').each(function () {
    $(this).owlCarousel($.extend({
      navigation: false,
      pagination: false,
      autoPlay: 3000,
      items: 4,
      navigationText: [
        '<i class="fa fa-angle-left"></i>',
        '<i class="fa fa-angle-right"></i>'
      ]
    }, $(this).data('carousel-options')))
  })

  $('.tms-carousel').each(function () {
    $(this).owlCarousel($.extend({
      navigation: false,
      pagination: true,
      autoPlay: 3000,
      items: 3,
      navigationText: [
        '<span class="arrows arrows-arrows-slim-left"></span>',
        '<span class="arrows arrows-arrows-slim-right"></span>'
      ]
    }, $(this).data('carousel-options')))
  })

  $('.tms-slides').each(function () {
    $(this).owlCarousel($.extend({
      autoPlay: 5000,
      navigation: false,
      singleItem: true,
      pagination: true,
      paginationSpeed: 1000,
      navigationText: [
        '<span class="arrows arrows-arrows-slim-left"></span>',
        '<span class="arrows arrows-arrows-slim-right"></span>'
      ]
    }, $(this).data('carousel-options')))
  })

  $('.image-slider').each(function () {
    $(this).owlCarousel($.extend({
      stopOnHover: true,
      navigation: true,
      pagination: true,
      autoPlay: 3000,
      singleItem: true,
      items: 1,
      navigationText: [
        '<span class="arrows arrows-arrows-slim-left"></span>',
        '<span class="arrows arrows-arrows-slim-right"></span>'
      ]
    }, $(this).data('carousel-options')))
  })

  /* ---------------------------------------------- /*
  * Lightbox, Gallery
  /* ---------------------------------------------- */

  $('.lightbox').magnificPopup({
    type: 'image'
  })

  $('[rel=single-photo]').magnificPopup({
    type: 'image',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0, 1]
    }
  })

  $('.gallery [rel=gallery]').magnificPopup({
    type: 'image',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0, 1]
    },
    image: {
      titleSrc: 'title',
      tError: 'The image could not be loaded.'
    }
  })

  $('.lightbox-video').magnificPopup({
    type: 'iframe'
  })

  $('a.product-gallery').magnificPopup({
    type: 'image',
    gallery: {
      enabled: true,
      navigateByImgClick: true,
      preload: [0, 1]
    },
    image: {
      titleSrc: 'title',
      tError: 'The image could not be loaded.'
    }
  })

  /* ---------------------------------------------- /*
  * Tooltips
  /* ---------------------------------------------- */

  $(function () {
    $('[data-toggle="tooltip"]').tooltip()
  })

  /* ---------------------------------------------- /*
  * A jQuery plugin for fluid width video embeds
  /* ---------------------------------------------- */

  $('body').fitVids()

  /* ---------------------------------------------- /*
  * Scroll Animation
  /* ---------------------------------------------- */

  $('.smoothscroll').on('click', function (e) {
    var target = this.hash
    var $target = $(target)

    $('html, body').stop().animate({
      'scrollTop': $target.offset().top - header.height()
    }, 600, 'swing')

    e.preventDefault()
  })

  /* ---------------------------------------------- /*
  * Scroll top
  /* ---------------------------------------------- */

  $(window).scroll(function () {
    if ($(this).scrollTop() > 100) {
      $('.scroll-top').addClass('scroll-top-visible')
    } else {
      $('.scroll-top').removeClass('scroll-top-visible')
    }
  })

  $('a[href="#top"]').on('click', function () {
    $('html, body').animate({ scrollTop: 0 }, 'slow')
    return false
  })

  /* ---------------------------------------------- /*
  * Parallax
  /* ---------------------------------------------- */

  $('.parallax').jarallax({
    speed: 0.4
  })
})
