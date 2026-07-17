<template>
    <footer class="main-footer text-md">
    <strong>{{ $t('footer.copyright') }} &copy; {{new Date().getFullYear()}} <a href="/">Ayub Jewelers</a>.</strong>
    {{ $t('footer.rights') }}
  </footer>
</template>

<script>
export default {
    name: "Footer"
}
</script>
