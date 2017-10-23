
                    <div>
                        Listed Addresses<br><br>
                        <ul>

                            <li id="address-@{{ address.id }}" ng-repeat="address in addresses">
                                Type: <span class="type">@{{ address.type }}</span><br/>
                                <span class="address_id" style="display:none">@{{ address.id }}</span><span class="firstName">@{{ address.name }}</span>, 
                                <br/><span class="name">@{{ address.city }}</span>
                                <br/><span class="phone">@{{ address.phone }}</span>, <span class="postal">@{{ address.postal }}</span>
                                <br/><span class="address">@{{ address.address }}</span>, <span class="city">@{{ address.cityName }}</span><span class="city_id" style="display:none">@{{ address.city_id }}</span>
                                <br/><span class="regionName">@{{ address.regionName }}</span>, <span class="countryName">@{{ address.countryName }}</span>
                                <span class="region_id" style="display:none">@{{ address.region_id }}</span>, <span class="country_id"  style="display:none">@{{ address.country_id }}</span>
                                <br/><a href="javascript:;" ng-click="editAddress(address.id)" class="editar">Editar</a>
                                <br/><a href="javascript:;" ng-click="selectAddress(address.id)" ng-hide="buying" class="editar">Set as Billing</a>
                                <br/><a href="javascript:;" ng-click="deleteAddress(address.id)" ng-hide="buying" class="editar">Borrar</a>
                            </li>

                        </ul>
                    </div>


