@extends(config("app.views").'layouts.app')

@section('content')
<div class="container-fluid" ng-controller="PaymentsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-body">
                    <div class="replace-address">
                        @include('billing.payments.paymentsList')
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
