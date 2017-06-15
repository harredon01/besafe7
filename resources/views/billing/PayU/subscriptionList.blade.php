
<div>
    Listed Subscriptions<br><br>
    <ul>

        <li id="subscription-@{{ subscription.id }}" ng-repeat="subscription in subscriptions">
            Type: <span class="type">@{{ subscription.type }}</span><br/>
            <span class="subscription_id" style="display:none">@{{ subscription.id }}</span><span class="firstName">@{{ subscription.firstName }}</span>, <span class="lastName">@{{ subscription.lastName }}</span>
            <br/><span class="phone">@{{ subscription.phone }}</span>, <span class="postal">@{{ subscription.postal }}</span>
            <br/><span class="subscription">@{{ subscription.subscription }}</span>, <span class="city">@{{ subscription.cityName }}</span><span class="city_id" style="display:none">@{{ subscription.city_id }}</span>
            <br/><span class="regionName">@{{ subscription.regionName }}</span>, <span class="countryName">@{{ subscription.countryName }}</span>
            <span class="region_id" style="display:none">@{{ subscription.region_id }}</span>, <span class="country_id"  style="display:none">@{{ subscription.country_id }}</span>
            <br/><a href="javascript:;" ng-click="editSubscription(subscription.id)" class="editar">Edit</a>
            <br/><a href="javascript:;" ng-click="deleteSubscription(subscription.id)" class="editar">Borrar</a>
        </li>

    </ul>
</div>



