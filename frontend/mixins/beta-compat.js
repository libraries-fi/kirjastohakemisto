import { demo } from '@/config.json'

export function useBetaKirkantaDomain(url) {
  if (!demo.enabled) {
    return url
  }

  if (url) {
    console.log(url.replace(/\bhttp(s?):\/\/kirkanta\.kirjastot\.fi\b/, demo.assetUrl))
    return url.replace(/\bhttp(s?):\/\/kirkanta\.kirjastot\.fi\b/, demo.assetUrl)
  }
}
