const BUILD_MODE = process.env.NODE_ENV

const config = (() => {
  switch (BUILD_MODE) {
    case 'production':
      return {
        apiUrl: 'https://api.kirjastot.fi',
        widgetBuilderUrl: 'https://hakemisto.kirjastot.fi/embed/v1'
      }

    default:
      return {
        apiUrl: 'https://api.kirjastot.fi',
        widgetBuilderUrl: 'https://hakemisto.kirjastot.fi/embed/v1',
        demo: {
          enabled: false,
          assetUrl: 'https://kirkanta.kirjastot.fi',
          position: {
            coords: {
              latitude: 60.225672,
              longitude: 24.849124
            }
          }
        }
      }
  }
})()

export default config
