                   
                    <div>
                        <ul>
                            <li id="plan-@{{ plan.id }}" ng-repeat="plan in plans">
                                <span class="plan_id" style="display:none">@{{ plan.id }}</span><span class="firstName">@{{ plan.firstName }}</span>, <span class="lastName">@{{ plan.lastName }}</span>
                                <br/>Teléfono: <span class="phone">@{{ plan.phone }}</span>
                                <br/>Código Postal: <span class="postal">@{{ plan.postal }}</span>
                                <br/><span class="plan">@{{ plan.plan }}</span>, <span class="city">@{{ plan.cityName }}</span><span class="city_id" style="display:none">@{{ plan.city_id }}</span>
                                <br/><span class="regionName">@{{ plan.regionName }}</span>, <span class="countryName">@{{ plan.countryName }}</span>
                                <span class="region_id" style="display:none">@{{ plan.region_id }}</span>, <span class="country_id"  style="display:none">@{{ plan.country_id }}</span>
                                <br/>
                                <a href="javascript:;" ng-click="selectPlan(plan.id)">Select</a>
                            </li>
                        </ul>
                    </div>
