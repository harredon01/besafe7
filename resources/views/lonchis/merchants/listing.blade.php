@extends(config("app.views").'.layouts.app')

@section('content')
<main class="section-padding shop-page-section">
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
                                    Showing 1–20 of 52 results
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="shop-product-wrap list with-pagination row border grid-four-column  mr-0 ml-0 no-gutters">
            @foreach ($merchants['data'] as $merchant)
            <div class="col-lg-3 col-sm-6">
                <div class="pm-product product-type-list  ">
                    <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" class="image" tabindex="0">
                        <img src="{{ $merchant['icon']}}" alt="{{ $merchant['name']}}">
                    </a>
                    <div class="hover-conents">
                        <ul class="product-btns">
                            <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                        </ul>
                    </div>
                    <div class="content">
                        <h3 class="font-weight-500"><a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" >{{ $merchant['name']}}</a></h3>
                        @if ($merchant['unit_cost'] > 0)
                        <div class="price text-red">
                            <span>${{$merchant['unit_cost']}}</span>
                        </div>
                        @endif
                        <div class="btn-block grid-btn">
                            <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                        </div>
                        <div class="card-list-content ">
                            <div class="rating-widget mt--20">
                                @for ($x = 1; $x <= 5; $x++) 
                                @if ($merchant['rating'] >= $x)
                                <a href="#" class="single-rating"><i class="fas fa-star"></i></a>
                                @elseif ($merchant['rating'] < $x && $merchant['rating'] >= ($x-1)&&$x<5)
                                <a href="#" class="single-rating"><i class="fas fa-star-half-alt"></i></a>
                                @else
                                <a href="#" class="single-rating"><i class="far fa-star"></i></a>
                                @endif
                                @endfor
                            </div>
                            <article>
                                <h3 class="d-none sr-only">Article</h3>
                                <p>{{ $merchant['description']}}</p>
                            </article>
                            <div class="btn-block d-flex">
                                <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                <div class="btn-options">
                                    <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Agregar a Favoritos</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
            <div class="col-lg-3 col-sm-6" ng-repeat="merchant in merchants">
                <div class="pm-product product-type-list  ">
                    <a href="product-details.html" class="image" tabindex="0">
                        <img ng-src="@{{merchant.icon}}">
                    </a>
                    <div class="hover-conents">
                        <ul class="product-btns">
                            <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                        </ul>
                    </div>
                    <div class="content">
                        <h3 class="font-weight-500"><a href="/a/products/@{{category.url'}}?merchant_id=@{{ merchant.categorizable_id}}">@{{ merchant.name}}</a></h3>
                        <div class="price text-red" ng-if="merchant.unit_cost > 0">
                            <span>@{{ merchant.unit_cost | currency }}</span>
                        </div>
                        <div class="btn-block grid-btn">
                            <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                        </div>
                        <div class="card-list-content ">
                            <div class="rating-widget mt--20">
                                <a href="#" class="single-rating" ng-repeat="n in [].constructor(5) track by $index">
                                    <i class="fas fa-star" ng-if="$index <= merchant.rating"></i>
                                    <i class="fas fa-star-half-alt" ng-if="$index > merchant.rating && ($index - 1) <= merchant.rating && $index < 5"></i>
                                    <i class="far fa-star-half-alt" ng-if="($index - 1) > merchant.rating"></i>
                                </a>
                            </div>
                            <article>
                                <h3 class="d-none sr-only">Article</h3>
                                <p>@{{ merchant.description}}</p>
                            </article>
                            <div class="btn-block d-flex">
                                <a href="/a/products/@{{category.url'}}?merchant_id=@{{ merchant.categorizable_id}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                <div class="btn-options">
                                    <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Agregar a Favoritos</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="mt--30">
            <div class="pagination-widget">
                <div class="site-pagination">
                    <a href="#" class="single-pagination">|&lt;</a>
                    <a href="#" class="single-pagination">&lt;</a>
                    <a href="#" class="single-pagination active">1</a>
                    <a href="#" class="single-pagination">2</a>
                    <a href="#" class="single-pagination">&gt;</a>
                    <a href="#" class="single-pagination">&gt;|</a>
                </div>
            </div>

        </div>
        <script>
            var viewData = '@json($merchants)';
        </script>
    </div>
</main>
@endsection
