<template>
  <nav class="navbar navbar-expand-md navbar-light bg-white shadow-sm">
      <div class="container">
          <RouterLink class="navbar-brand" to="/">
            Subscription
          </RouterLink>

          <div class="navbar-menu">

            <span v-if="isLogin" class="navbar-item">
              {{ username }}
            </span>

            <RouterLink
             v-if="isLogin"
             class="btn btn-outline-warning"
             to="/paymentMethodForm"
            >
              Payment Method
            </RouterLink>

            <RouterLink v-else class="btn btn-outline-secondary" to="/login">
              Login / Register
            </RouterLink>

            <RouterLink class="btn btn-outline-success" to="/subscribePage">
              Subscribe
            </RouterLink>  

          </div>

      </div>
  </nav>
</template>

<script>
export default {
  computed: {
    apiStatus () {
      return this.$store.state.auth.apiStatus
    },
    isLogin () {
      return this.$store.getters['auth/check']
    },
    username () {
      return this.$store.getters['auth/username']
    },
    hasDefaultPaymentMethod () {
      return this.$store.getters['stripe/hasDefaultPaymentMethod']
    }
  }
}
</script>
