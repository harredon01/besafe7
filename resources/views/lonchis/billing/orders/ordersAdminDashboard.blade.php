@extends(config("app.views").'.layouts.app')

@section('content')
<div class="page-section sp-inner-page" ng-controller="PaymentsMerchantCtrl">
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
                                    <h3>Mis Ordenes</h3>
                                    <select ng-model="merchant_id" ng-change="selectMerchant()">
                                        <option ng-repeat="merchant in merchants" ng-value="merchant.id">@{{merchant.name}}</option>
                                    </select>
                                    <div class="myaccount-table table-responsive text-center">
                                        <table class="table table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Items</th>
                                                    <th>Direccion</th>
                                                    <th>Usuario</th>
                                                    <th>Action</th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                <tr ng-repeat="order in orders">
                                                    <td>@{{ order.id}}</td>
                                                    <td><p ng-repeat="item in order.items">@{{ item.name }}-@{{ item.sku }}(@{{ item.quantity }}):@{{ item.price | currency }}</p></td>
                                                    <td>@{{ order.order_addresses[0].address}}<br/>@{{ order.order_addresses[0].city}}</td>
                                                    <td>@{{ order.user.firstName}} @{{ order.user.lastName}}<br/>@{{ order.user.cellphone}}<br/>@{{ order.user.email}}</td>
                                                    <td><a href="javascript" ng-hide="payment.status=='approved'" ng-click="fulfillOrder(order)" class="btn">Aprobar Pago</a></td>
                                                </tr>
                                            </tbody>
                                        </table>
                                        <button ng-click="getOrders()">Cargar mas</button>
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
