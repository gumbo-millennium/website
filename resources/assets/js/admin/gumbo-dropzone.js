/**
 * Dropzone helpers
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

// Load jQuery and Dropzone
import Dropzone from 'dropzone'

const queries = {
  uploadButton: '[data-upload-action=upload]',
  uploadZone: '[data-upload-content=dropzone]',
  progress: '[data-upload-content=progress]',
  modal: '[data-upload-content=modal]',
  queue: '[data-upload-content=queue]',
  template: '[data-upload-content=template]',
  fileRow: '[data-file-content=row]',
  fileName: '[data-file-content=name]',
  fileSize: '[data-file-content=size]',
  fileSlug: '[data-file-content=slug]',
  fileStatus: '[data-file-content=status]',
  fileCancel: '[data-file-action=cancel]',
  fileLink: '[data-file-action=link]',
  modalClose: '[data-upload-action="modal-close"]',
  modalReload: '[data-upload-action="modal-reload"]'
}

/**
 * Finds a node
 * @param {String} query Query string
 * @param {DOMNode} node Scope
 * @returns {NodeList}
 */
const $ = (query, node = document) => {
  if (node === null) {
    return new NodeList()
  }

  if (!node.querySelectorAll) {
    console.warn('Cannot find querySelectorAll on %o.', node)
  }

  try {
    return node.querySelectorAll(query)
  } catch (error) {
    console.warn('Failed to run querySelectorAll on %o.', node)
    return new NodeList()
  }
}

/**
 * Default config for Dropzone
 */
const defaultConfig = {
  acceptedFiles: 'application/pdf',
  headers: [],
  url: '/'
}

/**
 * Validates a CSRF token, prevents injections
 * @param {String} csrf
 * @returns {Boolean}
 */
const validateCsrf = csrf => {
  return typeof csrf === 'string' && csrf.match(/^[a-z0-9]{5,}$/i)
}

/**
 * Finds a decent error message, or reports error code
 *
 * @param {Object} response
 * @param {XMLHttpRequest} xhr
 */
const getErrorMessage = (response, xhr) => {
  // Check if the response is a string
  if (typeof response === 'string') {
    return response
  }

  // Handle response if it's an object
  if (typeof response === 'object') {
    // Look for Laravel error on the file
    if (response.errors &&
        response.errors.file &&
        response.errors.file[0] &&
        typeof response.errors.file[0] === 'string'
    ) {
      return response.errors.file[0]
    }

    // Look for generic error message
    if (response.error && response.error.message && typeof response.error.message === 'string') {
      return response.error.message
    }

    // Look for an error string
    if (response.error && typeof response.error === 'string') {
      return response.error
    }

    // Look for a message
    if (response.message && typeof response.message === 'string') {
      return response.message
    }
  }

  // Derrive response from HTTP code
  const xhrStatus = xhr.status
  const xhrStatusLine = xhr.statusText || null

  // If the error is HTTP 419, we have a Laravel error on our hands
  if (xhrStatus === 419) {
    return 'Request validation error'
  }

  // Otherwise get the error from the statustext system
  return xhrStatusLine || `HTTP error (HTTP ${xhrStatus})`
}

/**
 * Allow only one dropzone to exist
 */
let created = false

/**
 * Handles advanced action on a dropzone, which is contained in a Bootstrap modal (well, usually)
 */
class GumboDropzone {
  /**
   * Finds a template
   *
   * @param {DOMNode} node Node to find template in
   * @returns {String|null}
   */
  static getTemplate (node) {
    let template = null
    for (let templateNode of $(queries.template, node)) {
      template = templateNode.innerHTML
      templateNode.classList.add('d-none')
      console.debug('Received template %s from %o.', template, templateNode)
      break
    }

    return template
  }

  /**
   * Finds click/droppable nodes
   * @param {DOMNode} node
   * @returns {NodeList|null}
   */
  static getClickables (node) {
    const innerClickable = $(queries.uploadZone, node)
    const outerClickable = $(queries.uploadButton)

    const clickables = [].concat(
      Array.from(innerClickable),
      Array.from(outerClickable)
    )
    let res = clickables.length > 0 ? Array.from(clickables) : null
    console.debug('Found clickables %o.', res)
    return res
  }

