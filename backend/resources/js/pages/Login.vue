<template>
  <div class="container">
    <ul class="tab">
      <li
        class="tab__item"
        :class="{ 'tab__item--active': tab === 1 }"
        @click="tab = 1"
      >
        Login
      </li>
      <li
        class="tab__item"
        :class="{ 'tab__item--active': tab === 2 }"
        @click="tab = 2"
      >
        Register
      </li>
    </ul>

    <div class="panel" v-show="tab === 1">

      <form @submit.prevent="login">

        <div class="form-group">
          <label for="login-email">E-mail</label>
          <div v-if="loginErrorMessages" class="errors">
            <ul v-if="loginErrorMessages.email">
              <li
               v-for="msg in loginErrorMessages.email"
               :key="msg"
              >
                {{ msg }}
              </li>
            </ul>
            <ul v-if="loginErrorMessages.password">
              <li
               v-for="msg in loginErrorMessages.password"
               :key="msg"
              >
                {{ msg }}
              </li>
            </ul>
          </div>
          <input
            type="email"
            class="form-control"
            id="login-email"
            v-model="loginForm.email"
          >
        </div>

        <div class="form-group">
          <label for="login-password">Password</label>
          <input
            type="password"
            class="form-control"
            id="login-password"
            v-model="loginForm.password"
          >
        </div>

        <div style="text-align: right;">
          <button
            type="submit"
            class="btn btn-outline-secondary"
          >
            login
          </button>
        </div>

      </form>

    </div>
    <div class="panel" v-show="tab === 2">

      <form @submit.prevent="register">

        <div class="form-group">
          <label for="username">Name</label>
          <div v-if="registerErrorMessages" class="errors">
            <ul v-if="registerErrorMessages.name">
              <li
               v-for="msg in registerErrorMessages.name"
               :key="msg"
              >
                {{ msg }}
              </li>
            </ul>
            <ul v-if="registerErrorMessages.email">
              <li
               v-for="msg in registerErrorMessages.email"
               :key="msg"
              >
                {{ msg }}
              </li>
            </ul>
            <ul v-if="registerErrorMessages.password">
              <li
               v-for="msg in registerErrorMessages.password"
               :key="msg"
              >
                {{ msg }}
              </li>
            </ul>
          </div>
          <input
            type="text"
            class="form-control"
            id="username"
            v-model="registerForm.name"
          >
        </div>

        <div class="form-group">
          <label for="register-email">E-mail</label>
          <input
            type="email"
            class="form-control"
            id="email"
            v-model="registerForm.email"
          >
        </div>

        <div class="form-group">
          <label for="register-password">Password</label>
          <input
            type="password"
            class="form-control"
            id="password"
            v-model="registerForm.password"
          >
        </div>

        <div class="form-group">
          <label for="password_confirmation">Password (confirm)</label>
          <input
            type="password"
            class="form-control"
            id="password-confirmation"
            v-model="registerForm.password_confirmation"
          >
        </div>

        <div style="text-align: right;">
          <button
            type="submit"
            class="btn btn-outline-secondary"
          >
            Register
          </button>
        </div>

      </form>

    </div>
  </div>
</template>

<style scoped>
.tab {
  display: flex;
  list-style-type: none;
  margin: 0;
  padding: 0;
}
.tab__item {
  border-bottom: 2px solid #dedede;
  color: #8a8a8a;
  cursor: pointer;
  margin: 0 1rem 0 0;
  padding: 1rem;
}
.tab__item--active {
  border-bottom: 2px solid #222;
  color: #222;
  font-weight: bold;
}
.panel {
  border: 1px solid #dedede;
  margin-top: 1rem;
  padding: 1.5rem;
}
.errors {
  border: 1px solid #c7004c;
  border-radius: 0.25rem;
  color: #c7004c;
  margin-bottom: 1rem;
}
</style>

<script>
export default {
  data() {
    return {
      tab: 1,
      loginForm: {
        email: '',
        password: '',
      },
      registerForm: {
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
      }
    }
  },

  methods: {

    async login () {
      await this.$store.dispatch('auth/login', this.loginForm)

      if(this.apiStatus) {
        this.$router.push('/')
      }

    },

    async register () {
      await this.$store.dispatch('auth/register', this.registerForm)

      if(this.apiStatus) {
        this.$router.push('/')
      }

    },

    clearErrorMessages () {
      this.$store.commit('auth/setRegisterErrorMessages', null)
      this.$store.commit('auth/setLoginErrorMessages', null)
    },

  },

  computed: {

    apiStatus () {
      return this.$store.state.auth.apiStatus
    },

    registerErrorMessages () {
      return this.$store.state.auth.registerErrorMessages
    },

    loginErrorMessages () {
      return this.$store.state.auth.loginErrorMessages
    },

  },

  created () {
    this.clearErrorMessages ()
  }
}
</script>
