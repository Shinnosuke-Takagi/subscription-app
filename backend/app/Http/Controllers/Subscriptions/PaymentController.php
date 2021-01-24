<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;

class PaymentController extends Controller
{
    // ユーザーのカード情報などをVueに渡す
    public function setup() {
        if(Auth::user()) {
            if(Auth::user()->hasDefaultPaymentMethod()) {

              // ユーザーが登録済のカードを配列で取得する
              $payment_methods = Auth::user()->paymentMethods()
                                              ->map(function($paymentMethod) {
                return $paymentMethod->asStripePaymentMethod();
              });

              // ユーザーがデフォルトに設定しているカード情報を取得する
              $default_payment_method = Auth::user()->defaultPaymentMethod()
                                              ->asStripePaymentMethod();

              return [
                'intent' => Auth::user()->createSetupIntent(),
                'payment_methods' => $payment_methods,
                'default_payment_method' => $default_payment_method,
              ];
            } else {
              return [
                'intent' => Auth::user()->createSetupIntent()
              ];
            }
        } else {
          return null;
        }
    }
    // Stripeにカードを登録する
    public function registerCard(Request $request)
    {
        $user = Auth::user();

        // ユーザーが追加したカード情報を追加してデフォルトに設定する。
        $user->updateDefaultPaymentMethod($request->payment_method);

        $payment_methods = Auth::user()->paymentMethods()
                                        ->map(function($paymentMethod) {
          return $paymentMethod->asStripePaymentMethod();
        });

        $default_payment_method = Auth::user()->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

         return [
           'user' => $user,
           'payment_methods' => $payment_methods,
           'default_payment_method' => $default_payment_method,
         ];
    }

    // カード情報を変更する
    public function changeDefaultCard(Request $request)
    {
        $user = Auth::user();

        // ユーザーが登録しているカードの中から、指定のカード情報を一つ取得する
        $payment_method = $user
                  ->findPaymentMethod($request->paymentMethodId)
                  ->asStripePaymentMethod();

        // ユーザーの追加済のカード情報を上で取得して、デフォルトに設定する
        $user->updateDefaultPaymentMethod($payment_method);

        $payment_methods = Auth::user()->paymentMethods()
                                        ->map(function($paymentMethod) {
          return $paymentMethod->asStripePaymentMethod();
        });

        $default_payment_method = Auth::user()->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

        return [
          'user' => $user,
          'payment_methods' => $payment_methods,
          'default_payment_method' => $default_payment_method,
        ];
    }

    public function subscribePlan(Request $request)
    {
        $user = Auth::user();

        $plan_id = Arr::get(config('services.stripe_plan'), $request->plan);

        $user->newSubscription('default', $plan_id)->add();

        return [
          'user' => $user,
          'subscriptions' => $user->subscriptions->first(),
        ];
    }

    // プラン変更する「ベーシック」「プレミアム」などの
    public function changePlan(Request $request)
    {
        // ユーザーをrequestから取得する
        $user = $request->user();

        // プランIDをrequestから取得する
        $plan = $request->plan;

        // ユーザーのサブスクリプション情報を取得して、新しいプラン
        // (request->plan)へと変更(swap)する
        $user->subscription('main')->swap($plan);

        // ユーザーのサブスクリプション情報の取得
        $subscription = $user->subscriptions->first();

        // planの取得(configに登録されているプランの中から、
          // ユーザーのサブスクリプション情報に含まれているプランIDを使って、
          // 文字列「ベーシック」「プレミアム」の中から取得する)
        // card_last_fourの取得(ユーザーテーブルのcard_last_fourカラムを取得)
        $details = [
          'plan' => Arr::get(config('services.stripe.plans'), $subscription['stripe_plan']),
          'card_last_four' => $user->card_last_four,
        ];

        // detailsにまとめて、responseとして返す
        return [
          'details' => $details
        ];
    }

    public function cancel(Request $request)
    {
        $user = $request->user();

        $user->subscription('main')->cancel();

        $subscription = $user->subscriptions->first();


        $details = [
          'end_date' => $subscription['ends_at']->format('Y-m-d'),
          'plan' => Arr::get(config('services.stripe.plans'), $subscription['stripe_plan']),
          'card_last_four' => $user->card_last_four,
        ];

        return [
          'details' => $details
        ];
    }

    public function resume(Request $request)
    {
        $user = $request->user();

        $user->subscription('main')->resume();

        $subscription = $user->subscriptions->first();

        $details = [
          'plan' => Arr::get(config('services.stripe.plans'), $subscription['stripe_plan']),
          'card_last_four' => $user->card_last_four,
        ];

        return [
          'details' => $details,
        ];
    }
}
