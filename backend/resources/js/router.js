import Vue from 'vue'
import VueRouter from 'vue-router'

import MainContents from './pages/MainContents.vue'
import SubscribePage from './pages/SubscribePage.vue'
import PaymentMethodForm from './pages/PaymentMethodForm.vue'
import Login from './pages/Login.vue'

import SystemError from './errorPages/System.vue'
import NotFound from './errorPages/NotFound.vue'

import store from './store'

Vue.use(VueRouter)

const routes = [
  {
    path: '/',
    component: MainContents,
  },
  {
    path: '/login',
    component: Login,
    beforeEnter (to, from, next) {
      if(store.getters['auth/check']) {
        next('/')
      } else {
        next()
      }
    }
  },
  {
    path: '/subscribePage',
    component: SubscribePage,
    beforeEnter (to, from, next) {

      if(store.getters['stripe/hasDefaultPaymentMethod']) {
        next()

      } else if(store.getters['auth/check']){
        next('/paymentMethodForm')

      } else {
        next('/login')
      }
    }
  },
  {
    path: '/paymentMethodForm',
    component: PaymentMethodForm,
    beforeEnter (to, from, next) {
      if(store.getters['auth/check']) {
        next()
      } else {
        next('/login')
      }
    }
  },
  {
    path: '/500',
    component: SystemError,
  },
  {
    path: '*',
    component: NotFound,
  }

]

const router = new VueRouter({
  mode: 'history',
  routes
})

export default router
