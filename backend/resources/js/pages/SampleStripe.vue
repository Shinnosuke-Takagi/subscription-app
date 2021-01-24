<template>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card">
          <div class="card-header">
            Subscription Page
          </div>

          <div v-if="isSubscribed" class="card">
            <div class="card-header">
              Subscribed Menu
            </div>
            <div class="card-body">
              <div v-if="isCancelled">
                キャンセル済みです。：有効期限日 <span v-text="details.end_date"></span>
                <button
                type="button"
                class="btn btn-dark w-100"
                @click="resume"
                >
                  サブスクリプションを再開する
                </button>
              </div>
              <div v-else>
                <div class="form-group">
                  加入中のプラン : <span v-text="details.plan"></span>
                </div>
                <div class="form-group">
                  支払いカード下4桁 : <span v-text="details.card_last_four"></span>
                </div>
                <button
                type="button"
                class="btn btn-warning w-100"
                @click="cancel"
                >
                  サブスクリプションを停止する
                </button>
              </div>
            </div>
          </div>

          <div v-if="! isSubscribed">
            <div class="card-body">
              <!-- サブスクリプションのプランを選択します。 -->
              <div class="form-group">
                <select class="form-control" v-model="plan">
                    <option
                      v-for="(value,key) in planOptions"
                      :value="key"
                      v-text="value"
                    >
                    </option>
                </select>
              </div>

              <!-- カード名義人の名前を入力します -->
              <div class="form-group">
                <label for="">Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="cardHolderName"
                  placeholder="Name on the card"
                >
              </div>

              <!-- Stripeのカード入力フォームが入ります -->
              <div class="form-group">
                <label for="">カード情報の入力</label>
                <div id="card-element"></div>
              </div>

              <!-- 送信（登録）ボタン -->
              <button
                type="button"
                class="btn btn-primary w-100"
                data-secret="this.dataSecret"
                @click="subscribe"
              >
                サブスクリプションを開始する
              </button>
            </div>
          </div>

          <div v-else-if="!isCancelled">
            <div class="card-body">
              <!-- サブスクリプションのプランを変更します。 -->
              <div class="form-group">
                <select class="form-control mb-2" v-model="plan">
                    <option
                      v-for="(value,key) in planOptions"
                      :value="key"
                      v-text="value"
                    >
                    </option>
                </select>
                <button
                  type="button"
                  class="btn btn-success w-100"
                  data-secret="this.dataSecret"
                  @click="changePlan"
                >
                  サブスクリプションのプランを変更する
                </button>
              </div>

              <!-- カード名義人の名前を変更します -->
              <div class="form-group">
                <label for="">Name</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="cardHolderName"
                  placeholder="Name on the card"
                >
              </div>

              <!-- Stripeのカード入力フォームが入ります -->
              <div class="form-group">
                <label for="">カード情報の変更</label>
                <div id="update-element"></div>
              </div>

              <!-- 送信（登録）ボタン -->
              <button
                type="button"
                class="btn btn-info w-100"
                data-secret="this.dataSecret"
                @click="updateCard"
              >
                クレジットカード情報を変更する
              </button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    //ストライプパブリックキー(STRIPE_KEY)を取得します。
    initialPublicKey: {
      type: String,
    },
    //ストライプに登録したプランのID(STRIPE_BASIC_IDなど)を取得します。
    initialPlans: {
      type: Object,
    },
    //Subscribe登録のルートを取得します。
    subscribeEndpoint: {
      type: String,
    },
    updateCardEndpoint: {
      type: String,
    },
    changePlanEndpoint: {
      type: String,
    },
    cancelEndpoint: {
      type: String,
    },
    resumeEndpoint: {
      type: String,
    },
  },
  data() {
    return {
      intent: null,
      stripe: null,
      publicKey: this.initialPublicKey,
      dataSecret: '',
      card: null,
      cardHolderName: '',
      plan: '',
      planOptions: this.initialPlans,
      status: '',
      details: {},
    }
  },
  methods: {
    // Payment_methodを取得するためのメソッドです。
    //targetにはこのメソッドの最後にリターンされるpayment_methodが入ります。
    async getPaymentMethod (target) {

      // コントローラーから取得したシークレットです。
      const clientSecret = this.dataSecret

      // 一つ目の値として、setupIntentにStripeのシークレット(clientSecret)を代入
      // 二つ目の値に、payment_methodという名前で配列にまとめる
      // card(カード情報)↓↓のマウントのところで処理しています
      // billing_details(カード名義人の名前)
      const { setupIntent, error } = await this.stripe.confirmCardSetup(
        clientSecret, {
          payment_method: {
            card: this.card,
            billing_details: { name: this.cardHolderName }
          }
        }
      )

      if(error) {
        console.log(error)
      } else {
        // payment_methodをリターンします。
        return setupIntent.payment_method
      }
    },

    // サブスクリプションプランを登録します。
    async subscribe(e) {

      // getPaymentMethodでpayment_methodを取得します。
      const paymentMethod = await this.getPaymentMethod(e.target)

      // コントローラーに送る値を設定します。(payment_methodとプラン)
      const params = {
        payment_method: paymentMethod,
        plan: this.plan
      }

      // route(subscriptions.store)に値をpostします。
      axios.post(this.subscribeEndpoint, params)
        .then( (e) => {
          console.log(e)
          // 返ってきた値から、
          // details->ユーザーのプラン、カード情報(下4桁)
          // をセットする
          this.details = e.data.details

          // POSTに成功したら、入力フォームのリセットと、
          // status->ユーザーの状態の値をsubscribed(サブスク加入中に更新する)
          // fetchPageメソッドでページを更新する
          if(e.status === 200) {
            this.resetForm()
            this.status = 'subscribed'
            this.fetchPage()
          }
        })
    },

    //カード情報の更新のメソッド
    async updateCard (e) {
      // getPaymentMethodでpayment_methodを取得する
      const paymentMethod = await this.getPaymentMethod(e.target)

      // postするためにpayment_methodをparamsに入れる
      const params = {
        payment_method: paymentMethod
      }

      // 新しいカード情報(payment_method)をpostする。
      axios.post(this.updateCardEndpoint, params)
        .then( (e) => {
          console.log(e)

          // responseのdetails(プラン名、カード番号下4桁)からページの
          // subscription Menuの方に反映させる
          this.details = e.data.details

          // response.status 200が返ってきたら、
          // resetFormでフォームリセットし、
          // ユーザーの状態をsubscribed(課金中)に変更し、
          // ページの再読み込みをする。
          if(e.status === 200) {
            this.resetForm()
            this.status = 'subscribed'
            this.fetchPage()
          }
        })
    },

    // プラン変更のメソッド
    changePlan () {
      // 新しいプランをコントローラーの方に送る。
       axios.post(this.changePlanEndpoint, {plan: this.plan})
        .then( (e) => {
          console.log(e)

          // responseからsubscription menu(プラン名、カード番号下4桁)に反映させる。
          this.details = e.data.details

          // response.status 200が返ってきたら、
          // resetFormメソッドで、フォームをリセットし、
          // ユーザーの状態(status)をsubscribed(課金中)にし、
          // ページの再読み込みをする。
          if(e.status === 200) {
            this.resetForm()
            this.status = 'subscribed'
            this.fetchPage()
          }
        })
    },
    cancel () {
       axios.post(this.cancelEndpoint)
        .then( (e) => {
          console.log(e)

          this.details = e.data.details

          if(e.status === 200) {
            this.status = 'cancelled'
            this.fetchPage()
          }
        })
    },
    resume () {
       axios.post(this.resumeEndpoint)
        .then( (e) => {
          console.log(e)

          this.details = e.data.details

          if(e.status === 200) {
            this.status = 'subscribed'
            this.fetchPage()
          }
        })
    },
    // 入力フォームをリセットするメソッドです。
    resetForm() {
      this.plan = ''
      this.cardHolderName = ''
      // StripeのcardElement入力フォームの値をクリアーできるメソッド
      this.card.clear()
    },
    // サブスクページを読み込むメソッドです。
    async fetchPage () {
      await axios.get('/subscription')
          .then( (e) => {
            console.log(e.data)
            // レスポンスのユーザーのカード情報のシークレットを取得
            this.intent = e.data.intent
            // シークレットからカード情報のシークレットのみを取得
            this.dataSecret = this.intent.client_secret
            // ユーザーのサブスク加入状態を取得
            this.status = e.data.status
            // ユーザーのカード情報を取得
            this.details = e.data.details
          })
    },
  },
  computed: {
    // ユーザーがサブスク加入中かどうか算出するメソッドです
    // フォームを切り替えるためのメソッドです
    isSubscribed () {
      return (this.status === 'subscribed' || this.status === 'cancelled')
    },
    isCancelled() {
      return (this.status === 'cancelled')
    }
  },
  watch: {
    // ユーザーのサブスク加入状態(status)を監視して、
    // カード入力フォーム(cardElement)を表示する場所を分けるメソッドです。
    status() {
      Vue.nextTick( () => {
        // Stripeからカード入力フォームを作れる
        this.card = this.stripe.elements().create('card')
        // ユーザーの加入状態(status)によってマウントする場所を分ける
        if(this.status === 'subscribed') {
          this.card.mount('#update-element')
        } else if(this.status === 'unsubscribed') {
          this.card.mount('#card-element')
        }
      })
    }
  },
  mounted() {
    // Stripeを立ち上げる
    this.stripe = Stripe(this.publicKey)
    // Laravelから初期状態(index)を取得する
    this.fetchPage()
  }
}
</script>
