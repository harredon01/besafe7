@extends(config("app.views").'layouts.app')

@section('content')
<div class="container-fluid" ng-controller="SubscriptionsCtrl" ng-init="config={sources:{!! $user->sources !!},gateway:'Stripe'}">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus Subscripciones

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
                    <div class="replace-address">
                        @include('billing.PayU.subscriptionList')
                    </div>


                    <div >
                        @include('billing.PayU.editSubscriptionSourceForm')
                    </div>


                    <div >
                       
                        @include('billing.PayU.editSubscription')
                    
                    </div>


                </div>
            </div>
        </div>
    </div>
</div>
@endsection
