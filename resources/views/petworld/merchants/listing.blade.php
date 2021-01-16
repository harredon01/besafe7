@extends(config("app.views").'.layouts.app')
@if(isset($merchants['category']))
@section('title', 'Petworld '.$merchants['category']['name'])
@section('meta_description', $merchants['category']['description'])
@endif
@section('content')
<main class="section-padding shop-page-section"  ng-controller="MerchantsCtrl">
    <div class="container">
        <div class="shop-toolbar">
            <div class="row align-items-center">
                <div class="col-5 col-md-3 col-lg-4 hide-responsive">
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

                                <span style="display: none;">Sort By:</span>
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
                                    Showing @{{((current - 1) * per_page) + 1}}–@{{((current - 1) * per_page) + merchants.length}} of @{{total}} results
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="shop-product-wrap list with-pagination row border grid-four-column  mr-0 ml-0 no-gutters" id="dissapear">
            @foreach ($merchants['data'] as $merchant)
            <div class="col-lg-3 col-sm-6">
                <div class="pm-product product-type-list  ">
                    @if(isset($merchants['category']))
                    @if(isset($merchant['categorizable_id']))
                    <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" class="image" tabindex="0">
                        @else
                        <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['id']}}" class="image" tabindex="0">
                            @endif
                            @else
                            <a href="/a/merchant/{{$merchant['slug']}}/products" class="image" tabindex="0">
                                @endif
                                <img data-src="{{ $merchant['icon']}}" class="lazyload" alt="{{ $merchant['name']}}">
                            </a>
                            <div class="hover-conents">
                                <ul class="product-btns">
                                    <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                                </ul>
                            </div>
                            <div class="content">
                                <h3 class="font-weight-500">
                                    @if(isset($merchants['category']))
                                    @if(isset($merchant['categorizable_id']))
                                    <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" >{{ $merchant['name']}}</a>
                                    @else
                                    <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['id']}}" >{{ $merchant['name']}}</a>
                                    @endif
                                    @else
                                    <a href="/a/merchant/{{$merchant['slug']}}/products" >{{ $merchant['name']}}</a>
                                    @endif
                                </h3>
                                @if ($merchant['unit_cost'] > 0)
                                <div class="price text-red">
                                    <span>${{$merchant['unit_cost']}}</span>
                                </div>
                                @endif
                                @if(isset($merchant['Distance']))
                                <div class="price text-red">
                                    <span>Distancia: {{$merchant['Distance']}}</span>
                                </div>
                                @endif
                                <div class="btn-block grid-btn">
                                    @if(isset($merchants['category']))

                                    @if(isset($merchant['categorizable_id']))
                                    <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                    @else
                                    <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['id']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                    @endif
                                    @else
                                    <a href="/a/merchant/{{$merchant['slug']}}/products" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                    @endif
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
                                        @if(isset($merchants['category']))

                                        @if(isset($merchant['categorizable_id']))
                                        <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['categorizable_id']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                        @else
                                        <a href="/a/products/{{$merchants['category']['url']}}?merchant_id={{ $merchant['id']}}" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                        @endif
                                        @else
                                        <a href="/a/merchant/{{$merchant['slug']}}/products" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Ver</a>
                                        @endif
                                        <div class="btn-options">
                                            <a href="/a/merchant/{{$merchant['slug']}}">Detalle Negocio</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </div>
                            </div>
                            @endforeach
                            </div>
                            <div class="shop-product-wrap list with-pagination row border grid-four-column  mr-0 ml-0 no-gutters">
                                <h2>Elige un proveedor</h2>
                                <div class="col-lg-3 col-sm-6" ng-repeat="merchant in merchants">
                                    <div class="pm-product product-type-list">

                                        <div class="content-a">
                                            <a style="text-align: center;" href="javascript:;" ng-click="openItem(merchant)" class="image" tabindex="0">
                                                <img ng-src="@{{merchant.icon}}" style="width: 60%">
                                            </a>
                                            <h3 class="font-weight-500"><a href="javascript:;" ng-click="openItem(merchant)" >@{{ merchant.name}}</a></h3>
                                            <div class="price" >
                                                <span  ng-if="merchant.address">Direccion: @{{ merchant.address}} </span><br/>
                                                <span  ng-if="merchant.telephone">Tel: <a style="color: #56a700 !important;" ng-href="tel:@{{ merchant.telephone}}">@{{ merchant.telephone}}</a> </span><br/>
                                                <span  ng-if="merchant.email">Email: <a style="color: #56a700 !important;" ng-href="mailto:@{{ merchant.email}}">@{{ merchant.email}}</a> </span><br/>
                                            </div>
                                            <div class="price" ng-if="merchant.Distance > 0">
                                                Distancia:<span style="color: red !important;"> @{{ merchant.Distance | number }} km</span>
                                            </div>
                                            <div class="btn-block d-flex">
                                                <a href="javascript:;" ng-click="openItem(merchant)" class="btn btn-outlined btn-rounded btn-mid" ng-show="showStore" tabindex="0" style="display: block;text-align: center;width: 100%;padding-top: 7px;margin-top: 5px;">Tienda</a>
                                                <a href="/a/merchant/@{{ merchant.slug}}" class="btn btn-outlined btn-rounded btn-mid" ng-hide="showStore" tabindex="0">Ver Detalle</a>

                                            </div>
                                            <div class="btn-options" ng-show="showStore">
                                                <a href="/a/merchant/@{{ merchant.slug}}" style="text-align: center;display: block;width: 100%;margin-right: 0;padding-top: 5px;">Detalle Negocio</a>
                                            </div>
                                            <div class="btn-block grid-btn">
                                                <a href="javascript:;" ng-click="openItem(merchant)" class="btn btn-outlined btn-rounded btn-mid" tabindex="0" style="display: block;text-align: center;width: 100%;padding-top: 7px;margin-top: 5px;">Tienda</a>
                                            </div>
                                        </div>
                                        <div class="hover-conents">
                                            <ul class="product-btns">
                                                <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                                            </ul>
                                        </div>
                                        <div class="content">

                                            <div class="card-list-content ">

                                                <div class="rating-widget mt--20" ng-if="merchant.rating">
                                                    <a href="#" class="single-rating" ng-repeat="n in [].constructor(5) track by $index">
                                                        <i class="fas fa-star" ng-if="$index <= merchant.rating"></i>
                                                        <i class="fas fa-star-half-alt" ng-if="$index > merchant.rating && ($index - 1) <= merchant.rating && $index < 5"></i>
                                                        <i class="far fa-star-half-alt" ng-if="($index - 1) > merchant.rating"></i>
                                                    </a>
                                                </div>
                                                <article ng-show="merchant.activeCategory.tots">
                                                    <h3 class="d-none sr-only">Article</h3>
                                                    


                                                </article>


                                                <div class="product-flex" ng-show="merchant.activeCategory.tots">
                                                    <div class="col-lg-4 col-sm-12" ng-repeat="product in merchant.activeCategory.featured">
                                                        <div class="" id="prod-cont-@{{product.id}}">
                                                            <a href="/a/product-detail/@{{product.slug}}?merchant_id=@{{merchant.id}}" class="image" tabindex="0" style="text-align: center;">
                                                                <img ng-src="@{{product.file}}">
                                                            </a>
                                                            <div class="content">
                                                                <h3 class="font-weight-500"><a href="/a/product-detail/@{{product.slug}}?merchant_id=@{{merchant.id}}" class="ng-binding">@{{product.name}}</a></h3>

                                                                <div class="price text-red" ng-hide="product.activeVariant.is_on_sale" aria-hidden="false">
                                                                    <span class="ng-binding">Desde: @{{product.low | currency}}</span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    
                                                </div>
                                                <a href="javascript:;" ng-click="openItem(merchant)" style="font-size:18px;float: right"><span class="text-primary" >Ver todos (@{{ merchant.activeCategory.tots}}) </span></a>
                                                <div style="clear:both"></div>
                                                <p>@{{ merchant.description}}</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @include(config("app.views").'.pagination')
                            <script>
                                var viewData = '@json($merchants)';
                            </script>
                            </div>
                            </main>
                            @endsection
