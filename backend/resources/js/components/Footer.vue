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
  width: 100%;
  align-items: center;
  display: flex;
  justify-content: center;
  position: absolute;
  bottom: 0px;
}
.btn {
  border-radius: 0px;
  width: 100%;
  padding-top: 10px;
  padding-bottom: 10px;
  font-size: 1.2rem;
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
