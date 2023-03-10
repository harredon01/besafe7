@extends(config("app.views").'.layouts.app')

@section('content')
<div class="page-section sp-inner-page" ng-controller="PaymentsCtrl">
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
                                                    <th>Fecha y total</th>
                                                    <th>Estado</th>
                                                    <th>Usuario</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr ng-repeat="payment in payments">
                                                    <td>@{{ payment.id}}</td>
                                                    <td>@{{ payment.order.id}}</td>
                                                    <td>@{{ payment.updated_at}}<br/>@{{ payment.total | currency}}</td>
                                                    <td>@{{ payment.status}}</td>
                                                    <td>@{{ payment.user.firstName}} @{{ payment.user.lastName}}<br/>@{{ payment.user.cellphone}}<br/>@{{ payment.user.email}}</td>
                                                    <td><a href="javascript:;" ng-hide="payment.status=='approved'" ng-click="approvePayment(payment)" class="btn">Aprobar Pago</a></td>
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
