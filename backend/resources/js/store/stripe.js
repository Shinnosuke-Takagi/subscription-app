import { OK } from '../util'
import { UNPROCESSABLE_ENTITY } from '../util'

const state = {
  intent: null,
  paymentMethods: null,
  defaultPaymentMethod: null,
  apiStatus: null,
}

const getters = {
  hasDefaultPaymentMethod: state => !! state.defaultPaymentMethod,
  clientSecret: state => state.intent ? state.intent.client_secret : null,
  paymentMethodId: state => state.defaultPaymentMethod ? state.defaultPaymentMethod.id : null,
}

const mutations = {
  setIntent (state, intent) {
    state.intent = intent
  },
  setPaymentMethods (state, paymentMethods) {
    state.paymentMethods = paymentMethods
  },
  setDefaultPaymentMethod (state, defaultPaymentMethod) {
    state.defaultPaymentMethod = defaultPaymentMethod
  },
  setApiStatus (state, status) {
    state.apiStatus = status
  },
}

const actions = {
  // 新規ユーザーのシークレットと登録済ならその人のカード情報などを取得する
  async currentClientSecretPaymentMethod (context) {
    const response = await axios.get('/api/setup')

    if(response.status === OK) {
      context.commit('setApiStatus', true)
      context.commit('setIntent', response.data.intent || null)
      context.commit('setPaymentMethods', response.data.payment_methods || null)
      context.commit('setDefaultPaymentMethod', response.data.default_payment_method || null)
      return false
    }

    context.commit('setApiStatus', false)
    context.commit('error/setCode', response.status, { root: true })

  },

  // 送られてきたカード情報(PaymentMethod)をStripeに追加してデフォルトに設定するアクション
  async registerCard (context, data) {

    const response = await axios.post('/api/registerCard', data)

    if(response.status === OK) {
      context.commit('setApiStatus', true)
      context.commit('setPaymentMethods', response.data.payment_methods)
      context.commit('setDefaultPaymentMethod', response.data.default_payment_method)
      context.commit('auth/setUser', response.data.user, { root:true })
      return false
    }

    context.commit('setApiStatus', false)
    context.commit('error/setCode', response.status, { root:true })
  },

  // 送られてきた登録済カード情報をデフォルトへ設定するアクション
  async changeDefaultCard (context, data) {

    const response = await axios.post('/api/changeDefaultCard', data)

    if(response.status === OK) {
      context.commit('setApiStatus', true)
      context.commit('setPaymentMethods', response.data.payment_methods)
      context.commit('setDefaultPaymentMethod', response.data.default_payment_method)
      context.commit('auth/setUser', response.data.user, { root:true })
      return false
    }

    context.commit('setApiStatus', false)
    context.commit('error/setCode', response.status, { root:true })
  },

  async subscribePlan (context, data) {
    const response = await axios.post('/api/subscribePlan', data)

    console.log(response)
  },
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions,
}
