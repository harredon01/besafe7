<div class="shop_toolbar t_bottom">
    <div class="pagination">
        <ul><li><a href="javascript:;" ng-click="goTo(1)" ng-show="current > 1"><<</a></li>
            <li class="next"><a href="javascript:;"ng-click="goTo((current - 1))" ng-show="current > 1">&lt;</a></li>
            <li><a href="javascript:;"ng-click="goTo((current - 1))" ng-show="current > 1">@{{current - 1}}</a></li>
            <li class="current">@{{current}}</li>
            <li><a href="javascript:;" ng-click="goTo((current + 1))"  ng-show="current < last">@{{current + 1}}</a></li>
            <li class="next"><a href="javascript:;" ng-click="goTo((current + 1))"  ng-show="current < last">&gt;</a></li>
            <li><a href="javascript:;" ng-click="goTo((last))" ng-show="last > 2 && current < last">>></a></li>
        </ul>
    </div>
</div>