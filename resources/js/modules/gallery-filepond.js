import { create, registerPlugin } from 'filepond'

// Plugins
import FileValidateSizePlugin from 'filepond-plugin-file-validate-size'
import FileValidateTypePlugin from 'filepond-plugin-file-validate-type'

export default function () {
  /** @var {HTMLDivElement} dropField */
  const dropField = document.querySelector('[data-content=filepond][data-scope=gallery]')

  if (!dropField) {
    console.info('[GumboGallery] No drop field found.')
    return
  }

  const dropFieldForm = dropField.closest('form')
  if (!dropFieldForm) {
    console.info('[GumboGallery] Drop field is not in a form.')
    return
  }

  const processUrl = dropField.dataset.processUrl
  const revertUrl = dropField.dataset.revertUrl

  if (!processUrl || !revertUrl) {
    console.warn('[GumboGallery] Missing processUrl or revertUrl.')
    return
  }

  const csrfToken = dropField.dataset.csrf
  if (!csrfToken) {
    console.warn('[GumboGallery] Missing CSRF token.')
    return
  }

  const maxFileSize = dropField.dataset.maxFilesize || (1024 * 1024 * 3)

  const requestHeaders = {
    'X-CSRF-Token': csrfToken,
    Accept: 'application/json',
  }

  let existingFiles = []
  const existingFilesContainer = dropField.querySelector('script[data-content="pending-uploads"]')
  if (existingFilesContainer) {
    try {
      existingFiles = JSON.parse(existingFilesContainer.textContent)
    } catch (jsonException) {
      console.error('[GumboGallery] Error parsing existing files:', jsonException)
    }
  }

  registerPlugin(FileValidateSizePlugin)
  registerPlugin(FileValidateTypePlugin)

  const pond = create(dropField, {
    // Upload settings
    name: 'file',
    server: {
      process: {
        url: processUrl,
        headers: requestHeaders,
      },
      revert: {
        url: revertUrl,
        headers: {
          ...requestHeaders,
          'Content-Type': 'application/json',
        },
      },
      fetch: null,
      restore: null,
    },

    // Re-supply pre-uploaded images
    files: existingFiles,

    // Settings
    dropOnPage: true,
    allowMultiple: true,
    forceRevert: true,
    itemInsertLocation: 'after',

    // File size valiation
    maxFileSize: maxFileSize,

    // File type validation
    acceptedFileTypes: [
      'image/jpeg',
    ],

    // Image preview
    allowImagePreview: true,
    imagePreviewMaxWidth: '100%',

    // Localisation
    labelIdle: 'Sleep je bestanden hier, of <span class="filepond--label-action"> blader </span>',
    labelInvalidField: 'Het veld bevat ongeldige bestanden',
    labelFileWaitingForSize: 'Wachten op bestandsgrootte...',
    labelFileSizeNotAvailable: 'Bestandsgrootte niet beschikbaar',
    labelFileLoading: 'Laden',
    labelFileLoadError: 'Fout tijdens laden',
    labelFileProcessing: 'Uploaden',
    labelFileProcessingComplete: 'Upload voltooid',
    labelFileProcessingAborted: 'Upload geannuleerd',
    labelFileProcessingError: 'Fout tijdens uploaden',
    labelFileProcessingRevertError: 'Fout tijdens ongedaan maken',
    labelFileRemoveError: 'Fout tijdens verwijderen',
    labelTapToCancel: 'tap om te annuleren',
    labelTapToRetry: 'tap om opnieuw te proberen',
    labelTapToUndo: 'tap om ongedaan te maken',
    labelButtonRemoveItem: 'Verwijderen',
    labelButtonAbortItemLoad: 'Annuleren',
    labelButtonRetryItemLoad: 'Opnieuw proberen',
    labelButtonAbortItemProcessing: 'Annuleren',
    labelButtonUndoItemProcessing: 'Ongedaan maken',
    labelButtonRetryItemProcessing: 'Opnieuw proberen',
    labelButtonProcessItem: 'Upload',
  })

  // Add a helper to rename the fields named "file" to "file[x]"
  let selfTriggered = false
  dropFieldForm.addEventListener('submit', event => {
    if (!dropFieldForm.checkValidity()) {
      return
    }

    if (selfTriggered) {
      selfTriggered = false
      return
    }

    event.preventDefault()

    let fileIteration = 0
    dropFieldForm.querySelectorAll('input[name^="file"]').forEach(input => {
      input.setAttribute('name', `file[${fileIteration++}]`)
    })

    selfTriggered = true
    dropFieldForm.submit()
  })

  dropField.dataset.filepond = pond.id
};
