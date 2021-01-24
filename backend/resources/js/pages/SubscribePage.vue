<template>
  <div class="container">
    <div class="panel">
      <span>Select Plan</span>
      <select class="form-control mb-2" v-model="selectedPlan.plan">
          <option
            v-for="plan in plans"
            :key="plan"
            v-text="plan"
          >
          </option>
      </select>
      <button
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

  },
  computed: {
    apiStatus () {
      return this.$store.state.auth.apiStatus
    }
  }
}
</script>
