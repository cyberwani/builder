let errors = []

let logError = (message, details) => {
  let error = new Error()
  errors.push({
    message: message,
    details: details,
    stack: error && error.stack ? error.stack : []
  })
}

let getErrors = () => {
  return errors
}

let sendErrors = (e, cb) => {
  e && e.preventDefault && e.preventDefault()
  window.jQuery.ajax(
    {
      type: 'POST',
      url: window.vcvErrorReportUrl,
      data: {
        'vcv-nonce': window.vcvNonce,
        errors: JSON.stringify(getErrors())
      }
    }
  ).always(cb)
  // reset list of errors
  errors = []
}

module.exports = { log: logError, all: getErrors, send: sendErrors }