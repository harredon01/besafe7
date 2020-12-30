                   
                    <div>
                        <ul>
                            <li id="source-@{{ source.id }}" ng-repeat="source in sources">
                                <span class="source_id" style="display:none">@{{ source.id }}</span><span class="firstName">@{{ source.firstName }}</span>, <span class="lastName">@{{ source.lastName }}</span>
                                <br/>Teléfono: <span class="phone">@{{ source.phone }}</span>
                                <br/>Código Postal: <span class="postal">@{{ source.postal }}</span>
                                <br/><span class="source">@{{ source.source }}</span>, <span class="city">@{{ source.cityName }}</span><span class="city_id" style="display:none">@{{ source.city_id }}</span>
                                <br/><span class="regionName">@{{ source.regionName }}</span>, <span class="countryName">@{{ source.countryName }}</span>
                                <span class="region_id" style="display:none">@{{ source.region_id }}</span>, <span class="country_id"  style="display:none">@{{ source.country_id }}</span>
                                <br/>
                                <a href="javascript:;" ng-click="selectSource(source.id)">Select</a>
                            </li>
                        </ul>
                    </div>
