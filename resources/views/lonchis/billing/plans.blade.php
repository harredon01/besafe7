@extends(config("app.views").'.layouts.app')

@section('content')
<script type="text/javascript">
    var sources = {!! $user->sources !!}
    ;
</script>
<div class="container-fluid" ng-controller="PlansCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Hola {{$user->firstName}}, aca puedes comprar planes

                </div>
                <div class="panel-body" >
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
                    @if (count($plans) > 0)
                    <div>
                        Our plans<br><br>
                        <ul>
                            @foreach ($plans as $plan)
                            <li>
                                <h2>
                                    {{ $plan->name }}
                                </h2>
                                <p>
                                    {{ $plan->type }}
                                </p>
                                <p>
                                    Duration {{ $plan->interval }} {{ $plan->interval_type }}
                                </p>
                                <a href="javascript:;" ng-click="selectPlan('{{ $plan->plan_id }}')" class="editar">Select</a>
                            </li>
                            @endforeach
                        </ul>

                    </div>
                    @include(config("app.views").'.groups.selectGroup')

                    @endif

                    <div ng-controller="GatewaysCtrl" ng-show="objectSelected">
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
                        @include(config("app.views").'.billing.PayU.editSubscription')
                    </div>


                    <div ng-show="gateway=='Stripe'">
                        <h2>Stripe</h2>
                        
                        @include(config("app.views").'.billing.Stripe.editSubscriptionForm')

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
