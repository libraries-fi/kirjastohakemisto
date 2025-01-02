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
      <p>Jos huomaat sivustolla saavutettavuusongelmia, anna ensin palautetta sivuston ylläpitäjälle. Vastauksessa voi mennä 14 päivää.
      Jos et ole tyytyväinen saamaasi vastaukseen tai et saa vastausta lainkaan kahden viikon aikana, voit antaa palautteen Liikenne- ja viestintäviraston
      digitaalisen esteettömyyden ja saavutettavuuden valvontaan. Liikenne- ja viestintäviraston digitaalisen esteettömyyden ja saavutettavuuden valvonnan
      saavutettavuusvaatimukset.fi-sivustolla kerrotaan, miten valituksen voi tehdä ja miten asia käsitellään.</p>
      <h3>Valvontaviranomaisen yhteystiedot:</h3>
      <p>Liikenne- ja viestintävirasto Traficom<br>
        Digitaalisen esteettömyyden ja saavutettavuuden valvontayksikkö<br>
        www.saavutettavuusvaatimukset.fi<br>
        saavutettavuus@traficom.fi<br>
        puhelinnumero vaihde 029 534 5000
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
      <p>Om du noterar tillgänglighetsproblem på webbplatsen, lämna först respons till webbplatsens administratör. Svaret kan dröja 14 dagar.
        Om du inte är nöjd med svaret eller om du inte har fått något svar efter två veckor, kan du lämna respons till Transport- och kommunikationsverket Traficoms enheten
        för tillsyn över digital tillgänglighet. På Transport- och kommunikationsverket Traficoms webbplats tillgänglighetskrav.fi finns information om hur du lämnar in ett
        klagomål och hur ärendet handläggs.
      </p>
      <h3>Tillsynsmyndighetens kontaktuppgifter:</h3>
      <p>Transport- och kommunikationsverket Traficom<br>
        Tillsyn över digital tillgänglighet<br>
        www.tillgänglighetskrav.fi<br>
        webbtillganglighet@traficom.fi<br>
        telefonnummer till växeln 029 534 5000
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
      <p>If you notice accessibility issues on the website, first give feedback to the website administrator. Receiving a response may take 14 days.
        If you are not satisfied with the response or you receive no response within two weeks, you can provide feedback to the Finnish Transport and
        Communications Agency Traficom Digital Accessibility Supervision Unit. Traficom’s accessibility requirements website (saavutettavuusvaatimukset.fi)
        explains how you can lodge a complaint and how the matter will be processed.
      </p>
      <h3>Contact information of the supervisory authority:</h3>
      <p>Finnish Transport and Communications Agency Traficom<br>
        Digital Accessibility Supervision<br>
        www.saavutettavuusvaatimukset.fi<br>
        saavutettavuus@traficom.fi<br>
        Telephone +358 29 534 5000 (exchange)
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
