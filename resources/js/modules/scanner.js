import SHA256 from 'crypto-js/sha256'
import QrScanner from 'qr-scanner'

const cssMap = {
  default: ['bg-gray-800', 'border-gray-700'],
  valid: ['bg-brand-700', 'border-brand-600'],
  consumed: ['bg-orange-800', 'border-orange-700'],
  invalid: ['bg-red-900', 'border-red-800'],
}

const allCssClasses = Object.values(cssMap).flat()

class Scanner {
  constructor (domNode) {
    // Properties
    this.salt = null
    this.barcodes = []
    this.lastScannedBarcode = null

    // URLs
    this.indexUrl = domNode.dataset.indexUrl
    this.preloadUrl = domNode.dataset.preloadUrl
    this.consumeUrl = domNode.dataset.consumeUrl
    this.csrfToken = domNode.dataset.csrfToken

    // DOMNodes
    this.domNode = domNode
    this.loadingNode = domNode.querySelector('[data-content=loading]')
    this.loadingReasonNode = this.loadingNode.querySelector('[data-content="loading-reason"]')
    this.resultNode = domNode.querySelector('[data-content=result]')
    this.barcodeNode = domNode.querySelector('[data-content=barcode]')

    // Find first child of the body that contains the scanner
    const body = document.body
    let bodyNodeContainingScanner = domNode
    while (bodyNodeContainingScanner.parentNode !== body) {
      bodyNodeContainingScanner = bodyNodeContainingScanner.parentNode
    }

    // Mark node as relative
    bodyNodeContainingScanner.classList.add('relative')

    const videoParentNode = document.createElement('div')
    videoParentNode.classList.add(
      'scanner-video',
      'overflow-hidden',
      'absolute', 'inset-0',
      'w-screen', 'h-screen',
      'flex', 'items-center', 'justify-center',
    )
    body.insertBefore(videoParentNode, bodyNodeContainingScanner)

    this.videoNode = document.createElement('video')
    this.videoNode.classList.add('w-screen', 'h-screen', 'object-cover', 'scanner-camera')
    videoParentNode.appendChild(this.videoNode)

    // Set loading state
    this._setLoading('Toegangstoken ophalen...')
    this._preload(true)

    // Update preload list every 5 minutes
    setInterval(() => this._preload(false), 60 * 5 * 1000)

    // Start camera
    this.camera = new QrScanner(this.videoNode, result => this._foundBarcode(result), {
      returnDetailedScanResult: true,
      highlightScanRegion: true,
      highlightCodeOutline: true,
    })
    this._startCamera()

    // Bind to changes in visibility
    document.addEventListener('visibilitychange', this._handleVisibilityChange.bind(this))
  }

  /**
   * Preload a hashed, abbreviated set of barcodes
   * @param {boolean} impactLoading
   */
  _preload (impactLoading) {
    if (impactLoading) {
      this._setLoading('Barcodes ophalen...')
    }

    fetch(this.preloadUrl, {
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': this.csrfToken,
      },
    })
      .then(response => response.json())
      .then(({ ok, data }) => {
        if (!ok) {
          document.location.href = this.indexUrl
          return
        }

        this.salt = data.salt
        this.barcodes = data.barcodes

        if (impactLoading) {
          this._setLoading(false)
          this.camera.start()
        }
      })
  }

  _consume (barcode) {
    fetch(this.consumeUrl, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'X-CSRF-TOKEN': this.csrfToken,
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        barcode,
      }),
    })
      .then(response => response.json())
      .then(({ ok, code }) => {
        if (code === 400) {
          document.location.href = this.indexUrl
          this.camera.stop()
        }

        this._setLoading(false)
        this._setResult(ok ? 'valid' : (code === 409 ? 'consumed' : 'invalid'))
      })
      .catch(() => {
        this._setLoading(false)
        this._setResult('invalid')
      })
  }

  _setLoading (loading) {
    if (!loading) {
      this.loading = false
      this.loadingNode.classList.add('hidden')
      return
    }

    this.loading = true
    this.loadingNode.classList.remove('hidden')
    this.loadingReasonNode.innerText = loading
  }

  _setResult (result) {
    this.resultNode.classList.remove('hidden')

    if (!['valid', 'consumed', 'default'].includes(result)) {
      result = 'invalid'
    }

    this.videoNode.parentNode.classList.toggle('scanner-video--valid', result === 'valid')
    this.videoNode.parentNode.classList.toggle('scanner-video--consumed', result === 'consumed')
    this.videoNode.parentNode.classList.toggle('scanner-video--invalid', result === 'invalid')

    this.resultNode.classList.remove(...allCssClasses)
    this.resultNode.classList.add(...cssMap[result])

    // Stop here if "resetting"
    if (result === 'default') {
      this.lastScannedBarcode = null
      return
    }

    this.resultNode.querySelectorAll('[data-result]').forEach(node => {
      node.classList.toggle('hidden', node.dataset.result !== result)
      node.classList.toggle('flex', node.dataset.result === result)
    })

    if (this.resultTimeout) {
      clearTimeout(this.resultTimeout)
    }

    this.resultTimeout = setTimeout(() => this._setResult('default'), 5000)
  }

  _foundBarcode (result) {
    if (!result || result.data === this.lastScannedBarcode) {
      return
    }

    this.lastScannedBarcode = result.data
    this.barcodeNode.innerText = result.data
    this.barcodeNode.classList.remove('hidden')

    this._handleBarcode(result.data)
  }

  _handleBarcode (barcode) {
    // Noop if loading
    if (this.loading || !this.salt) {
      return
    }

    // Determine proper hash
    const barcodeHash = SHA256(`${this.salt}${barcode}`.toUpperCase()).toString().substring(0, 12)

    // Check against local preload list
    if (!this.barcodes.includes(barcodeHash)) {
      this._setResult('invalid')
      return
    }

    // Check online
    this._setLoading('Barcode controleren...')
    this._consume(barcode)
  }

  _startCamera () {
    if (this.camera) {
      this.camera.start()
    }
  }

  _stopCamera () {
    if (this.camera) {
      this.camera.pause()
    }
  }

  _handleVisibilityChange (visibilityChangeEvent) {
    if (document.visibilityState === 'hidden') {
      this._stopCamera()
    } else {
      this._startCamera()
    }
  }
}

export default () => {
  const scannerContainer = document.querySelector('[data-content=scanner]')
  if (scannerContainer) {
    scannerContainer.scannerClass = new Scanner(scannerContainer)
  }
}
