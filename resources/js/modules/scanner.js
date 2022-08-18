import { SHA256 } from 'crypto-js'
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
    this.preloadUrl = domNode.dataset.preloadUrl
    this.consumeUrl = domNode.dataset.consumeUrl
    this.csrfToken = domNode.dataset.csrfToken

    // DOMNodes
    this.domNode = domNode
    this.loadingNode = domNode.querySelector('[data-content=loading]')
    this.loadingReasonNode = this.loadingNode.querySelector('[data-content="loading-reason"]')
    this.resultNode = domNode.querySelector('[data-content=result]')
    this.barcodeNode = domNode.querySelector('[data-content=barcode]')
    this.videoNode = domNode.querySelector('video')

    this._setLoading('Toegangstoken ophalen...')
    this._preload()

    this.camera = new QrScanner(this.videoNode, result => this._foundBarcode(result), {
      returnDetailedScanResult: true,
    })
  }

  _preload () {
    this._setLoading('Barcodes ophalen...')

    fetch(this.preloadUrl, {
      headers: {
        Accept: 'application/json',
        'X-XSRF-TOKEN': this.csrfToken,
      },
    })
      .then(response => response.json())
      .then(({ data }) => {
        this._setLoading(false)
        this.salt = data.salt
        this.barcodes = data.barcodes

        this.camera.start()
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

    console.log('Removed %o, added %o', allCssClasses, cssMap[result])

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

    console.log('found barcode', result)

    this.lastScannedBarcode = result.data
    this.barcodeNode.innerText = result.data
    this.barcodeNode.classList.remove('hidden')

    this._handleBarcode(result.data)
  }

  _handleBarcode (barcode) {
    const barcodeHash = SHA256(`${this.salt}${barcode}`.toUpperCase()).toString().substring(0, 12)

    if (!this.barcodes.includes(barcodeHash)) {
      this._setResult('invalid')
      return
    }

    this._setLoading('Barcode controleren...')
    this._consume(barcode)
  }
}

export default () => {
  const scannerContainer = document.querySelector('[data-content=scanner]')
  if (scannerContainer) {
    scannerContainer.scannerClass = new Scanner(scannerContainer)
  }
}
