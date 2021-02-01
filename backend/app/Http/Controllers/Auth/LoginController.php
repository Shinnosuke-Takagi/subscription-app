<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    protected function authenticated(Request $request, $user)
    {
        if($user->hasDefaultPaymentMethod()) {

          $payment_methods = $user->paymentMethods()
                                          ->map(function($paymentMethod) {
            return $paymentMethod->asStripePaymentMethod();
          });

          $default_payment_method = $user->defaultPaymentMethod()
                                          ->asStripePaymentMethod();

          if($user->subscribed('default')) {
              $subscription = $user->subscriptions->first();
          }

          return [
            'user' => $user,
            'intent' => $user->createSetupIntent(),
            'subscription' => $subscription,
            'payment_methods' => $payment_methods,
            'default_payment_method' => $default_payment_method,
          ];

        } else {

          return [
            'user' => $user,
            'intent' => $user->createSetupIntent(),
          ];
        }

    }

    protected function loggedOut(Request $request)
    {
        $request->session()->regenerate();

        return response()->json();
    }
}
