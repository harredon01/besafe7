
<div>
    <ul>

        <li id="source-@{{ source.id }}" ng-repeat="source in sources">
            Type: <span class="type">@{{ source.type }}</span><br/>
            Expiration: <span class="exp_month">@{{ source.exp_month }}</span> / <span class="exp_year">@{{ source.exp_year }}</span>
            <br/>Brand: <span class="brand">@{{ source.brand }}</span>
            <br/>Country: <span class="country">@{{ source.country }}</span>
            <br/>Last Four: <span class="last4" >@{{ source.last4 }}</span>
            <br/><span class="last4" ng-show="source.is_default">Default:  @{{ source.is_default }}</span>
            <br/><a href="javascript:;" ng-click="setAsDefault(source)" ng-hide="source.is_default || buying" class="editar">Set as Default</a>
            <br/><a href="javascript:;" ng-click="deleteSource(source)" ng-hide="source.is_default || buying" class="editar">Delete</a>
            <br/><a href="javascript:;" ng-click="selectSource(source)" ng-show="buying"class="editar">Use</a>
        </li>

    </ul>
</div>



