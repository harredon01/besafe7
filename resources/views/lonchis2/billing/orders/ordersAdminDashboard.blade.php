@extends(config("app.views").'.layouts.app')

@section('content')
<section class="main_content_area" ng-controller="PaymentsUserCtrl">
    <div class="container">   
        <div class="account_dashboard">
            <div class="row">
                @include(config("app.views").'.user.account_menu')
                <div class="col-sm-12 col-md-9 col-lg-9">
                    <!-- Tab panes -->
                    <div class="tab-content dashboard_content">
                        <div class="tab-pane fade  show active" id="dashboard">
                            <h3>Mis Pagos</h3>
                            <div class="table-responsive">
                                <table class="table">
                                    <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Fecha</th>
                                            <th>Estado</th>
                                            <th>Total</th>
                                            <th>Accoines</th>	 	 	 	
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="payment in payments">
                                            <td>@{{ payment.id}}</td>
                                            <td>@{{ payment.updated_at}}</td>
                                            <td>@{{ payment.status}}</td>
                                            <td>@{{ payment.total | currency}}</td>
                                            <td><a href="javascript" ng-href="/user/payments/@{{payment.id}}" class="btn">Ver</a></td>
                                        </tr>
                                    </tbody>
                                </table>
                                <button ng-click="getPayments()">Cargar mas</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>  
    </div>        	
</section>
@endsection
