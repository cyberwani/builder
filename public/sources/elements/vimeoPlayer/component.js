/* global React, vcvAPI */
class Component extends vcvAPI.elementComponent {
  validUnits (src) {
    switch (src) {
      case 'px':
        return src
      case 'em':
        return src
      case 'rem':
        return src
      case '%':
        return src
      case 'vw':
        return src
      case 'vh':
        return src
      default:
        return 'px'
    }
  }

  render () {
    let { id, atts, editor } = this.props
    let { customClass, videoPlayer, alignment, size, customSize, advanced } = atts
    let classes = 'vce-vim-video-player vce'
    let innerClasses = 'vce-vim-video-player-inner'
    let source, videoWidth, videoId
    let autopause = advanced && atts.autopause ? 1 : 0
    let autoplay = advanced && atts.autoplay ? 1 : 0
    let loop = advanced && atts.loop ? 1 : 0
    let color = advanced ? atts.color.slice(1) : '00adef'
    let vrx = /https?:\/\/(?:www\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|)(\d+)(?:$|\/|\?)/

    if (typeof customClass === 'string' && customClass) {
      classes = classes.concat(` ${customClass}`)
    }
    if (alignment) {
      classes += ` vce-vim-video-player--align-${alignment}`
    }
    if (size) {
      classes += ` vce-vim-video-player--size-${size}`
      if (size === 'custom') {
        if (/^[0-9.]+$/.test(customSize)) {
          videoWidth = `${parseFloat(customSize)}px`
        } else if (parseFloat(customSize)) {
          let chars = customSize.slice(parseFloat(customSize).toString().length)
          videoWidth = `${parseFloat(customSize)}${this.validUnits(chars)}`
        } else {
          videoWidth = '560px'
        }
      } else {
        videoWidth = `${size.slice(0, size.indexOf('x'))}px`
      }
    }
    if (videoPlayer.match(vrx)) {
      let url = videoPlayer.match(vrx)
      videoId = url[3]
    }

    source = `//player.vimeo.com/video/${videoId}?autopause=${autopause}&autoplay=${autoplay}&color=${color}&loop=${loop}`

    return <div className={classes} {...editor} data-vcv-element-disabled='true'>
      <div className={innerClasses} id={'el-' + id} style={{width: videoWidth}}>
        <iframe
          className='vce-vim-video-player-iframe'
          src={source}
          frameBorder='0'
          allowFullScreen='true'
        />
      </div>
    </div>
  }
}
