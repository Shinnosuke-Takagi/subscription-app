<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Arr;
use App\Traits\UserStatusTrait;

class PaymentController extends Controller
{
    use UserStatusTrait;
    // ユーザーのカード情報などをVueに渡す
    public function setup()
    {
        if (Auth::user()) {
            if (Auth::user()->hasDefaultPaymentMethod()) {

                return [
                  'intent' => Auth::user()->createSetupIntent(),
                  'subscription' => $this->getSubscription(Auth::user()),
                  'payment_methods' => $this->getPaymentMethods(Auth::user()),
                  'default_payment_method' => $this->getDefaultPaymentMethod(Auth::user()),
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

        return [
          'user' => $user,
          'subscription' => $this->getSubscription($user),
          'payment_methods' => $this->getPaymentMethods($user),
          'default_payment_method' => $this->getDefaultPaymentMethod($user),
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

        return [
          'user' => $user,
          'subscription' => $this->getSubscription($user),
          'payment_methods' => $this->getPaymentMethods($user),
          'default_payment_method' => $this->getDefaultPaymentMethod($user),
        ];
    }

    // サブスクリプションに加入する
    public function subscribePlan(Request $request)
    {
        $user = Auth::user();

        $plan_id = Arr::get(config('services.stripe_plan'), $request->plan);

        // ユーザーのカード情報から、$plan_idが一致するプランに加入させる
        $user->newSubscription('default', $plan_id)->add();

        return [
          'user' => $user,
          'subscription' => $this->getSubscription($user),
          'payment_methods' => $this->getPaymentMethods($user),
          'default_payment_method' => $this->getDefaultPaymentMethod($user),
        ];
    }

    // プラン変更する「ベーシック」「プレミアム」などの
    public function changePlan(Request $request)
    {
        $user = Auth::user();

        $plan_id = Arr::get(config('services.stripe_plan'), $request->plan);

        // ユーザーのカード情報から、$plan_idが一致するプランに加入させる
        $user->subscription('default')->swap($plan_id);

        return [
          'user' => $user,
          'subscription' => $this->getSubscription($user),
          'payment_methods' => $this->getPaymentMethods($user),
          'default_payment_method' => $this->getDefaultPaymentMethod($user),
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
