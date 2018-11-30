const BUILD_MODE = process.env.NODE_ENV;

const config = (() => {
  switch (BUILD_MODE) {
    case 'production':
      return {
        apiUrl: 'https://api.kirjastot.fi',
      }

    default:
      return {
        apiUrl: 'http://api.kirjastot.fi.local',
        demo: {
          enabled: true,
          assetUrl: 'http://kirkanta.kirjastot.fi.local',
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

console.log('CONFIG', config, BUILD_MODE)

export default config
