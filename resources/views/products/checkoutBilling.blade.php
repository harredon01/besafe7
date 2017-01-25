<div class="replace-address">
    <h3>Selecciona la direccion del pagador</h3>
    @include('user.checkoutAddressList')
    <a href="javascript:;" ng-click="showAddressForm()">Agregar Dirección</a>
</div>


<div ng-show="addAddress">
    @include('user.editAddressForm')
    <a href="javascript:;" ng-click="hideAddressForm()">Cerrar</a>
</div>