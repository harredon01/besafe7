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
        <div class="shop-product-wrap grid with-pagination row border grid-four-column  mr-0 ml-0 no-gutters">
            @foreach ($categories as $category)
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
                            <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Add to Cart</a>
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
                                <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Add to Cart</a>
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
    </div>
</main>
<main class="section-padding shop-page-section" ng-controller="ProductsCtrl">
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
        <div class="shop-product-wrap grid with-pagination row border grid-four-column  mr-0 ml-0 no-gutters">
            <div ng-repeat="ng - repeat ="category in categories">
                <div ng-repeat="ng - repeat ="product in category.products">

                    @foreach ($categories as $category)
                    @foreach ($category['products'] as $product)
                    <div class="col-lg-3 col-sm-6">
                        <div class="pm-product  ">
                            <a href="/a/@{{product.slug}}" class="image" tabindex="0">
                                <img ng-src="@{{product.src}}" alt="">
                            </a>
                            <div class="hover-conents">
                                <ul class="product-btns">
                                    <li><a href="wishlist.html" tabindex="0"><i class="ion-ios-heart-outline"></i></a></li>
                                    <li><a href="compare.html" tabindex="0"><i class="ion-ios-shuffle"></i></a></li>
                                    <li><a href="#" data-toggle="modal" data-target="#quickModal" tabindex="0"><i class="ion-ios-search"></i></a></li>
                                </ul>
                            </div>
                            <div class="content">
                                <h3 class="font-weight-500"><a href="product-details.html">@{{product.name}}</a></h3>
                                @if($product['variants'][0]['is_on_sale'])
                                <div class="price text-red" ng-if="product">
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
                                    <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Add to Cart</a>
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
                                        <a href="cart.html" class="btn btn-outlined btn-rounded btn-mid" tabindex="0">Add to Cart</a>
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
    </div>
</main>
<div class="container-fluid" ng-controller="ProductsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <a href="javascript:;" ng-click="changeStore()" class="editar">Cambiar tienda</a>
            <div class="panel panel-default" ng-repeat="category in categories">
                <div class="panel-heading">@{{category.name}}

                </div>
                <div class="panel-body">
                    <div >
                        <ul>
                            <li ng-repeat="product in category.products">
                                <h3>
                                    @{{ product.name}}
                                </h3>
                                <div class='gallery'>
                                    <img ng-repeat="img in product.imgs" ng-src="@{{img.file}}" style="width: 250px"/>
                                </div>

                                <p ng-bind-html="product.description">

                                </p>
                                <p>

                                </p>
                                <div class="product" ng-repeat="productVariant in product.variants">
                                    <p>
                                        @{{ productVariant.price}}
                                    </p>
                                    <a href="javascript:;" ng-click="addCartItem(productVariant, [])" class="editar">agregar</a>
                                </div>

                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="panel panel-default" id="dissapear">

                @foreach ($categories as $category)
                <div class="panel-heading">{{ $category['name']}}

                </div>
                <div class="panel-body">

                    @if (count($category['products']) > 0)
                    <div >
                        <ul>
                            @foreach ($category['products'] as $product)
                            <li>
                                <h3>
                                    {{ $product['name']}}
                                </h3>
                                <div class='gallery'>
                                    @foreach ($product['imgs'] as $img)

                                    <img src="{{ $img['file']}}" style="width: 250px"/>


                                    @endforeach
                                </div>

                                <p>
                                    {!! $product['description']!!}
                                </p>
                                <p>

                                </p>
                                @foreach ($product['variants'] as $productVariant)
                                <div class="product">
                                    <p>
                                        {{ $productVariant['price']}}
                                    </p>
                                    <a href="javascript:;" ng-click="addCartItem({{ $productVariant['id']}}, 1, 1)" class="editar">agregar</a>
                                </div>

                                @endforeach
                            </li>
                            @endforeach
                        </ul>
                    </div>

                    @endif


                </div>
                @endforeach
            </div>

            <script>
                var viewData = '@json($categories)';
            </script>
        </div>
    </div>
</div>
@endsection
