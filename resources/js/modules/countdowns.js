import moment from 'moment'

const mappedCountdowns = new Map()

const formatDiff = (date) => {
  const diffHours = date.diff(moment(), 'hours')
  const diffMinutes = date.diff(moment(), 'minutes') - diffHours * 60
  const diffSeconds = date.diff(moment(), 'seconds') - diffMinutes * 60

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
  })
}

const init = () => {
  console.log('Init!')
  document.querySelectorAll('[data-countdown]').forEach(node => {
    console.log('Mapped %o to date %s → %o', node, node.dataset.countdown)

    mappedCountdowns.set(node, moment(node.dataset.countdown))
  })

  setTimeout(() => {
    updateNodes()
    setInterval(updateNodes, 1000)
  }, 1000 - (new Date().getMilliseconds() % 1000))

  updateNodes()
}

export default init
