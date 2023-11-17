<template>
  <div id="Page404">
    <h1 class="page-title">{{ $t('404.page-title') }}</h1>

    <p>{{ $t('404.page-text') }}</p>

    <p>{{ $t('404.libraries-tip') }} <router-link :to="{ name: 'library.collection' }">{{ $t('nav.libraries') }}</router-link>{{ $t('404.from-page') }} 
       {{ $t('404.libraries-continued-tip') }}<br />
       {{ $t('404.consortium-tip') }} <router-link :to="{ name: 'consortium.collection' }">{{ $t('nav.consortiums') }}</router-link>{{ $t('404.from-page') }}<br />
       {{ $t('404.services-tip') }} <router-link :to="{ name: 'service.collection' }">{{ $t('nav.services') }}</router-link>{{ $t('404.from-page') }}<br />
       {{ $t('404.location-tip') }}
    </p>
  </div>
</template>
