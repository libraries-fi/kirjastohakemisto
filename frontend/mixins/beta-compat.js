export function useBetaKirkantaDomain(url) {
  if (url) {
    return url.replace(/\bkirkanta\.kirjastot\.fi\b/, 'beta-kirkanta.kirjastot.fi')
  }
}
