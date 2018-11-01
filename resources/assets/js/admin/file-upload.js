/**
 * File upload
 *
 * @author Roelof Roos <github@roelof.io>
 * @license MPL-2.0
 */

import $ from 'jquery'

import { FineUploaderBasic } from 'fine-uploader/lib/core'
import { getCsrfToken } from '../utils'

const DEFAULT_OPTIONS = {
  multiple: true,
  disableCancelForFormUploads: true,

  // Request settings
  request: {
    inputName: 'file',
    omitDefaultParams: true,
    customHeaders: {
      'X-CSRF-TOKEN': getCsrfToken()
    }
  },
  validation: {
    allowedExtensions: ['pdf'],
    acceptFiles: 'application/pdf'
  },
  deleteFile: {
    enabled: false
  },
  retry: {
    enableAuto: true,
    maxAutoAttempts: 3,
    autoAttemptDelay: 15
  },
  messages: {
    typeError: "{file} heeft een ongeldige extensie. Geldige bestandsextensie(s): {extensions}.",
    sizeError: "{file} is te groot, de maximale bestandsgrootte is {sizeLimit}.",
    minSizeError: "{file} is te klein, de minimale bestandsgrootte is {minSizeLimit}.",
    emptyError: "{file} is leeg, selecteer opnieuw bestanden.",
    noFilesError: "Geen bestanden om te uploaden.",
    tooManyItemsError: "Er staan te veel ({netItems}) items in de wachtrij. Het limiet is {itemLimit}.",
    maxHeightImageError: "Afbeelding is te hoog.",
    maxWidthImageError: "Afbeelding is te breed.",
    minHeightImageError: "Afbeelding niet hoog genoeg.",
    minWidthImageError: "Afbeelding is niet breed genoeg.",
    retryFailTooManyItems: "Opnieuw proberen is mislukt — Je bestandslimiet is bereikt.",
    onLeave: "Er worden nog bestanden geüpload. Als je van deze pagina weggaat, worden deze uploads afgebroken.",
    unsupportedBrowserIos8Safari: "Onherstelbare fout - je browser ondersteund uploaden niet in iOS8 Safari. Maak gebruik van iOS8 Chrome."
  },
}

const createUploader = node => {
  if (!('uploadUrl' in node.dataset)) {
    return
  }

  // Find elements
  const dropZone = node.querySelector('[data-upload="dropzone"]')
  const queue = node.querySelector('[data-upload="queue"]')
  const progress = node.querySelector('[data-upload="progress"]')
  const template = node.querySelector('[data-upload="template"]')

  // Get confguration
  let options = {...DEFAULT_OPTIONS}
  options.request.endpoint = node.dataset.uploadUrl

  const uploader = FineUploaderBasic()
}


// Export file uploader
export default function {
  document.querySelectorAll('[data-content=upload-modal]').forEach(createUploader)
}
