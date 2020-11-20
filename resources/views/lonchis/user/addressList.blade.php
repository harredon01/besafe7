
                    <div>
                        Listed Addresses<br><br>
                        <ul>

                            <li id="address-@{{ address.id }}" ng-repeat="address in addresses">
                                <span class="firstName">@{{ address.name }}</span>, 
                                <br/><span class="address">@{{ address.address }}</span>, <span class="city">@{{ address.cityName }}</span>, <span class="postal">@{{ address.postal }}</span>
                                <br/><span class="regionName">@{{ address.regionName }}</span>, <span class="countryName">@{{ address.countryName }}</span>
                                <br/><span class="phone">@{{ address.phone }}</span>
                                <br/><a href="javascript:;" ng-click="editAddressObj(address)" class="editar">Editar</a>
                                <br/><a href="javascript:;" ng-click="deleteAddress(address)" ng-hide="buying" class="editar">Borrar</a>
                            </li>

                        </ul>
                    </div>


