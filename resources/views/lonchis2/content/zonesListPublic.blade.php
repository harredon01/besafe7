<div>
    Zonas<br><br>
    <h2>Conoce nuestras zonas de cobertura</h2>
    <p>Cada uno de nuestros servicios tiene una zona de cobertura diferente. Visita nuestra app para saber que servicio aplica a cada zona. Estamos expandiendo entonces visita este link para ver información actualizada</p>
    <select ng-model="activeMerchant" ng-change="changeScenario()">
        <option ng-repeat="merchant in merchants" value="@{{ merchant.value}}">@{{ merchant.name}}</option>
    </select><br/>
    <div class="mapcont">
        <div id="map" style="width:100%;height: 600px"></div>
    </div>
</div>
<div>

</div>


