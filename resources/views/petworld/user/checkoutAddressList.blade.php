                   
<div>
    <h4 class="checkout-title">Selecciona una direccion de envío</h4>
    <ul style="color:black;font-weight: bold">
        <li id="address-@{{ address.id}}" ng-repeat="address in addresses">
            <a href="javascript:;"  ng-click="selectAddress(address)">
                <span>@{{ address.name}}</span>
                <br/>Teléfono: <span class="phone">@{{ address.phone}}</span>
                <br/>Código Postal: <span class="postal">@{{ address.postal}}</span>
                <br/><span class="address">@{{ address.address}}</span>, <span class="city">@{{ address.cityName}}</span><span class="city_id" style="display:none">@{{ address.city_id}}</span>
                <br/><span class="regionName">@{{ address.regionName}}</span>, <span class="countryName">@{{ address.countryName}}</span>
                <br/>
            </a>
            <br/><br/>
        </li>
    </ul>
</div>
