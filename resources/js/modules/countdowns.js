const mappedCountdowns = new Map()

const timeInMillis = {
  hour: (60 * 60 * 1000),
  minute: (60 * 1000),
  second: 1000,
}

/**
 * Formats a human-readable diff, based from a time
 * @param {Date} date
 * @returns {String}
 */
const formatDiff = (date) => {
  let diffInMillis = Math.max(0, Date.now() - date.getTime())

  const diffHours = Math.max(0, diffInMillis / timeInMillis.hour)
  diffInMillis %= timeInMillis.hour

  const diffMinutes = Math.max(0, diffInMillis / timeInMillis.minute)
  diffInMillis %= timeInMillis.minute

  const diffSeconds = Math.max(0, diffInMillis / timeInMillis.second)
  diffInMillis %= timeInMillis.second

  return [
    diffHours < 10 ? `0${diffHours}` : diffHours,
    diffMinutes < 10 ? `0${diffMinutes}` : diffMinutes,
    diffSeconds < 10 ? `0${diffSeconds}` : diffSeconds,
  ].join(':')
}

const updateNodes = () => {
  // Iterate over dates and nodes in mappedCountdowns
  mappedCountdowns.forEach((date, node) => {
    node.innerText = formatDiff(date)

    // Remove from the list if done
    if (node.innerText !== '00:00:00') {
      return
    }

    // Remove node from mappedCountdowns
    mappedCountdowns.delete(node)

    // Check if the node has a completion action
    const completeClass = node.dataset.completeClass || null
    if (completeClass) {
      node.classList.add(completeClass.split(' '))
    }
  })
}

const init = () => {
  document.querySelectorAll('[data-countdown]').forEach(node => {
    mappedCountdowns.set(node, new Date(Date.parse(node.dataset.countdown)))
  })

  setTimeout(() => {
    updateNodes()
    setInterval(updateNodes, 1000)
  }, 1000 - (new Date().getMilliseconds() % 1000))

  updateNodes()
}

export default init
