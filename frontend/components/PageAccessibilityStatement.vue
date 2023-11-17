<template>
  <main>
    <h1 class="page-title">{{ $t('accessibility-statement.page-title') }}</h1>

    <div v-if="isFinnish()">
      <p>Tervetuloa, tämä on Helsingin kaupunginkirjaston Valtakunnalliset kehittämispalvelut -yksikön saavutettavuusseloste. Tätä sivustoa koskee 
        <a href="https://www.finlex.fi/fi/laki/alkup/2019/20190306">laki digitaalisten palvelujen tarjoamisesta</a>, jossa edellytetään, 
        että julkisten verkkopalvelujen on oltava saavutettavia.
      </p>
      <p>Tämä saavutettavuusseloste koskee sivustoa hakemisto.kirjastot.fi ja on laadittu 30.12.2021. Sivuston saavutettavuuden arviointi perustuu itsearvioon.</p>
      <h2>Digipalvelun saavutettavuuden tila</h2>
      <p>Täyttää kriittiset A &amp; AA-vaatimukset.</p>
      <h2>Huomasitko saavutettavuuspuutteen digipalvelussamme? Kerro se meille ja teemme parhaamme puutteen korjaamiseksi</h2>
      <p>Saavutettavuuspalautetta voi jättää Kirjastot.fi:n yhteisellä palautelomakkeella, joka sijaitsee tämän sivuston ulkopuolella.<br/>
        <strong><a href="https://www.kirjastot.fi/feedback/kirjastohakemisto?accessibility">Anna saavutettavuuspalautetta tällä verkkolomakkeella</a></strong>.
      </p>
      <p>Voit myös lähettää palautteesi sähköpostitse osoitteeseen <a href="mailto:toimitus@kirjastot.fi">toimitus@kirjastot.fi</a>.</p>
      <h2>Valvontaviranomainen</h2>
      <p>Jos huomaat sivustolla saavutettavuusongelmia, anna ensin palautetta meille eli sivuston ylläpitäjälle. Vastauksessa voi mennä 14 päivää.
        Jos et ole tyytyväinen saamaasi vastaukseen tai et saa vastausta lainkaan kahden viikon aikana,
        <a href="https://www.saavutettavuusvaatimukset.fi/oikeutesi/ilmoita-ongelmasta-saavutettavuudessa/">voit tehdä ilmoituksen</a> Etelä-Suomen aluehallintovirastoon.
        Etelä-Suomen aluehallintoviraston sivulla kerrotaan tarkasti, miten ilmoituksen voi tehdä ja miten asia käsitellään.
      </p>
      <h3>Valvontaviranomaisen yhteystiedot:</h3>
      <p>Etelä-Suomen aluehallintovirasto</br>
        Saavutettavuuden valvonnan yksikkö</br>
        www.saavutettavuusvaatimukset.fi</br>
        saavutettavuus(at)avi.fi</br>
        puhelinnumero: 0295 016 000 (vaihde)
      </p>
    </div>

    <div v-if="isSwedish()">
      <p>Välkommen, det här är tillgänglighetsutlåtandet för enheten för nationella utvecklingstjänster vid Helsingfors stadsbibliotek. Den här webbplatsen omfattas av
        <a href="https://www.finlex.fi/sv/laki/alkup/2019/20190306">lagen om tillhandahållande av digitala tjänster</a>
        och dess krav på att offentliga webbplatser måste vara tillgängliga.
      </p>
      <p>Det här tillgänglighetsutlåtandet gäller webbplatsen registret.biblioteken.fi och har upprättats 30.12.2021.</p>
      <h2>Tillgänglighetsstatus för den digitala tjänsten</h2>
      <p>Uppfyller de kritiska A & AA-kraven.</p>
      <h2>Hittade du brister i tillgängligheten i vår digitala tjänst? Berätta det för oss, så gör vi vårt bästa för att reparera bristerna</h2>
      <p>Du kan skicka respons om tillgängligheten via Biblioteken.fi:s gemensamma responsformulär, som finns utanför den här webbplatsen: 
        ge din respons om tillgängligheten via det här webbformuläret:<br/>
        <strong><a href="https://www.biblioteken.fi/feedback/kirjasampo?accessibility">ge din respons om tillgängligheten via det här webbformuläret.</a></strong>.
      </p>
      <p>Du kan också skicka respons till e-postadressen <a href="mailto:redaktionen@biblioteken.fi">redaktionen@biblioteken.fi</a>.</p>
      <h2>Tillsynsmyndigheten</h2>
      <p>Kontakta först oss, webbplatsens upprätthållare om du vill påtala brister i tillgängligheten på vår webbplats. 
        Det kan ta 14 dagar innan du får ett svar. Om du inte är nöjd med det svar du har fått eller inte får något svar alls inom två veckor
        <a href="https://www.xn--tillgnglighetskrav-ptb.fi/dina-rattigheter/anmal-bristande-tillganglighet/">kan du lämna in en anmälan om bristande tillgänglighet </a>
        till Regionförvaltningsverket i Södra Finland. På regionförvaltningsverkets webbplats finns det beskrivet hur du kan lämna in en anmälan och hur ärendet behandlas.
      </p>
      <h3>Tillsynsmyndighetens kontaktuppgifter:</h3>
      <p>Regionförvaltningsverket i Södra Finland</br>
        Enheten för tillgänglighetstillsyn</br>
        www.tillgänglighetskrav.fi</br>
        webbtillganglighet@rfv.fi</br>
        telefonnummer växeln 0295 016 000
      </p>
    </div>

    <div v-if="isEnglish()">
      <p>Welcome to the accessibility statement of the National Development Services of Helsinki City Library. 
        This website is subject to the Digital Services Act, which requires that all public sector online services in Finland are accessible.
      </p>
      <p>
        This accessibility statement applies to the website directory.libraries.fi as of 30 December 2021.
      </p>
      <h2>Accessibility status of the digital service</h2>
      <p>Meets the critical A & AA requirements.</p>
      <h2>Did you notice any accessibility deficiency in our digital service? Please let us know and we will do our best to rectify it</h2>
      <p>
        <strong><a href="https://www.libraries.fi/feedback/?accessibility">Please use this online form for accessibility feedback</a></strong>.
      </p>
      <p>You can also send your feedback by e-mail to <a href="mailto:toimitus@kirjastot.fi">toimitus@kirjastot.fi</a>.</p>
      <h2>Supervisory authority</h2>
      <p>If you notice any accessibility issues on the website, please leave your feedback first with us, the administrators of the website. 
        The answer can take up to 14 days. If you are not satisfied with the answer you have received, or do not receive a reply at all within two weeks, 
        you may file a complaint with the Regional State Administrative Agency for Southern Finland. The agency website provides detailed information 
        (in Finnish and Swedish only) on how to file a complaint and how the issue will be handled.
      </p>
      <h3>Contact information of the supervisory authority:</h3>
      <p>Southern Finland Regional State Administrative Agency</br>
        Accessibility Control Unit</br>
        www.saavutettavuusvaatimukset.fi</br>
        saavutettavuus(at)avi.fi</br>
        Telephone (switchboard): +358 295 016 000
      </p>
    </div>

  </main>
</template>

<script>
import { detectLanguage } from '@/mixins'

const langcode = detectLanguage()

export default {
  methods: {
    isFinnish () {
      if (langcode === 'fi') {
        return true
      } 
      return false
    },
    isEnglish () {
      if (langcode === 'en') {
        return true
      } 
      return false
    },
    isSwedish () {
      if (langcode === 'sv') {
        return true
      } 
      return false
    }
  }
}
</script>
