

<div>
    <div>
        <h2>Formas de pago</h2>
        <br/>
        <a href="javascript:;" ng-click="showMethod('CC')">Tarjeta de Credito</a><br/>
        <a href="javascript:;" ng-click="showMethod('PSE')">Tarjeta de Debito</a><br/>
        <a href="javascript:;" ng-click="showMethod('BALOTO')">Efectivo</a><br/>
    </div>
    <div>
        <div class="billing-address" ng-show="credito || debito">
            @include('products.checkoutBilling')
        </div>

        <div class="credito" ng-show="credito">
            @include('products.paymentCreditCard')
        </div>
        <div class="cash" ng-show="cash">
            @include('products.paymentCash')
        </div>
        <div class="debito" ng-show="debito">
            @include('products.paymentDebitCard')
        </div>
    </div>
</div>


