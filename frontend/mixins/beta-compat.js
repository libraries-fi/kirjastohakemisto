import config from '@/config'

export function useBetaKirkantaDomain (url) {
  if (!config.demo || !config.demo.enabled) {
    return url
  }

  if (url) {
    return url.replace(/\bhttp(s?):\/\/kirkanta\.kirjastot\.fi\b/, config.demo.assetUrl)
  }
}
