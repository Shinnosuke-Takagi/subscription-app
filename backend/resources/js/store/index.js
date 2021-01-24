import Vue from 'vue'
import Vuex from 'vuex'

import auth from './auth'
import stripe from './stripe'
import error from './error'

Vue.use(Vuex)

const store = new Vuex.Store({
  modules: {
    auth,
    stripe,
    error,
  }
})

export default store
