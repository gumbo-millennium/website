/*!
 *	Template Functions
*/

(function ($) {

  "use strict";

	/* ---------------------------------------------- /*
	 * Preloader
	/* ---------------------------------------------- */

  $(window).on('load', function () {

    $('.layout').addClass('fade-in');

		/* ---------------------------------------------- /*
		 * WOW Animation on page load
		/* ---------------------------------------------- */

    var wow = new WOW({
      mobile: false
    });

    wow.init();

  });

  $(document).ready(function () {

		/* ---------------------------------------------- /*
		 * Sticky Sidebar Portfolio
		/* ---------------------------------------------- */

    $('.sticky-sidebar').imagesLoaded(function () {
      $('.sticky-sidebar').stick_in_parent({
        offset_top: 80,
        recalc_every: 1
      })
        .on('sticky_kit:bottom', function (e) {
          $(this).parent().css('position', 'relative');
        })
        .on('sticky_kit:unbottom', function (e) {
          $(this).parent().css('position', 'relative');
        }).scroll();
    });

		/* ---------------------------------------------- /*
		 * Mobile detect
		/* ---------------------------------------------- */

    var mobileTest;

    if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
      mobileTest = true;
    } else {
      mobileTest = false;
    }

		/* ---------------------------------------------- /*
		 * Header animation
		/* ---------------------------------------------- */

    var header = $('.header');

    $(window).scroll(function () {
      var scroll = $(window).scrollTop();
      if (scroll >= 20) {
        header.addClass('header-small');
        header.addClass('header-shadow');
      } else {
        header.removeClass('header-small');
        header.removeClass('header-shadow');
      }
      if (($('.module-header').length <= 0) && ($('.module-slides').length <= 0) && ($('.flexslider').length <= 0)) {
        header.addClass('header-small');
      };
    }).scroll();

		/* ---------------------------------------------- /*
		 * Light/dark header
		/* ---------------------------------------------- */

    var module_header = $('.module-header');

    if (module_header.length >= 0) {
      if (module_header.hasClass('bg-dark')) {
        header.addClass('header-light');
      } else {
        header.removeClass('header-light');
      }
    }

		/* ---------------------------------------------- /*
		 * One Page Nav
		/* ---------------------------------------------- */

    $('.onepage-nav').singlePageNav({
      currentClass: 'active',
      filter: ':not(.external)'
    });

		/* ---------------------------------------------- /*
		 * Collapse navbar on click
		/* ---------------------------------------------- */

    $(document).on('click', '.inner-navigation.show', function (e) {
      if ($(e.target).is('a') && !$(e.target).parent().hasClass('menu-item-has-children')) {
        $(this).collapse('hide');
      }
    });

		/* ---------------------------------------------- /*
		 * Ripples
		/* ---------------------------------------------- */

    if (mobileTest != true) {
      if ($('.ripples').length > 0) {
        $('.ripples').each(function () {
          $(this).ripples($.extend({
            resolution: 500,
            dropRadius: 30,
            perturbance: 0.04,
          }, $(this).data('ripples-options')));
        });
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
        pagination: true,
      });

      $(document).on('animated.slides', function () {
        heroSuperSliderLight();
      });

    } else if ($('.slides-container li').length == 1) {
      $('.module-slides').superslides();
      heroSuperSliderLight();
    }

    function heroSuperSliderLight() {
      var currentSlide = $('.module-slides').superslides('current');
      var $this = $('.slides-container li').eq(currentSlide);
      $('.slides-container li').removeClass('active-slide');
      $this.addClass('active-slide');
      if ($('.slides-container li.bg-dark').hasClass('active-slide')) {
        header.addClass('header-light');
        $('.module-slides').removeClass('dark-nav')
      } else {
        header.removeClass('header-light');
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
          heroSliderLight();
        },
        before: function (slider) {
          if (mobileTest != true) {
            $('.flexslider .container').fadeOut(500).animate({ opacity: '0' }, 500);
            slider.slides.eq(slider.currentSlide).delay(500);
            slider.slides.eq(slider.animatingTo).delay(500);
          }
        },
        after: function (slider) {
          heroSliderLight();
          if (mobileTest != true) {
            $('.flexslider .container').fadeIn(500).animate({ opacity: '1' }, 500);
          }
        },
        useCSS: true
      });
    };

    function heroSliderLight() {
      if ($('li.bg-dark').hasClass('flex-active-slide')) {
        header.addClass('header-light');
        $('.flexslider').removeClass('dark-nav')
      } else {
        header.removeClass('header-light');
        $('.flexslider').addClass('dark-nav')
      }
    }

		/* ---------------------------------------------- /*
		 * Rotate
		/* ---------------------------------------------- */

    $(".rotate").textrotator({
      animation: "dissolve",
      separator: "|",
      speed: 3000
    });

		/* ---------------------------------------------- /*
		 * Setting background of modules
		/* ---------------------------------------------- */

    $('[data-background]').each(function () {
      $(this).css('background-image', 'url(' + $(this).attr('data-background') + ')');
    });

    $('[data-background-color]').each(function () {
      $(this).css('background-color', $(this).attr('data-background-color'));
    });

    $('[data-color]').each(function () {
      $(this).css('color', $(this).attr('data-color'));
    });

    $('[data-mY]').each(function () {
      $(this).css('margin-top', $(this).attr('data-mY'));
    });

		/* ---------------------------------------------- /*
		 * Portfolio masonry
		/* ---------------------------------------------- */

    var filters = $('#filters'),
      worksgrid = $('.row-portfolio');

    $('a', filters).on('click', function () {
      var selector = $(this).attr('data-filter');
      $('.current', filters).removeClass('current');
      $(this).addClass('current');
      worksgrid.isotope({
        filter: selector
      });
      return false;
    });

    $(window).on('resize', function () {
      worksgrid.imagesLoaded(function () {
        worksgrid.isotope({
          layoutMode: 'masonry',
          itemSelector: '.portfolio-item, .portfolio-item-classic ',
          transitionDuration: '0.4s',
          masonry: {
            columnWidth: '.grid-sizer',
          },
        });
      });
    }).resize();

		/* ---------------------------------------------- /*
		 * Blog masonry
		/* ---------------------------------------------- */

    $(window).on('resize', function () {
      setTimeout(function () {
        $('.blog-masonry').isotope({
          layoutMode: 'masonry',
          transitionDuration: '0.8s',
        });
      }, 1000);
    });

		/* ---------------------------------------------- /*
		 * Shop masonry
		/* ---------------------------------------------- */

    $(window).on('resize', function () {
      $('.row-shop-masonry').isotope({
        layoutMode: 'masonry',
        transitionDuration: '0.4s',
      });
    });

		/* ---------------------------------------------- /*
		 * Open & Close shop cart
		/* ---------------------------------------------- */

    $('.open-offcanvas, .close-offcanvas').on('click', function () {
      $('body').toggleClass('off-canvas-sidebar-open');
      return false;
    });

    $(document).on('click', function (e) {
      var container = $('.off-canvas-sidebar');
      if (!container.is(e.target) && container.has(e.target).length === 0) {
        $('body').removeClass('off-canvas-sidebar-open');
      }
    });

    function getScrollBarWidth() {
      var inner = document.createElement('p');
      inner.style.width = "100%";
      inner.style.height = "200px";
      var outer = document.createElement('div');
      outer.style.position = "absolute";
      outer.style.top = "0px";
      outer.style.left = "0px";
      outer.style.visibility = "hidden";
      outer.style.width = "200px";
      outer.style.height = "150px";
      outer.style.overflow = "hidden";
      outer.appendChild(inner);
      document.body.appendChild(outer);
      var w1 = inner.offsetWidth;
      outer.style.overflow = 'scroll';
      var w2 = inner.offsetWidth;
      if (w1 == w2) w2 = outer.clientWidth;
      document.body.removeChild(outer);
      return (w1 - w2);
    };

    $('.off-canvas-sidebar-wrapper').css('margin-right', '-' + getScrollBarWidth() + 'px');

    $(window).on('resize', function () {
      var width = Math.max($(window).width(), window.innerWidth);

      if (width <= 991) {
        $('body').removeClass('off-canvas-sidebar-open');
      }
    });

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
        ],
      }, $(this).data('carousel-options')));
    });

    $('.tms-carousel').each(function () {
      $(this).owlCarousel($.extend({
        navigation: false,
        pagination: true,
        autoPlay: 3000,
        items: 3,
        navigationText: [
          '<span class="arrows arrows-arrows-slim-left"></span>',
          '<span class="arrows arrows-arrows-slim-right"></span>'
        ],
      }, $(this).data('carousel-options')));
    });

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
        ],
      }, $(this).data('carousel-options')));
    });

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
        ],
      }, $(this).data('carousel-options')));
    });

		/* ---------------------------------------------- /*
		 * Progress bars, counters, pie charts animations
		/* ---------------------------------------------- */

    $('.progress-bar').each(function () {
      $(this).appear(function () {
        var percent = $(this).attr('aria-valuenow');
        $(this).animate({ 'width': percent + '%' });
        $(this).find('.pb-number-box').animate({ 'opacity': 1 }, 1000);
        $(this).find('.pb-number').countTo({
          from: 0,
          to: percent,
          speed: 900,
          refreshInterval: 30
        });
      });
    });

    $('.counter-timer').each(function () {
      $(this).appear(function () {
        var number = $(this).attr('data-to');
        $(this).countTo({
          from: 0,
          to: number,
          speed: 1500,
          refreshInterval: 10,
          formatter: function (number, options) {
            number = number.toFixed(options.decimals);
            number = number.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return number;
          }
        });
      });
    });

    $('.chart').each(function () {
      $(this).appear(function () {
        $(this).easyPieChart($.extend({
          barColor: '#111111',
          trackColor: '#eee',
          scaleColor: false,
          lineCap: 'round',
          lineWidth: 5,
          size: 180,
        }, $(this).data('chart-options')));

        var number = $(this).attr('data-percent');
        $(this).find('.chart-text span').countTo({
          from: 0,
          to: number,
          speed: 1000,
          refreshInterval: 10,
          formatter: function (number, options) {
            number = number.toFixed(options.decimals);
            number = number.replace(/\B(?=(\d{3})+(?!\d))/g, ',');
            return number;
          }
        });
      });
    });

		/* ---------------------------------------------- /*
		 * Twitter
		/* ---------------------------------------------- */

    $('.twitter-feed').each(function (index) {
      $(this).attr('id', 'twitter-' + index);

      var twitterID = $(this).data('twitter');
      var twitterMax = $(this).data('number');
      var twitter_config = {
        'id': twitterID,
        'domId': 'twitter-' + index,
        'maxTweets': twitterMax,
        'enableLinks': true,
        'showPermalinks': false
      };
      twitterFetcher.fetch(twitter_config);
    });

		/* ---------------------------------------------- /*
		 * Lightbox, Gallery
		/* ---------------------------------------------- */

    $('.lightbox').magnificPopup({
      type: 'image'
    });

    $('[rel=single-photo]').magnificPopup({
      type: 'image',
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        preload: [0, 1]
      },
    });

    $('.gallery [rel=gallery]').magnificPopup({
      type: 'image',
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        preload: [0, 1]
      },
      image: {
        titleSrc: 'title',
        tError: 'The image could not be loaded.',
      }
    });

    $('.lightbox-video').magnificPopup({
      type: 'iframe',
    });

    $('a.product-gallery').magnificPopup({
      type: 'image',
      gallery: {
        enabled: true,
        navigateByImgClick: true,
        preload: [0, 1]
      },
      image: {
        titleSrc: 'title',
        tError: 'The image could not be loaded.',
      }
    });

		/* ---------------------------------------------- /*
		 * Tooltips
		/* ---------------------------------------------- */

    $(function () {
      $('[data-toggle="tooltip"]').tooltip()
    });

		/* ---------------------------------------------- /*
		 * A jQuery plugin for fluid width video embeds
		/* ---------------------------------------------- */

    $('body').fitVids();

		/* ---------------------------------------------- /*
		 * Google Map
		/* ---------------------------------------------- */

    $('.map').each(function () {

      var reg_exp = /\[[^(\]\[)]*\]/g;

      var map_div = $(this);
      var is_draggable = Math.max($(window).width(), window.innerWidth) > 736 ? true : false;

      if (map_div.length > 0) {

        var markers_addresses = map_div[0].getAttribute('data-addresses').match(reg_exp),
          markers_info = map_div[0].getAttribute('data-info').match(reg_exp),
          markers_icon = map_div.data('icon'),
          map_zoom = map_div.data('zoom');

        var markers_values = [], map_center;

        markers_addresses.forEach(function (marker_address, index) {
          var marker_value = '{'
          marker_value += '"latLng":' + marker_address;
          if (index == 0) {
            map_center = JSON.parse(marker_address);
          };
          if (markers_info[index]) {
            var marker_data = markers_info[index].replace(/\[|\]/g, '');
            marker_value += ', "data":"' + marker_data + '"';
          };
          marker_value += '}';
          markers_values.push(JSON.parse(marker_value));
        });

        var map_options = {
          scrollwheel: false,
          styles: [{ "featureType": "water", "elementType": "geometry", "stylers": [{ "color": "#e9e9e9" }, { "lightness": 17 }] }, { "featureType": "landscape", "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }, { "lightness": 20 }] }, { "featureType": "road.highway", "elementType": "geometry.fill", "stylers": [{ "color": "#ffffff" }, { "lightness": 17 }] }, { "featureType": "road.highway", "elementType": "geometry.stroke", "stylers": [{ "color": "#ffffff" }, { "lightness": 29 }, { "weight": 0.2 }] }, { "featureType": "road.arterial", "elementType": "geometry", "stylers": [{ "color": "#ffffff" }, { "lightness": 18 }] }, { "featureType": "road.local", "elementType": "geometry", "stylers": [{ "color": "#ffffff" }, { "lightness": 16 }] }, { "featureType": "poi", "elementType": "geometry", "stylers": [{ "color": "#f5f5f5" }, { "lightness": 21 }] }, { "featureType": "poi.park", "elementType": "geometry", "stylers": [{ "color": "#dedede" }, { "lightness": 21 }] }, { "elementType": "labels.text.stroke", "stylers": [{ "visibility": "on" }, { "color": "#ffffff" }, { "lightness": 16 }] }, { "elementType": "labels.text.fill", "stylers": [{ "saturation": 36 }, { "color": "#333333" }, { "lightness": 40 }] }, { "elementType": "labels.icon", "stylers": [{ "visibility": "off" }] }, { "featureType": "transit", "elementType": "geometry", "stylers": [{ "color": "#f2f2f2" }, { "lightness": 19 }] }, { "featureType": "administrative", "elementType": "geometry.fill", "stylers": [{ "color": "#fefefe" }, { "lightness": 20 }] }, { "featureType": "administrative", "elementType": "geometry.stroke", "stylers": [{ "color": "#fefefe" }, { "lightness": 17 }, { "weight": 1.2 }] }]
        };

        map_options.center = map_center;
        map_options.zoom = map_zoom;
        map_options.draggable = is_draggable;

        var markers_options = {};
        markers_options.icon = markers_icon;

        map_div.gmap3({
          map: {
            options:
              map_options
          },
          marker: {
            values: markers_values,
            options: markers_options,
            events: {
              click: function (marker, event, context) {
                if (context.data) {
                  var map = $(this).gmap3("get"),
                    infowindow = $(this).gmap3({ get: { name: "infowindow" } });
                  if (infowindow) {
                    infowindow.open(map, marker);
                    infowindow.setContent(context.data);
                  } else {
                    $(this).gmap3({
                      infowindow: {
                        anchor: marker,
                        options: { content: context.data }
                      }
                    });
                  }
                }
              }
            }
          }
        });

      };
    });

		/* ---------------------------------------------- /*
		 * Scroll Animation
		/* ---------------------------------------------- */

    $('.smoothscroll').on('click', function (e) {
      var target = this.hash;
      var $target = $(target);

      $('html, body').stop().animate({
        'scrollTop': $target.offset().top - header.height()
      }, 600, 'swing');

      e.preventDefault();
    });10

		/* ---------------------------------------------- /*
		 * Scroll top
		/* ---------------------------------------------- */

    $(window).scroll(function () {
      if ($(this).scrollTop() > 100) {
        $('.scroll-top').addClass('scroll-top-visible');
      } else {
        $('.scroll-top').removeClass('scroll-top-visible');
      }
    });

    $('a[href="#top"]').on('click', function () {
      $('html, body').animate({ scrollTop: 0 }, 'slow');
      return false;
    });

		/* ---------------------------------------------- /*
		 * Disable hover on scroll
		/* ---------------------------------------------- */

    var body = document.body,
      timer;
    window.addEventListener('scroll', function () {
      clearTimeout(timer);
      if (!body.classList.contains('disable-hover')) {
        body.classList.add('disable-hover')
      }
      timer = setTimeout(function () {
        body.classList.remove('disable-hover')
      }, 100);
    }, false);

		/* ---------------------------------------------- /*
		 * Parallax
		/* ---------------------------------------------- */

    $('.parallax').jarallax({
      speed: 0.4
    });

  });

})(jQuery);
