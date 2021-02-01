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

                if(Auth::user()->subscribed('default')) {
                  $subscription = Auth::user()->subscriptions->first();
                } else {
                  $subscription = null;
                }

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
                  'subscription' => $subscription,
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

        if($user->subscribed('default')) {
            $subscription = $user->subscriptions->first();
        }

        $payment_methods = Auth::user()->paymentMethods()
                                        ->map(function($paymentMethod) {
          return $paymentMethod->asStripePaymentMethod();
        });

        $default_payment_method = Auth::user()->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

         return [
           'user' => $user,
           'subscription' => $subscription,
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

        if($user->subscribed('default')) {
            $subscription = $user->subscriptions->first();
        }

        $payment_methods = Auth::user()->paymentMethods()
                                        ->map(function($paymentMethod) {
          return $paymentMethod->asStripePaymentMethod();
        });

        $default_payment_method = Auth::user()->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

        return [
          'user' => $user,
          'subscription' => $subscription,
          'payment_methods' => $payment_methods,
          'default_payment_method' => $default_payment_method,
        ];
    }

    // サブスクリプションに加入する
    public function subscribePlan(Request $request)
    {
        $user = Auth::user();

        $plan_id = Arr::get(config('services.stripe_plan'), $request->plan);

        // ユーザーのカード情報から、$plan_idが一致するプランに加入させる
        $user->newSubscription('default', $plan_id)->add();

        if($user->subscribed('default')) {
            $subscription = $user->subscriptions->first();
        }

        $payment_methods = Auth::user()->paymentMethods()
                                        ->map(function($paymentMethod) {
          return $paymentMethod->asStripePaymentMethod();
        });

        $default_payment_method = Auth::user()->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

        return [
          'user' => $user,
          'subscription' => $subscription,
          'payment_methods' => $payment_methods,
          'default_payment_method' => $default_payment_method,
        ];
    }

    // プラン変更する「ベーシック」「プレミアム」などの
    public function changePlan(Request $request)
    {
        $user = Auth::user();

        $plan_id = Arr::get(config('services.stripe_plan'), $request->plan);

        // ユーザーのカード情報から、$plan_idが一致するプランに加入させる
        $user->subscription('default')->swap($plan_id);

        if($user->subscribed('default')) {
            $subscription = $user->subscriptions->first();
        }

        $payment_methods = Auth::user()->paymentMethods()
                                        ->map(function($paymentMethod) {
          return $paymentMethod->asStripePaymentMethod();
        });

        $default_payment_method = Auth::user()->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

        return [
          'user' => $user,
          'subscription' => $subscription,
          'payment_methods' => $payment_methods,
          'default_payment_method' => $default_payment_method,
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
