/**!
 * Exports various configurations
 */

/**
 * Google Maps config
 * @type {Object}
 */
const map = {
  scrollwheel: false,
  styles: [
    {
      "featureType": "water",
      "elementType": "geometry",
      "stylers": [
        {"color": "#e9e9e9"},
        {"lightness": 17}
      ]
    },
    {
      "featureType": "landscape",
      "elementType": "geometry",
      "stylers": [
        {"color": "#f5f5f5"},
        {"lightness": 20}
      ]
    },
    {
      "featureType": "road.highway",
      "elementType": "geometry.fill",
      "stylers": [
        {"color": "#ffffff"},
        {"lightness": 17}
      ]
    },
    {
      "featureType": "road.highway",
      "elementType": "geometry.stroke",
      "stylers": [
        {"color": "#ffffff"},
        {"lightness": 29},
        {"weight": 0.2}
      ]
    },
    {
      "featureType": "road.arterial",
      "elementType": "geometry",
      "stylers": [
        {"color": "#ffffff"},
        {"lightness": 18}
      ]
    },
    {
      "featureType": "road.local",
      "elementType": "geometry",
      "stylers": [
        {"color": "#ffffff"},
        {"lightness": 16}
      ]
    },
    {
      "featureType": "poi",
      "elementType": "geometry",
      "stylers": [
        {"color": "#f5f5f5"},
        {"lightness": 21}
      ]
    },
    {
      "featureType": "poi.park",
      "elementType": "geometry",
      "stylers": [
        {"color": "#dedede"},
        {"lightness": 21}
      ]
    },
    {
      "elementType": "labels.text.stroke",
      "stylers": [
        {"visibility": "on"},
        {"color": "#ffffff"},
        {"lightness": 16}
      ]
    },
    {
      "elementType": "labels.text.fill",
      "stylers": [
        {"saturation": 36},
        {"color": "#333333"},
        {"lightness": 40}
      ]
    },
    {
      "elementType": "labels.icon",
      "stylers": [
        {"visibility": "off"}
      ]
    },
    {
      "featureType": "transit",
      "elementType": "geometry",
      "stylers": [
        {"color": "#f2f2f2"},
        {"lightness": 19}
      ]
    },
    {
      "featureType": "administrative",
      "elementType": "geometry.fill",
      "stylers": [
        {"color": "#fefefe"},
        {"lightness": 20}
      ]
    },
    {
      "featureType": "administrative",
      "elementType": "geometry.stroke",
      "stylers": [
        {"color": "#fefefe"},
        {"lightness": 17},
        {"weight": 1.2}
      ]
    }
  ]
}

/**
 * Particle animation
 * @type {Object}
 */
const particles = {
  "particles": {
    "number": {
      "value": 60,
      "density": {
        "enable": true,
        "value_area": 900
      }
    },
    "color": {
      "value": "#ffffff"
    },
    "shape": {
      "type": "circle",
      "stroke": {
        "width": 0,
        "color": "#ffffff"
      },
      "polygon": {
        "nb_sides": 5
      },
      "image": {
        "src": "img/github.svg",
        "width": 100,
        "height": 100
      }
    },
    "opacity": {
      "value": 0.3,
      "random": true,
      "anim": {
        "enable": true,
        "speed": 1,
        "opacity_min": 0.1,
        "sync": false
      }
    },
    "size": {
      "value": 5,
      "random": true,
      "anim": {
        "enable": false,
        "speed": 40,
        "size_min": 0.1,
        "sync": false
      }
    },
    "line_linked": {
      "enable": true,
      "distance": 150,
      "color": "#ffffff",
      "opacity": 0.4,
      "width": 1
    },
    "move": {
      "enable": true,
      "speed": 1,
      "direction": "top",
      "random": true,
      "straight": false,
      "out_mode": "out",
      "bounce": false,
      "attract": {
        "enable": true,
        "rotateX": 600,
        "rotateY": 1200
      }
    }
  },
  "interactivity": {
    "detect_on": "canvas",
    "events": {
      "onhover": {
        "enable": true,
        "mode": "grab"
      },
      "onclick": {
        "enable": true,
        "mode": "repulse"
      },
      "resize": true
    },
    "modes": {
      "grab": {
        "distance": 260,
        "line_linked": {
          "opacity": 0.6
        }
      },
      "bubble": {
        "distance": 400,
        "size": 40,
        "duration": 2,
        "opacity": 8,
        "speed": 3
      },
      "repulse": {
        "distance": 200,
        "duration": 0.4
      },
      "push": {
        "particles_nb": 4
      },
      "remove": {
        "particles_nb": 2
      }
    }
  },
  "retina_detect": true
}

/**
 * Export config as object.
 */
const config = {map, particles}

export default config
