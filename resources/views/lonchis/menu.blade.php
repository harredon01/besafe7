<div class="col-lg-3 col-md-8">
    <!-- Category Nav Start -->
    <div class="category-nav-wrapper bg-blue"  ng-controller="SitemapCtrl">
        <div class="category-nav">
            <h2 class="category-nav__title primary-bg" id="js-cat-nav-title"><i class="fa fa-bars"></i>
                <span>All Categories</span></h2>

            <ul class="category-nav__menu" id="js-cat-nav">
                @foreach ($categories as $category)
                @if (count($category['children']) > 0)
                <li class="category-nav__menu__item has-children">
                    @if(!(strpos($category['type'], 'merchant')===false))
                        <a href="/a/merchants/{{ $category['url']}}" ng-click="goTo('{{ $category['type']}}', $event)">{{ $category['name']}}</a> 
                    @else
                        <a href="/a/reports/{{ $category['url']}}" ng-click="goTo('{{ $category['type']}}', $event)">{{ $category['name']}}</a> 
                    @endif
                    <div class="category-nav__submenu">
                        <div class="category-nav__submenu--inner">
                            <ul>
                                @foreach ($category['children'] as $child)
                                <li>
                                    @if(!(strpos($child['type'], 'merchant')===false))
                                        <a href="/a/merchants/{{ $child['url']}}" ng-click="goTo('{{ $child['type']}}', $event)">{{ $child['name']}}</a>
                                    @else
                                        <a href="/a/reports/{{ $child['url']}}" ng-click="goTo('{{ $child['type']}}', $event)">{{ $child['name']}}</a>
                                    @endif
                                </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                </li>
                @else
                <li class="category-nav__menu__item">
                    @if(!(strpos($category['type'], 'merchant')===false))
                        <a href="/a/merchants/{{ $category['url']}}" ng-click="goTo('{{ $category['type']}}', $event)">{{ $category['name']}}</a> 
                    @else
                        <a href="/a/reports/{{ $category['url']}}" ng-click="goTo('{{ $category['type']}}', $event)">{{ $category['name']}}</a> 
                    @endif
                </li>
                @endif
                @endforeach

            </ul>
        </div>
    </div>
    <!-- Category Nav End -->
    <div class="category-mobile-menu"></div>
</div>