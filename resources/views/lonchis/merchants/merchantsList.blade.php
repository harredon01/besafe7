
                    <div>
                        Tus negocios<br><br>
                        <ul>

                            <li id="address-@{{ merchant.id }}" ng-repeat="merchant in addresses">
                                Type: <span class="type">@{{ merchant.type }}</span><br/>
                                <span class="firstName">@{{ merchant.name }}</span>, 
                                <br/><span class="name">@{{ merchant.city }}</span>
                                <br/><span class="phone">@{{ merchant.phone }}</span>, <span class="postal">@{{ merchant.postal }}</span>
                                <br/><span class="merchant">@{{ merchant.merchant }}</span>, <span class="city">@{{ merchant.cityName }}</span><span class="city_id" style="display:none">@{{ merchant.city_id }}</span>
                                <br/><span class="regionName">@{{ merchant.regionName }}</span>, <span class="countryName">@{{ merchant.countryName }}</span>
                                <span class="region_id" style="display:none">@{{ merchant.region_id }}</span>, <span class="country_id"  style="display:none">@{{ merchant.country_id }}</span>
                                <br/><a href="javascript:;" ng-click="editMerchant(merchant.id)" class="editar">Editar</a>
                                <br/><a href="javascript:;" ng-click="deleteMerchant(merchant.id)" class="editar">Borrar</a>
                            </li>

                        </ul>
                    </div>


