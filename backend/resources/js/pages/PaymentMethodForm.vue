<template>
  <div class="container">

    <div class="panel">

        <div v-if="hasDefaultPaymentMethod" class="panel">
          <span>Your Cards</span>
          <div
            v-for="(dtl, index) in paymentMethods"
            :key="index"
          >
            <ul
              :class="{ 'errors': dtl.id === newId.paymentMethodId }"
              @click="newId.paymentMethodId = dtl.id"
            >
              <li>{{ dtl.billing_details.name }}</li>
              <li>{{ dtl.card.brand }}</li>
              <li>{{ dtl.card.last4 }}</li>
            </ul>
          </div>

          <div style="text-align: right;">

              <button
               class="btn btn-outline-secondary"
               @click="changeDefaultCard"
              >
                Change Default Card
              </button>

          </div>

        </div>

        <form @submit.prevent="registerCard">

            <div class="form-group">
                <label for="card-holder-name">Card Holder Name</label>

                <input
                  type="text"
                  class="form-control"
                  id="card-holder-name"
                  v-model="cardForm.cardHolderName"
                >
            </div>

            <div class="form-group">
                <label for="card-element">Card Element</label>
                <div id="card-element"></div>
            </div>

            <div style="text-align: right;">

                <button
                  type="submit"
                  class="btn btn-outline-secondary"
                >
                  {{ hasDefaultPaymentMethod ? 'Add Card' : 'Register Card' }}
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
      stripe: null,
      newId: {
        paymentMethodId: this.$store.getters['stripe/paymentMethodId'],
      },
      newPay: {
        payment_method: null,
      },
      cardForm: {
        cardHolderName: '',
        cardElement: null,
      }
    }
  },

  methods: {
    async getPaymentMethod () {
      const { setupIntent, error } = await this.stripe.confirmCardSetup (
        this.clientSecret, {
          payment_method: {
            card: this.cardForm.cardElement,
            billing_details: {
              name: this.cardForm.cardHolderName,
            },
          }
        }
      )

      if(error) {
        console.log(error)
      } else {
        this.newPay.payment_method = setupIntent.payment_method
      }
    },

    // カード情報を新たに追加してデフォルトに設定する
    async registerCard () {
      await this.getPaymentMethod ()
      await this.$store.dispatch('stripe/registerCard', this.newPay)

      if(this.apiStatus) {
        this.$router.push('/')
        this.$store.dispatch('stripe/currentClientSecretPaymentMethod')
      }
    },

    async changeDefaultCard () {
      await this.$store.dispatch('stripe/changeDefaultCard', this.newId)

      if(this.apiStatus) {
        this.$router.push('/')
      }
    },

    async createCardElement () {
      this.stripe = await Stripe(`${process.env.MIX_STRIPE_KEY}`)
      const elements = this.stripe.elements()
      this.cardForm.cardElement = elements.create('card')
      this.cardForm.cardElement.mount('#card-element')
    }
  },

  computed: {

    apiStatus () {
      return this.$store.state.stripe.apiStatus
    },

    clientSecret () {
      return this.$store.getters['stripe/clientSecret']
    },

    hasDefaultPaymentMethod () {
      return this.$store.getters['stripe/hasDefaultPaymentMethod']
    },

    paymentMethods () {
      return this.$store.state.stripe.paymentMethods
    },

    defaultPaymentMethod () {
      return this.$store.state.stripe.defaultPaymentMethod
    },

  },
  mounted () {
    this.createCardElement ()
  }
}
</script>
