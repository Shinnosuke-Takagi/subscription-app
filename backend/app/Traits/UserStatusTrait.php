<?php

namespace App\Traits;

trait UserStatusTrait
{
    public function getSubscription($user)
    {
        if ($user->subscribed('default')) {
            $subscription = $user->subscriptions->first();
        } else {
            $subscription = null;
        }

        return $subscription;
    }

    // ユーザーが登録済のカードを配列で取得する
    public function getPaymentMethods($user)
    {
        $payment_methods = $user->paymentMethods()
                                        ->map(function ($paymentMethod) {
            return $paymentMethod->asStripePaymentMethod();
        });

        return $payment_methods;
    }

    // ユーザーがデフォルトに設定しているカード情報を取得する
    public function getDefaultPaymentMethod($user)
    {
        $default_payment_method = $user->defaultPaymentMethod()
                                        ->asStripePaymentMethod();

        return $default_payment_method;
    }
}
