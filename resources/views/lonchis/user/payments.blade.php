@extends(config("app.views").'.layouts.app')

@section('content')
<div class="page-section sp-inner-page" ng-controller="PaymentsUserCtrl">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="row">
                    <!-- My Account Tab Menu Start -->
                    @include(config("app.views").'.user.account_menu')
                    <!-- My Account Tab Menu End -->

                    <!-- My Account Tab Content Start -->
                    <div class="col-lg-9 col-12 mt--30 mt-lg-0">
                        <div class="tab-content" id="myaccountContent">
                            <div class="tab-pane" style="display:block" id="account-info" role="tabpanel">
                                <div class="myaccount-content">
                                    <h3>Mis pagos</h3>

                                    <div class="myaccount-table table-responsive text-center">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Orden</th>
                                                    <th>Fecha</th>
                                                    <th>Estado</th>
                                                    <th>Total</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr ng-repeat="payment in payments">
                                                    <td>@{{ payment.id}}</td>
                                                    <td>@{{ payment.order.id}}</td>
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
                            <!-- Single Tab Content End -->
                        </div>
                    </div>
                    <!-- My Account Tab Content End -->
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
