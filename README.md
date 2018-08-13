KIRJASTOHAKEMISTO
=================

This is the main website for [Kirjastohakemisto](https://hakemisto.kirjastot.fi),
the Finnish Library Database. The website is built as a simple frontend that queries our open API at
[api.kirjastot.fi](https://api.kirjastot.fi). The API serves as the database.

## See it in action
- https://hakemisto.kirjastot.fi
- https://directory.libraries.fi
- https://registret.biblioteken.fi

## Dependencies
- PHP 7.1+
- Node.js (for compiling modified assets)

## Deployment
The repo contains pre-compiled assets, so all-in-all this is just a simple PHP web application.
To compile the modified assets, one needs to run *npm install* in the web root to install
the dependencies and *npm run dev* to run the compiler. Assets can be compiled in production mode via
*npm run build*.

## Kirkanta repository family
- [Kirjastohakemisto](https://github.com/libraries-fi/kirjastohakemisto) -- frontend
- [Kirkanta](https://github.com/libraries-fi/kirkanta) -- backend
- [Kirkanta API](https://github.com/libraries-fi/kirkanta-api) -- REST API
- [Kirkanta Widgets](https://github.com/libraries-fi/kirkanta-embed) -- Build embeddable widgets
