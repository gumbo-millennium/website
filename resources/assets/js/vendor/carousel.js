/**
 * Handles carousel configuration
 *
 * @author Roelof Roos <github@roelof.io>
 */

import jQuery from 'jquery'
import isMobile from './mobiletest'

// get elements
const header = jQuery('.header');
const sliders = jQuery('.flexslider');

const heroSliderLight = () => {
  if (jQuery('li.bg-dark').hasClass('flex-active-slide')) {
    header.addClass('header-light');
    jQuery('.flexslider').removeClass('dark-nav')
  } else {
    header.removeClass('header-light');
    jQuery('.flexslider').addClass('dark-nav')
  }
}

if (sliders.length > 0) {
  sliders.flexslider({
    animation: 'fade',
    animationSpeed: 1000,
    slideshowSpeed: 9000,
    animationLoop: true,
    prevText: ' ',
    nextText: ' ',
    start: () => {
      heroSliderLight()
    },
    before: (slider) => {
      if (!isMobile) {
        jQuery('.flexslider .container').fadeOut(500).animate({
          opacity: '0'
        }, 500);
        slider.slides.eq(slider.currentSlide).delay(500);
        slider.slides.eq(slider.animatingTo).delay(500);
      }
    },
    after: (slider) => {
      heroSliderLight();
      if (!isMobile) {
        jQuery('.flexslider .container').fadeIn(500).animate({
          opacity: '1'
        }, 500);
      }
    },
    useCSS: true
  });
};
