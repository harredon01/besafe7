@extends(config("app.views").'.layouts.app')

@section('content')
<main class="section-padding shop-page-section"  ng-controller="ReportsCtrl">
    <div class="container">
        <div class="shop-toolbar mb--30">
            <div class="row align-items-center">
                <div class="col-5 col-md-3 col-lg-4">
                    <!-- Product View Mode -->
                    <div class="product-view-mode">
                        <a href="#" class="sortting-btn active" data-target="list "><i class="fas fa-list"></i></a>
                        <a href="#"  class="sortting-btn" data-target="grid"><i class="fas fa-th"></i></a>
                    </div>
                </div>
                <div class="col-12 col-md-9 col-lg-7 offset-lg-1 mt-3 mt-md-0  pr-md-0">
                    <div class="sorting-selection">
                        <div class="row align-items-center pl-md-0 pr-md-0 no-gutters">
                            <div class="col-sm-6 col-md-7 col-xl-8 d-flex align-items-center justify-content-md-end">

                                <span>Sort By:</span>
                                <select id="input-sort" class="form-control nice-select sort-select" style="display: none;">
                                    <option value="" selected="selected">Default Sorting</option>
                                    <option value="">Sort
                                        By:Name (A - Z)</option>
                                    <option value="">Sort
                                        By:Name (Z - A)</option>
                                    <option value="">Sort
                                        By:Price (Low &gt; High)</option>
                                    <option value="">Sort
                                        By:Price (High &gt; Low)</option>
                                    <option value="">Sort
                                        By:Rating (Highest)</option>
                                    <option value="">Sort
                                        By:Rating (Lowest)</option>
                                    <option value="">Sort
                                        By:Model (A - Z)</option>
                                    <option value="">Sort
                                        By:Model (Z - A)</option>
                                </select>
                            </div>
                            <div class="col-md-5 col-xl-4 col-sm-6 text-sm-right mt-sm-0 mt-3">
                                <span>
                                    Showing @{{((current - 1) * per_page) + 1}}–@{{((current - 1) * per_page) + reports.length}} of @{{total}} results
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="shop-product-wrap list with-pagination row border grid-four-column  mr-0 ml-0 no-gutters" id="dissapear">
            @foreach ($reports['data'] as $report)
            <div class="col-lg-3 col-sm-6">
                <div class="pm-product product-type-list  ">
                    <a href="/a/report/{{$report['slug']}}" class="image" tabindex="0">
                    
                        <img src="{{ $report['icon']}}" alt="{{ $report['name']}}">
                    </a>
                    <div class="hover-conents">
                        <ul class="product-btns">
                            <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                        </ul>
                    </div>
                    <div class="content">
                        <h3 class="font-weight-500">
                            <a href="/a/report/{{$report['slug']}}" >{{ $report['name']}}</a>
                        </h3>
                        @if(isset($report['Distance']))
                        <div class="price text-red">
                            <span>Distancia: ${{$report['Distance']}}</span>
                        </div>
                        @endif
                        <div class="btn-block grid-btn">
                            <a href="/a/report/{{$report['slug']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                        </div>
                        <div class="card-list-content ">
                            <div class="rating-widget mt--20">
                                @for ($x = 1; $x <= 5; $x++) 
                                @if ($report['rating'] >= $x)
                                <a href="#" class="single-rating"><i class="fas fa-star"></i></a>
                                @elseif ($report['rating'] < $x && $report['rating'] >= ($x-1)&&$x<5)
                                <a href="#" class="single-rating"><i class="fas fa-star-half-alt"></i></a>
                                @else
                                <a href="#" class="single-rating"><i class="far fa-star"></i></a>
                                @endif
                                @endfor
                            </div>
                            <article>
                                <h3 class="d-none sr-only">Article</h3>
                                <p>{{ $report['description']}}</p>
                            </article>
                            <div class="btn-block d-flex">
                                <a href="/a/report/{{$report['slug']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                <div class="btn-options">
                                    <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Agregar a Favoritos</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="shop-product-wrap list with-pagination row border grid-four-column  mr-0 ml-0 no-gutters">
            <div class="col-lg-3 col-sm-6" ng-repeat="report in reports">
                <div class="pm-product product-type-list  ">
                    <a href="/a/report/@{{report.slug}}" class="image" tabindex="0">
                        <img ng-src="@{{report.icon}}">
                    </a>
                    <div class="hover-conents">
                        <ul class="product-btns">
                            <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                        </ul>
                    </div>
                    <div class="content">
                        <h3 class="font-weight-500"><a href="/a/report/@{{report.slug}}">@{{ report.name}}</a></h3>
                        <div class="price text-red" ng-if="report.Distance > 0">
                            <span>Distancia: @{{ report.Distance }}</span>
                        </div>
                        <div class="btn-block grid-btn">
                            <a href="/a/report/@{{report.slug}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                        </div>
                        <div class="card-list-content ">
                            <div class="rating-widget mt--20">
                                <a href="#" class="single-rating" ng-repeat="n in [].constructor(5) track by $index">
                                    <i class="fas fa-star" ng-if="$index <= report.rating"></i>
                                    <i class="fas fa-star-half-alt" ng-if="$index > report.rating && ($index - 1) <= report.rating && $index < 5"></i>
                                    <i class="far fa-star-half-alt" ng-if="($index - 1) > report.rating"></i>
                                </a>
                            </div>
                            <article>
                                <h3 class="d-none sr-only">Article</h3>
                                <p>@{{ report.description}}</p>
                            </article>
                            <div class="btn-block d-flex">
                                <a href="/a/report/@{{report.slug}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                <div class="btn-options">
                                    <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Agregar a Favoritos</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @include(config("app.views").'.pagination')
        <script>
                    var viewData = '@json($reports)';
        </script>
    </div>
</main>
@endsection
