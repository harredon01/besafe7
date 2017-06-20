@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus Subscripcionesss

                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <div ng-controller="SubscriptionsCtrl" >

                        <div>
                            Listed Subscriptions<br><br>
                            <ul>

                                <li id="subscription-@{{ subscription.id }}" ng-repeat="subscription in subscriptions">
                                    Type: <span class="type">@{{ subscription.type }}</span><br/>
                                    Object: <span class="type">@{{ subscription.object_name }}</span><br/>
                                    Object Code: <span class="type">@{{ subscription.object_code }}</span><br/>
                                    Object Status: <span class="type">@{{ subscription.object_status }}</span><br/>
                                    Object Expiration: <span class="type">@{{ subscription.object_ends }}</span><br/>
                                    Plan: <span class="firstName">@{{ subscription.plan }}</span><br/>
                                    Gateway: <span class="firstName">@{{ subscription.gateway }}</span><br/>
                                    Expires: <span class="firstName">@{{ subscription.ends_at }}</span><br/>
                                    <br/><a href="javascript:;" ng-click="editSubscription(subscription)" class="editar">Edit</a>
                                    <br/><a href="javascript:;" ng-click="deleteSubscription(subscription)" class="editar">Delete</a>
                                    <br/><a href="javascript:;" ng-click="updateCard(subscription)" ng-show="subscription.gateway =='PayU'" class="editar">Update Card</a>
                                    <br/><a href="/sources" ng-show="subscription.gateway =='stripe'" class="editar">Update Default Card</a>
                                </li>

                            </ul>
                        </div>
                        @include('billing.Stripe.editSubscriptionPlanForm')
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
