/**
* Stylelint config
*
* @author Roelof Roos <github@roelof.io>
* @license MPL-2.0
*/

module.exports = {
  extends: 'stylelint-config-recommended-scss',
  plugins: [
    'stylelint-order'
  ],
  ignoreFiles: [
    'node_modules/*',
    'bower_components/*',
    'vendor/*'
  ],
  rules: {
    'selector-max-id': 0,
    'value-no-vendor-prefix': true,
    'property-no-vendor-prefix': true,
    'selector-no-vendor-prefix': true,
    'media-feature-name-no-vendor-prefix': true,
    'at-rule-no-vendor-prefix': true,
    'font-weight-notation': 'named-where-possible',
    'color-hex-case': 'lower',
    'color-hex-length': 'short',
    'length-zero-no-unit': true,
    'block-opening-brace-newline-after': 'always-multi-line',
    'block-opening-brace-space-after': 'always-single-line',
    'block-closing-brace-newline-before': 'always-multi-line',
    'block-closing-brace-space-before': 'always-single-line',
    'block-opening-brace-space-before': 'always',
    'block-closing-brace-newline-after': 'always',
    'no-missing-end-of-source-newline': true,
    'max-empty-lines': 1,
    'rule-empty-line-before': [
      'always-multi-line',
      {
        except: [
          'after-single-line-comment',
          'first-nested'
        ],
        ignore: [
          'after-comment'
        ]
      }
    ],
    'declaration-colon-space-before': 'never',
    'declaration-colon-space-after': 'always',
    'at-rule-empty-line-before': [
      'always',
      {
        except: [
          'blockless-after-same-name-blockless',
          'first-nested'
        ],
        ignore: [
          'after-comment'
        ]
      }
    ],
    'declaration-block-semicolon-newline-after': 'always-multi-line',
    'no-eol-whitespace': true,
    indentation: 2,
    'unit-whitelist': [
      [
        '%',
        'deg',
        'ms',
        'px',
        'rem',
        's',
        'vh',
        'vmax',
        'vmin',
        'vw'
      ],
      {
        ignoreProperties: {
          em: [
            '/^padding/',
            '/^margin/'
          ]
        }
      }
    ],
    'no-descending-specificity': null,
    'order/order': [
      'declarations',
      'rules'
    ],
    'order/properties-order': [
      [
        'display',
        'visibility',
        'opacity',
        {
          properties: [
            'position',
            'top',
            'right',
            'bottom',
            'left'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'order',
            'flex',
            'flex-flow',
            'flex-direction',
            'flex-wrap',
            'flex-grow',
            'flex-shrink',
            'flex-basis',
            'align-content',
            'align-items',
            'align-self',
            'justify-content',
            'justify-items',
            'justify-self',
            'place-content'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'width',
            'height',
            'min-width',
            'min-height',
            'max-width',
            'max-height'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'margin',
            'margin-top',
            'margin-right',
            'margin-bottom',
            'margin-left',
            'padding',
            'padding-top',
            'padding-right',
            'padding-bottom',
            'padding-left'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'border',
            'border-width',
            'border-color',
            'border-style',
            'border-radius',
            'border-top',
            'border-right',
            'border-bottom',
            'border-left',
            'border-top-width',
            'border-right-width',
            'border-bottom-width',
            'border-left-width',
            'border-top-color',
            'border-right-color',
            'border-bottom-color',
            'border-left-color',
            'border-top-style',
            'border-right-style',
            'border-bottom-style',
            'border-left-style',
            'border-top-left-radius',
            'border-top-right-radius',
            'border-bottom-right-radius',
            'border-bottom-left-radius'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'background',
            'background-color',
            'background-image',
            'background-repeat',
            'background-position',
            'background-clip',
            'background-size',
            'background-attachment'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'color',
            'text-align',
            'font',
            'font-family',
            'font-size',
            'font-weight',
            'font-style',
            'font-variant'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'word-break',
            'word-wrap',
            'word-spacing'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'transform'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'transition',
            'transition-property',
            'transition-duration',
            'transition-delay',
            'transition-timing-function'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'animation',
            'animation-name',
            'animation-duration',
            'animation-delay',
            'animation-direction',
            'animation-fill-mode',
            'animation-iteration-count',
            'animation-play-state',
            'animation-timing-function'
          ],
          emptyLineBefore: 'never'
        },
        {
          properties: [
            'word-break',
            'word-wrap',
            'word-spacing'
          ],
          emptyLineBefore: 'never'
        }
      ],
      {
        unspecified: 'bottomAlphabetical'
      }
    ]
  }
}
