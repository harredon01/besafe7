                   
                    <div>
                        Listed Addresses<br><br>
                        <ul>

                            <li id="address-@{{ address.id }}" ng-repeat="address in addresses">
                                <span class="regionName">Type: @{{ address.type }}</span><br/>
                                <span class="address_id" style="display:none">@{{ address.id }}</span><span class="firstName">@{{ address.firstName }}</span>, <span class="lastName">@{{ address.lastName }}</span>
                                <br/><span class="address">@{{ address.address }}</span>, <span class="city">@{{ address.cityName }}</span><span class="city_id" style="display:none">@{{ address.city_id }}</span>
                                <br/><span class="regionName">@{{ address.regionName }}</span>, <span class="countryName">@{{ address.countryName }}</span>
                                <span class="region_id" style="display:none">@{{ address.region_id }}</span>, <span class="country_id"  style="display:none">@{{ address.country_id }}</span>
                                <br/><a href="javascript:;" ng-click="billingAddress(address.id)" ng-hide="address.type=='billing'" class="editar">Set as Billing</a>
                            </li>

                        </ul>
                    </div>