  /**
   * Finds queue
   * @param {DOMNode} node
   * @returns {DOMNode|null}
   */
  static getQueue (node) {
    const res = $(queries.queue, node)
    return res.length > 0 ? res[0] : null
  }

  /**
   * Creates dropzone for the given node
   *
   * @param {DOMnode} node
   */
  constructor (node) {
    if (created) {
      console.warn('Attemptd to create secondary dropzone at %o', node)
      return
    }

    // Create intial
    this.totalBytes = 0
    this.replacedButtons = false

    // Log
    console.debug('Creating Gumbo zone on %o.', node)

    // Register the node
    this.node = node

    // Get URL
    this.url = this.node.dataset.uploadUrl
    if (!this.url) {
      console.warn('Cannot find URL on %o', this.node)
      return
    }

    // Create config from baseConfig with current URL
    let config = {
      ...defaultConfig,
      url: this.url
    }

    // if there's a CSRF token and it's valid, register it
    if (this.node.dataset.uploadCsrf && validateCsrf(this.node.dataset.uploadCsrf)) {
      config.headers['X-CSRF-TOKEN'] = `${this.node.dataset.uploadCsrf}`
    }

    // Find the queue container and progress bar(s)
    this.queue = GumboDropzone.getQueue(this.node)
    this.progressBars = $(queries.progress, this.node)

    // Generate some fields
    let createdConfig = [
      ['clickable', GumboDropzone.getClickables(this.node)],
      ['previewsContainer', this.queue],
      ['previewTemplate', GumboDropzone.getTemplate(this.node)]
    ]

    // Add the generated fields, if they're not null
    createdConfig
      .filter(([, value]) => value !== null)
      .forEach(([name, value]) => {
        // Assign generated value to config
        config[name] = value
      })

    // Create dropzone
    this.dropzone = new Dropzone(this.node, config)

    // Register events
    this.dropzone.on('addedfile', this.addedfile.bind(this))
    this.dropzone.on('error', this.error.bind(this))
    this.dropzone.on('sending', this.sending.bind(this))
    this.dropzone.on('uploadprogress', this.uploadprogress.bind(this))
    this.dropzone.on('success', this.success.bind(this))
    this.dropzone.on('queuecomplete', this.queuecomplete.bind(this))
  }

  /**
   * Updates this file's status
   *
   * @param {DOMNode} node File to update status of
   * @param {String} status New status text
   * @param {Boolean} error Is this an error?
   * @returns {void}
   */
  updateStatus (node, status, error = false) {
    for (let statusNode of $(queries.fileStatus, node)) {
      statusNode.classList.remove('d-none')
      statusNode.classList.toggle('text-danger', error === true)
      statusNode.innerText = `${status}`
    }
  }

  /**
   * Updates the progress bars
   *
   * @param {Number} progress Progress, as float between 0-100
   * @param {Boolean} active Show active animation or not
   */
  updateProgressbar (progress, active) {
    for (let bar of this.progressBars) {
      // Update style
      bar.classList.toggle('progress-bar-striped', active === true)
      bar.classList.toggle('progress-bar-animated', active === true)

      // Update value
      bar.setAttribute('aria-valuenow', progress)
      bar.style.width = `${progress}%`
    }
  }

  replaceModalButtons () {
    // Skip if we've already done this
    if (this.replacedButtons) {
      return
    }

    // Mark buttons as replaced
    this.replacedButtons = true

    // Remove primary label off close dialog
    for (let closeButton of $(queries.modalClose, this.node)) {
      closeButton.classList.remove('btn-primary')
    }

    // Show refresh button
    for (let refreshButton of $(queries.modalReload, this.node)) {
      refreshButton.classList.remove('d-none')
      refreshButton.addEventListener('click', event => {
        event.preventDefault()
        document.location.reload(false)
      })
    }
  }

