@extends(config("app.views").'.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes editar tus Metodos de pago

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

                    <div ng-controller="GatewaysCtrl">
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                <button ng-click="selectGateway('PayU')" class="btn btn-primary">PayU</button>
                                <button ng-click="selectGateway('Stripe')" class="btn btn-primary">Stripe</button>

                            </div>
                        </div>
                    </div>
                    <div class='clear'></div>
                    <div ng-show="gateway=='PayU'">
                        <h2>Pay U </h2>
                        @include(config("app.views").'.billing.PayU.sources')
                    </div>


                    <div ng-show="gateway=='Stripe'">
                        <h2>Stripe</h2>
                        @include(config("app.views").'.billing.Stripe.sources')

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
