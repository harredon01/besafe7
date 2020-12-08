@extends(config("app.views").'.layouts.app')

@section('content')
<main class="section-padding shop-page-section" id="dissapear">
    <div class="container">
        <div class="row">
            <div class="col-lg-8 col-xl-9 order-lg-2 mb--40">
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
                                            Mostrando @{{((current - 1) * per_page) + 1}}–@{{((current - 1) * per_page) + local_total}} de @{{total}} resultados
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="shop-product-wrap grid with-pagination row border grid-four-column  mr-0 ml-0 no-gutters">
                    @foreach ($data['categories'] as $category)
                    @foreach ($category['products'] as $product)
                    <div class="col-lg-3 col-sm-6">
                        <div class="pm-product  ">
                            <a href="/a/{{$product['slug']}}" class="image" tabindex="0">
                                <img src="{{$product['src']}}" alt="">
                            </a>
                            <div class="hover-conents">
                                <ul class="product-btns">
                                    <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                                    <li><a href="compare.html" tabindex="0"><i class="ion-ios-shuffle"></i></a></li>
                                    <li><a href="#" data-toggle="modal" data-target="#quickModal" tabindex="0"><i class="ion-ios-search"></i></a></li>
                                </ul>
                            </div>
                            <div class="content">
                                <h3 class="font-weight-500"><a href="product-details.html">{{$product['name']}}</a></h3>
                                @if($product['variants'][0]['is_on_sale'])
                                <div class="price text-red">
                                    <span class="old">${{$product['variants'][0]['price']}}</span>
                                    <span>${{$product['variants'][0]['sale']}}</span>
                                </div>
                                @else
                                <div class="price text-red">
                                    <span>${{$product['variants'][0]['price']}}</span>
                                </div>
                                @endif
                                <div class="btn-block grid-btn">
                                    <select class="nice-select">
                                        @foreach ($product['variants'] as $variant)
                                        <option value="{{$variant['id']}}">{{$variant['description']}}</option>
                                        @endforeach
                                    </select>
                                    <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Comprar</a>
                                </div>
                                <div class="card-list-content ">
                                    <div class="rating-widget mt--20">
                                        @for ($x = 1; $x <= 5; $x++) 
                                        @if ($product['rating'] >= $x)
                                        <a href="#" class="single-rating"><i class="fas fa-star"></i></a>
                                        @elseif ($product['rating'] < $x && $product['rating'] >= ($x-1)&&$x<5)
                                        <a href="#" class="single-rating"><i class="fas fa-star-half-alt"></i></a>
                                        @else
                                        <a href="#" class="single-rating"><i class="far fa-star"></i></a>
                                        @endif
                                        @endfor
                                    </div>
                                    <article>
                                        <h3 class="d-none sr-only">Article</h3>
                                        <p>{{$product['description']}}</p>
                                    </article>
                                    <div class="btn-block d-flex">
                                        <select class="nice-select">
                                            @foreach ($product['variants'] as $variant)
                                            <option value="{{$variant['id']}}">{{$variant['description']}}</option>
                                            @endforeach
                                        </select>
                                        <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Comprar</a>
                                        <!--div class="btn-options">
                                            <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Add to Wishlist</a>
                                            <a href="compare.html"><i class="ion-ios-shuffle"></i>Add to Compare</a>
                                        </div-->

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>     
                    @endforeach
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4 col-xl-3 order-lg-1">
                <div class="sidebar-widget">
                    @if(count($data['side_categories'])>0)
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">CATEGORIAS</h2>
                        <ul class="sidebar-filter-list">
                            @foreach($data['side_categories'] as $cat)
                            @if(isset($data['merchant_id']))
                            <li><a href="/a/products/{{$cat['url']}}?merchant_id={{$data['merchant_id']}}" data-count="({{$cat['tots']}})">{{$cat['name']}}</a></li>
                            @else                           
                            <li><a href="/a/products/{{$cat['url']}}" data-count="({{$cat['tots']}})">{{$cat['name']}}</a></li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <!--<div class="single-sidebar">
                        <h2 class="sidebar-title">Color</h2>
                        <ul class="sidebar-filter-list">
                            <li><a href="#" data-count="(4)">Gold</a></li>

                        </ul>
                    </div>
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Filtra por precio</h2>
                        <div class="range-slider pt--10">
                            <div class="pm-range-slider"></div>
                            <div class="slider-price">
                                <p>
                                    <input type="text" id="amount" readonly>
                                    <a href="#" class="btn btn--primary">Filter</a>
                                </p>
                            </div>
                        </div>
                    </div>-->
                    <!--
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Filtra por precio</h2>
                        <a href="product-details.html" class="sidebar-product pm-product product-type-list">
                            <div class="image"  >
                                <img src="image/product/home-1/product-7.jpg" alt="">
                            </div>

                            <div class="content">
                                <h3>Convallis quam sit</h3>
                                <div class="rating-widget">
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star-half-alt"></i></span>
                                    <span class="single-rating"><i class="far fa-star"></i></span>
                                </div>
                                <div class="price text-red">
                                    <span class="old">$200</span>
                                    <span>$300</span>
                                </div>
                            </div>
                        </a>
                        <a href="product-details.html" class="sidebar-product pm-product product-type-list">
                            <div class="image"  >
                                <img src="image/product/home-1/product-7.jpg" alt="">
                            </div>

                            <div class="content">
                                <h3>Convallis quam sit</h3>
                                <div class="rating-widget">
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star-half-alt"></i></span>
                                    <span class="single-rating"><i class="far fa-star"></i></span>
                                </div>
                                <div class="price text-red">
                                    <span class="old">$200</span>
                                    <span>$300</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="single-sidebar">
                        <h2 class="sidebar-title">TAGS</h2>
                        <ul class="sidebar-tag-list">
                            <li><a href="#"> Chilled</a></li>
                            <li><a href="#">Dark</a></li>
                            <li> <a href="#">Euro</a></li>
                            <li><a href="#">Fashion</a></li>
                            <li><a href="#">Food</a></li>
                            <li><a href="#">Hardware</a></li>
                            <li><a href="#">Hat</a></li>
                            <li><a href="#">Hipster</a></li>
                            <li><a href="#">Holidays</a></li>
                            <li><a href="#">Light</a></li>
                            <li><a href="#">Mac</a></li>
                            <li><a href="#">Place</a></li>
                            <li><a href="#">T-Shirt</a></li>
                            <li><a href="#">Travel</a></li>
                            <li><a href="#">Video-2</a></li>
                            <li><a href="#">White</a></li>
                        </ul>
                    </div>-->
                </div>
            </div>
        </div>

        @include(config("app.views").'.pagination')
    </div>
