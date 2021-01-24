import { OK } from '../util'
import { UNPROCESSABLE_ENTITY } from '../util'

const state = {
  user: null,
  apiStatus: null,
  loginErrorMessages: null,
  registerErrorMessages: null,
}

const getters = {
  check: state => !! state.user,
  username: state => state.user ? state.user.name : ''
}

const mutations = {
  setUser (state, user) {
    state.user = user
  },
  setApiStatus (state, status) {
    state.apiStatus = status
  },
  setRegisterErrorMessages (state, messages) {
    state.registerErrorMessages = messages
  },
  setLoginErrorMessages (state, messages) {
    state.loginErrorMessages = messages
  }
}

const actions = {
  async currentUser (context) {
    const response = await axios.get('/api/user')

        if(response.status === OK) {
          context.commit('setApiStatus', true)
          context.commit('setUser', response.data || null)
          return false
        }

        context.commit('setApiStatus', false)
        context.commit('error/setCode', response.status, { root: true })

  },

  async register (context, data) {

    const response = await axios.post('/api/register', data)

    if(response.status === OK) {
        context.commit('setApiStatus', true)
        context.commit('setUser', response.data.user)
        context.commit('stripe/setIntent', response.data.intent, { root: true })
        context.commit('stripe/setPaymentMethods', null, { root: true })
        context.commit('stripe/setDefaultPaymentMethod', null, { root: true })

        return false
    }

    context.commit('setApiStatus', false)

    if(response.status === UNPROCESSABLE_ENTITY) {
        context.commit('setRegisterErrorMessages', response.data.errors)
    } else {
        context.commit('error/setCode', response.status, { root: true })
    }

  },

  async login (context, data) {

    const response = await axios.post('/api/login', data)

    if(response.status === OK) {
        context.commit('setApiStatus', true)
        context.commit('setUser', response.data.user)
        context.commit('stripe/setIntent',
          response.data.intent, { root: true }
        )
        context.commit('stripe/setPaymentMethods',
          response.data.payment_methods || null, { root: true }
        )
        context.commit('stripe/setDefaultPaymentMethod',
          response.data.default_payment_method || null, { root: true }
        )
        return false
    }

    context.commit('setApiStatus', false)

    if(response.status === UNPROCESSABLE_ENTITY) {
        context.commit('setLoginErrorMessages', response.data.errors)
    } else {
        context.commit('error/setCode', response.status, { root: true })
    }

  },

  async logout (context) {
    const response = await axios.post('/api/logout')

    if(response.status === OK) {
        context.commit('setApiStatus', true)
        context.commit('setUser', null)
        context.commit('stripe/setIntent', null, { root: true })
        context.commit('stripe/setPaymentMethods', null, { root: true })
        context.commit('stripe/setDefaultPaymentMethod', null, { root: true })
        return false
    }

    context.commit('setApiStatus', false)
    context.commit('error/setCode', resposne.status, { root: true })

  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
}