  /**
   * Called when a file is added. Moves the DOMnode to the right spot
   *
   * @param {Dropzone.file} file
   */
  addedfile (file) {
    const fileNode = file.previewElement

    // Show cancel button
    for (let button of $(queries.fileCancel, file.previewElement)) {
      button.classList.remove('d-none')
    }

    // add default status
    this.updateStatus(file.previewElement, 'In wachtrij')

    // Raise total number of bytes
    this.totalBytes += file.upload.total
    this.bytesSent = 0

    // Find the inner table row
    if (!this.queue) {
      return
    }

    // Find first row
    const rows = $(queries.fileRow, fileNode)
    const row = rows.length > 0 ? rows[0] : null

    if (!row) {
      return
    }

    // Move row to table
    this.queue.appendChild(row)

    // RE-assign preview
    file.previewElement = row

    // Delete row
    fileNode.parentNode.removeChild(fileNode)
  }

  /**
   * Flags error
   *
   * @param {Dropzone.File} file File that failed
   * @param {String} error error message
   */
  error (file, error, xhr) {
    this.updateStatus(file.previewElement, getErrorMessage(error, xhr), true)
  }

  /**
   * Marks file as sending
   * @param {Dropzone.file} file File being uploaded
   */
  sending (file) {
    this.updateStatus(file.previewElement, 'Gestart')

    // Mark start time
    file.upload.start = new Date().getTime()

    // Log start
    console.debug('Started upload on %o', file)
  }

  /**
   * Updates the progress
   *
   * @param {Dropzone.file} file File being uploaded
   * @param {int} progress Upload progress, in 0-100
   */
  uploadprogress (file, progress, bytesSent) {
    const deltaTime = (new Date().getTime() - file.upload.start) / 1000
    const progressNumber = Number(progress).toFixed(0)

    // Update sent bytes count using last delta
    let bytesDelta = file.upload.lastSent || 0
    file.upload.lastDelta = bytesSent
    this.bytesSent += Math.max(0, bytesSent - bytesDelta) // Update total byte count

    this.updateProgressbar(this.bytesSent / this.totalBytes * 100, true)

    // Don't calculate ETA before 2 seconds
    if (deltaTime < 2 || progress >= 100) {
      this.updateStatus(file.previewElement, `${progressNumber}%`)
      return
    }

    // Estimate speed
    const bytesPerSecond = bytesSent / deltaTime

    // Estimate time
    const bytesLeft = file.upload.total - file.upload.bytesSent
    let timeLeft = '∞'

    if (bytesPerSecond > 0) {
      timeLeft = (bytesLeft / bytesPerSecond).toFixed(0)
    }

    // Human readable speed and status
    const speed = this.dropzone.filesize(bytesPerSecond) + '/s'
    const status = `${progressNumber}% (${speed}, ~${timeLeft} sec)`.replace(/<\/?[^>]+(>|$)/g, '')

    // Print speed
    this.updateStatus(file.previewElement, status, false)
  }

  /**
   * Adds link to file on completion
   *
   * @param {Dropzone.file} file
   * @param {Object} response
   */
  success (file, response) {
    // Report if URL is missing
    if (!response.file || !response.file.url) {
      this.updateStatus(file.previewElement, 'Failed', true)
      return
    }

    // Flag modal for replacement
    this.replaceModalButtons()

    // Update all link buttons with the link URL
    for (let button of $(queries.fileLink, file.previewElement)) {
      button.setAttribute('href', response.file.url)
      button.classList.remove('d-none')
    }

    // Hide cancel button
    for (let button of $(queries.fileCancel, file.previewElement)) {
      button.classList.add('d-none')
    }

    // Add slug to HTML
    const slug = (new URL(response.file.url)).pathname
    for (let slugNode of $(queries.fileSlug, file.previewElement)) {
      slugNode.innerText = `${slug}`
      slugNode.setAttribute('href', slug)
      slugNode.setAttribute('target', '_blank')
      slugNode.classList.replace('d-none', 'd-block')
    }
  }

  /**
   * Called on cancellation
   *
   * @param {Dropzone.file} file
   */
  canceled (file) {
    // Hide link button
    for (let button of $(queries.fileLink, file.previewElement)) {
      button.classList.add('d-none')
    }

    // Update status
    this.updateStatus(file, 'Geannuleerd')
  }

  /**
   * Completes the progress bar
   */
  queuecomplete () {
    this.updateProgressbar(100, false)
  }
}

export default GumboDropzone
