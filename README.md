KIRJASTOHAKEMISTO
=================

This is the main website for [Kirjastohakemisto](https://hakemisto.kirjastot.fi),
the Finnish Library Database. The website is built as a simple frontend that queries our open API at
[api.kirjastot.fi](https://api.kirjastot.fi). The API serves as the database.

## See it in action
- https://beta-hakemisto.kirjastot.fi
- https://beta-directory.libraries.fi

## Dependencies
- PHP 7.1+
- Node.js (for compiling modified assets)

## Deployment
It is enough to clone this repository and compile the provided assets. Build environment can be
installed by running *npm install* in the project root. After this, the build process can be executed
with *npm run build*.

## Kirkanta repository family
- [Kirjastohakemisto](https://github.com/libraries-fi/kirjastohakemisto) -- frontend
- [Kirkanta](https://github.com/libraries-fi/kirkanta) -- backend
- [Kirkanta API](https://github.com/libraries-fi/kirkanta-api) -- REST API
- [Kirkanta Widgets](https://github.com/libraries-fi/kirkanta-embed) -- Build embeddable widgets

### Credits
Kirjastohakemisto employs data from OpenStreetMaps. Municipal regions were fetched using
[OSM Boundaries Map](https://wambachers-osm.website/boundaries/).
