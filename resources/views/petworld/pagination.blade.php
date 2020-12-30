<div class="mt--30">
    <div class="pagination-widget">
        <div class="site-pagination">
            <a href="#" class="single-pagination" ng-click="goTo(1)" ng-show="current > 1">|&lt;</a>
            <a href="#" class="single-pagination" ng-click="goTo((current-1))" ng-show="current > 1">&lt;</a>
            <a href="#" class="single-pagination" ng-click="goTo((current-1))" ng-show="current > 1">@{{current-1}}</a>
            <a href="#" class="single-pagination active">@{{current}}</a>
            <a href="#" class="single-pagination" ng-click="goTo((current+1))"  ng-show="current < last">@{{current+1}}</a>
            <a href="#" class="single-pagination" ng-click="goTo((current+1))"  ng-show="current < last">&gt;</a>
            <a href="#" class="single-pagination" ng-click="goTo((last))" ng-show="last > 2 && current < last">&gt;|</a>
        </div>
    </div>

</div>