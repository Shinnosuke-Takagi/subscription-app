<template>
  <footer class="footer">
    <button
      v-if="isLogin"
      class="btn btn-dark"
      @click="logout"
    >
      Logout
    </button>
    <RouterLink
      v-else
      class="btn btn-outline-secondary"
      to="/login"
    >
      Login / Register
    </RouterLink>
  </footer>
</template>

<style scoped>
.footer {
  align-items: center;
  display: flex;
  justify-content: center;
}
</style>

<script>
export default {
  methods: {
    async logout () {
      await this.$store.dispatch('auth/logout')

      if(this.apiStatus) {
        this.$router.push('/login')
      }
    }
  },
  computed: {
    apiStatus () {
      return this.$store.state.auth.apiStatus
    },
    isLogin () {
      return this.$store.getters['auth/check']
    },
  }
}
</script>
