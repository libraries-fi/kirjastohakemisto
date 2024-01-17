<template>
  <main>
    <h1 class="page-title">{{ $t('open-data.page-title') }}</h1>

    <p>{{ $t('open-data.page-description') }}</p>

    <h2>{{ $t('open-data.api-title') }}</h2>
    <p>{{ $t('open-data.api-description') }}</p>
    <p><a href="https://api.kirjastot.fi" class="external-link">{{ $t('open-data.api-link') }}</a></p>

    <h2>{{ $t('open-data.widgets-title') }}</h2>
    <p>{{ $t('open-data.widgets-description') }}</p>

    <p><a href="/info/widgets">{{ $t('open-data.widgets-link') }}</a></p>
  </main>
</template>

<script>
  export default {}
</script>