</main>
<script>
    var viewData = '@json($data)';
</script>
<main class="section-padding shop-page-section" ng-controller="ProductsCtrl">
    <div class="container">
        <div class='row'>
            <div class="col-lg-8 col-xl-9 order-lg-2 mb--40">
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
                                            Mostrando @{{((current - 1) * per_page) + 1}}–@{{((current - 1) * per_page) + local_total}} de @{{total}} resultados
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="shop-product-wrap grid with-pagination row border grid-four-column  mr-0 ml-0 no-gutters" id="prods-cont">
                    <div ng-repeat="category in categories">
                        <div class="col-lg-3 col-sm-6" ng-repeat="product in category.products">
                            <div class="pm-product" id="prod-cont-@{{product.id}}" style="min-height: 330px">
                                <a href="/a/product-detail/@{{product.slug}}?merchant_id=@{{product.merchant_id}}" class="image" tabindex="0">
                                    <img ng-src="@{{product.src}}" alt="">
                                </a>
                                <div class="hover-conents">
                                    <ul class="product-btns">
                                        <li ng-hide="product.isFavorite"><a href="javascript:;" ng-click="addFavorite(product)" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                                        <li ng-show="product.isFavorite"><a href="javascript:;" ng-click="deleteFavorite(product)" tabindex="0"><i class="ion-ios-heart" style="color:red"></i></a></li>
                                    </ul>
                                </div>
                                <div class="content">
                                    <h3 class="font-weight-500"><a href="/a/product-detail/@{{product.slug}}?merchant_id=@{{product.merchant_id}}">@{{product.name}}</a></h3>

                                    <div class="price text-red" ng-show="product.activeVariant.is_on_sale">
                                        <span class="old">@{{product.activeVariant.price| currency}}</span>
                                        <span>@{{product.activeVariant.sale| currency}}</span>
                                    </div>
                                    <div class="price text-red" ng-hide="product.activeVariant.is_on_sale">
                                        <span>@{{product.activeVariant.price| currency}}</span>
                                    </div>

                                    <div class="btn-block grid-btn" ng-hide="product.item_id">
                                        <div class="price text-red" ng-show="product.activeVariant.is_on_sale">
                                            <span class="old">@{{product.activeVariant.price| currency}}</span>
                                            <span>@{{product.activeVariant.sale| currency}}</span>
                                        </div>
                                        <div class="price text-red" ng-hide="product.activeVariant.is_on_sale">
                                            <span>@{{product.activeVariant.price| currency}}</span>
                                        </div>
                                        <select class="nice-select" ng-change="selectVariant(product)" ng-model="product.variant_id">
                                            <option  ng-repeat="variant in product.variants" ng-value="@{{variant.id}}" value="@{{variant.id}}">@{{variant.description}}</option>
                                        </select>
                                        <a href="javascript:;" ng-click="addCartItem(product)" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Comprar</a>
                                    </div>
                                    <div class="btn-block grid-btn" ng-show="product.item_id">
                                        <a href="javascript:;" style="width:15px" ng-click="changeCartQuantity(product, '-')">-</a>
                                        <input type="tel" ng-model="product.quantity"/>
                                        <a href="javascript:;" style="width:15px" ng-click="changeCartQuantity(product, '+')">+</a>
                                    </div>
                                    <div class="card-list-content ">
                                        <div class="rating-widget mt--20">
                                            <a href="#" class="single-rating" ng-repeat="n in [].constructor(5) track by $index">
                                                <i class="fas fa-star" ng-if="$index <= product.rating"></i>
                                                <i class="fas fa-star-half-alt" ng-if="$index > product.rating && ($index - 1) <= product.rating && $index < 5"></i>
                                                <i class="far fa-star-half-alt" ng-if="($index - 1) > product.rating"></i>
                                            </a>
                                        </div>
                                        <article>
                                            <h3 class="d-none sr-only">Article</h3>
                                            <p>@{{product.description}}</p>
                                        </article>
                                        <div class="btn-block d-flex">
                                            <select class="nice-select" ng-change="selectVariant(product)" ng-model="product.variant_id">
                                                <option  ng-repeat="variant in product.variants" ng-value="@{{variant.id}}" value="@{{variant.id}}">@{{variant.description}}</option>
                                            </select>
                                            <a href="javascript:;" ng-click="addCartItem(product)" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Comprar</a>
                                            <!--div class="btn-options">
                                                <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Add to Wishlist</a>
                                                <a href="compare.html"><i class="ion-ios-shuffle"></i>Add to Compare</a>
                                            </div-->

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>     
                    </div>
                </div>
            </div>
            <div class="col-lg-4 col-xl-3 order-lg-1">
                <div class="sidebar-widget">
                    
                    @if(count($data['side_categories'])>0)
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">CATEGORIAS</h2>
                        <ul class="sidebar-filter-list">
                            @foreach($data['side_categories'] as $cat)
                            @if(isset($data['merchant_id']))
                            <li><a href="/a/products/{{$cat['url']}}?merchant_id={{$data['merchant_id']}}" data-count="({{$cat['tots']}})">{{$cat['name']}}</a></li>
                            @else                           
                            <li><a href="/a/products/{{$cat['url']}}" data-count="({{$cat['tots']}})">{{$cat['name']}}</a></li>
                            @endif
                            @endforeach
                        </ul>
                    </div>
                    @endif 
                    <!--<div class="single-sidebar">
                        <h2 class="sidebar-title">Color</h2>
                        <ul class="sidebar-filter-list">
                            <li><a href="#" data-count="(4)">Gold</a></li>

                        </ul>
                    </div>-->
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Filtra por precio</h2>
                        <div class="range-slider pt--10">
                            <div class="pm-range-slider"></div>
                            <div class="slider-price">
                                <p>
                                    <input type="text" id="amount" ng-model="range" readonly>
                                    <a href="javascript:;" ng-click="filterPrice()" class="btn btn--primary">Filter</a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <!--
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Filtra por precio</h2>
                        <a href="product-details.html" class="sidebar-product pm-product product-type-list">
                            <div class="image"  >
                                <img src="image/product/home-1/product-7.jpg" alt="">
                            </div>

                            <div class="content">
                                <h3>Convallis quam sit</h3>
                                <div class="rating-widget">
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star-half-alt"></i></span>
                                    <span class="single-rating"><i class="far fa-star"></i></span>
                                </div>
                                <div class="price text-red">
                                    <span class="old">$200</span>
                                    <span>$300</span>
                                </div>
                            </div>
                        </a>
                        <a href="product-details.html" class="sidebar-product pm-product product-type-list">
                            <div class="image"  >
                                <img src="image/product/home-1/product-7.jpg" alt="">
                            </div>

                            <div class="content">
                                <h3>Convallis quam sit</h3>
                                <div class="rating-widget">
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star"></i></span>
                                    <span class="single-rating"><i class="fas fa-star-half-alt"></i></span>
                                    <span class="single-rating"><i class="far fa-star"></i></span>
                                </div>
                                <div class="price text-red">
                                    <span class="old">$200</span>
                                    <span>$300</span>
                                </div>
                            </div>
                        </a>
                    </div>

                    <div class="single-sidebar">
                        <h2 class="sidebar-title">TAGS</h2>
                        <ul class="sidebar-tag-list">
                            <li><a href="#"> Chilled</a></li>
                            <li><a href="#">Dark</a></li>
                            <li> <a href="#">Euro</a></li>
                            <li><a href="#">Fashion</a></li>
                            <li><a href="#">Food</a></li>
                            <li><a href="#">Hardware</a></li>
                            <li><a href="#">Hat</a></li>
                            <li><a href="#">Hipster</a></li>
                            <li><a href="#">Holidays</a></li>
                            <li><a href="#">Light</a></li>
                            <li><a href="#">Mac</a></li>
                            <li><a href="#">Place</a></li>
                            <li><a href="#">T-Shirt</a></li>
                            <li><a href="#">Travel</a></li>
                            <li><a href="#">Video-2</a></li>
                            <li><a href="#">White</a></li>
                        </ul>
                    </div>-->
                </div>
            </div>
        </div>

        @include(config("app.views").'.pagination')
    </div>
</main>
@endsection
