@extends(config("app.views").'.layouts.app')

@section('content')

<div ng-controller="PaymentDetailCtrl">
    <!-- Cart Page Start -->
    <div class="cart_area cart-area-padding sp-inner-page--top">
        <div class="container">
            <div class="page-section-title">
                <h1>Tu Orden</h1>
            </div>
            <div class="row">
                <div class="col-12">
                    <form action="#" class="">
                        <!-- Cart Table -->
                        <div class="cart-table table-responsive mb--40">
                            <table class="table">
                                <!-- Head Row -->
                                <thead>
                                    <tr>
                                        <th class="pro-thumbnail">Imagen</th>
                                        <th class="pro-title">Producto</th>
                                        <th class="pro-price">Precio</th>
                                        <th class="pro-quantity">Cantidad</th>
                                        <th class="pro-subtotal">Total</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <!-- Product Row -->
                                    <tr ng-repeat="item in payment.order.items">
                                        <td class="pro-thumbnail"><a href="#"><img ng-src="@{{item.attributes.image.file}}"  alt="Product"></a></td>
                                        <td class="pro-title"><a href="#">@{{item.name}}</a></td>
                                        <td class="pro-price"><span>@{{item.price| currency}}</span></td>
                                        <td class="pro-quantity"><span>@{{item.quantity| currency}}</span></td>
                                        <td class="pro-subtotal"><span>@{{item.priceSumConditions| currency}}</span></td>
                                    </tr>
                                    <!-- Discount Row  -->
                                </tbody>
                            </table>
                        </div>
                        <h3>Condiciones</h3>
                        <div class="cart-table table-responsive mb--40">
                            <table class="table">
                                <!-- Head Row -->
                                <thead>
                                    <tr>
                                        <th class="pro-title">Nombre</th>
                                        <th class="pro-price">Valor</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <!-- Product Row -->
                                    <tr ng-repeat="item in payment.order.order_conditions">
                                        <td class="pro-title"><a href="#">@{{item.name}}</a></td>
                                        <td class="pro-price"><span>@{{item.value| currency}}</span></td>
                                    </tr>
                                    <!-- Discount Row  -->
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="cart-section-2 sp-inner-page--bottom">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-12 d-flex">
                    <div class="cart-summary">
                        <div class="cart-summary-wrap">
                            <h4><span>Envio</span></h4>
                            <p>Recibe<span class="text-primary">@{{payment.order.order_addresses[0].name}}</span></p>
                            <p>Direccion<span class="text-primary">@{{payment.order.order_addresses[0].address}}</span></p>
                            <p>Ciudad <span class="text-primary">@{{payment.order.order_addresses[0].city}}</span></p>
                            <h2>Telefono <span class="text-primary">@{{payment.order.order_addresses[0].phone}}</span></h2>
                        </div>
                    </div>
                </div>

                <!-- Cart Summary -->
                <div class="col-lg-6 col-12 d-flex">
                    <div class="cart-summary">
                        <div class="cart-summary-wrap">
                            <h4><span>Resumen Pago</span></h4>
                            <p>Sub Total <span class="text-primary">@{{payment.subtotal| currency}}</span></p>
                            <p>Transaccion <span class="text-primary">@{{payment.transaction_cost| currency}}</span></p>
                            <h2>Gran Total <span class="text-primary">@{{payment.subtotal| currency}}</span></h2>
                        </div>
                        <div class="cart-summary-button" ng-show="payment.status != 'approved'" ng-click="retryPayment()">
                            <button class="btn btn-success" style="width: 150px;float:right">Reintentar pago</button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-7 mb--20" ng-controller="CheckoutBillingCtrl" ng-show="paymentActive">
                    <h4 class="checkout-title" ng-hide="showResult">Selecciona un método de pago</h4>
                    @include(config("app.views").'.products.checkoutPaymentMethods')
                </div>
            </div>
        </div>
    </div>
    <!-- Cart Page End -->
</div>
@endsection
