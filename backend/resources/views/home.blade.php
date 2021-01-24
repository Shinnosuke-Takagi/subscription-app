@extends('layouts.app')

@section('content')
<Subscription-form
  :initial-public-key='@json(config('services.stripe.key'))'
  :initial-plans='@json(config('services.stripe.plans'))'
  subscribe-endpoint="{{ route('subscriptions.subscribe') }}"
  update-card-endpoint="{{ route('subscriptions.updateCard') }}"
  change-plan-endpoint="{{ route('subscriptions.changePlan') }}"
  cancel-endpoint="{{ route('subscriptions.unsubscribe') }}"
  resume-endpoint="{{ route('subscriptions.resume') }}"
>
</Subscription-form>
@endsection
