<template>
  <div class="container">

    <div v-if="planId">
      <span>{{ planId }}</span>
    </div>
    <div class="panel">
      <span>Select Plan</span>
      <select class="form-control mb-2" v-model="selectedPlan.plan">
          <option value="">選択してください</option>
          <option
            v-for="plan in plans"
            :key="plan"
            v-text="plan"
          >
          </option>
      </select>
      <button
        v-if="planId"
        class="btn btn-outline-secondary"
        @click="changePlan"
      >
        Change Plan
      </button>
      <button
        v-else
        class="btn btn-outline-secondary"
        @click="subscribePlan"
      >
        Subscribe This Plan
      </button>

    </div>
  </div>
</template>

<style scoped>
.panel {
  border: 1px solid #dedede;
  margin-top: 1rem;
  padding: 1.5rem;
}
</style>

<script>
import { CASUAL_PLAN, STANDARD_PLAN, PREMIUM_PLAN } from '../util'

export default {
  data () {
    return {
      selectedPlan: {
        plan: null,
      },
      plans: ['Casual Plan ￥300', 'Standard Plan ￥500', 'Premium Plan ￥980'],
    }
  },
  methods: {
    async subscribePlan () {
      await this.$store.dispatch('stripe/subscribePlan', this.selectedPlan)

      if(this.apiStatus) {
        this.$router.push('/')
      }
    },
    async changePlan () {
      await this.$store.dispatch('stripe/changePlan', this.selectedPlan)

      if(this.apiStatus) {
        this.$router.push('/')
      }
    },

  },
  computed: {
    apiStatus () {
      return this.$store.state.auth.apiStatus
    },
    planId () {
      return this.$store.getters['stripe/planId']
    },
  },
}
</script>
