@extends(config("app.views").'.layouts.app')
@if(count($data['categories'])>0)
@if(count($data['categories'][0]['products'])>0)
@if(isset($data['categories'][0]['products'][0]['merchant_name']))
@section('title', 'Petworld '.$data['categories'][0]['products'][0]['merchant_name'])
@endif
@if(isset($data['categories'][0]['products'][0]['meta_description']))
@section('meta_description', $data['categories'][0]['products'][0]['merchant_description'])
@endif
@endif
@endif
@section('content')

<!--shop  area start-->
<div class="shop_area mt-10 mb-70" ng-controller="ProductsCtrl">
    <div class="container">
        <div class="row">
            <div class="col-lg-9 col-md-12">
                <!--shop wrapper start-->
                <!--shop toolbar start-->
                <div class="shop_toolbar_wrapper">
                    <div class="shop_toolbar_btn">

                        <button data-role="grid_3" type="button" class="active btn-grid-3" data-toggle="tooltip" title="3"></button>

                        <button data-role="grid_4" type="button"  class=" btn-grid-4" data-toggle="tooltip" title="4"></button>

                        <button data-role="grid_list" type="button"  class="btn-list" data-toggle="tooltip" title="List"></button>
                    </div>
                    <div class="page_amount">
                        <p>Mostrando @{{((current - 1) * per_page) + 1}}–@{{((current - 1) * per_page) + local_total}} de @{{total}} resultados</p>
                    </div>
                </div>
                <!--shop toolbar end-->
                <div class="row shop_wrapper" id="dissapear">
                    @foreach ($data['categories'] as $category)
                    @foreach ($category['products'] as $product)
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12 ">
                        <div class="single_product">
                            <div class="product_thumb">
                                <a class="primary_img" href="{{$product['slug']}}">@if(count($product['imgs'])>0)<img src="{{$product['imgs'][0]['file']}}" alt="">@endif</a>
                                <div class="label_product">
                                    @if($product['variants'][0]['is_on_sale'])
                                    <span class="label_sale">Sale</span>
                                    @endif
                                    <span class="label_new">New</span>
                                </div>
                                <div class="action_links">
                                    <ul>
                                        <li class="add_to_cart"><a href="javascript:;" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>

                                    </ul>
                                </div>
                            </div>
                            <div class="product_content grid_content">
                                <h4 class="product_name"><a href="{{$product['slug']}}">{{$product['name']}}</a></h4>
                                <div class="price_box"> 
                                    @if($product['variants'][0]['is_on_sale'])
                                    <span class="current_price">${{$product['variants'][0]['sale']}}</span>
                                    <span class="old_price">${{$product['variants'][0]['price']}}</span>
                                    @else
                                    <span class="current_price">${{$product['variants'][0]['price']}}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="product_content list_content">
                                <h4 class="product_name"><a href="{{$product['slug']}}">{{$product['name']}}</a></h4>
                                <div class="price_box"> 
                                    @if($product['variants'][0]['is_on_sale'])
                                    <span class="current_price">${{$product['variants'][0]['sale']}}</span>
                                    <span class="old_price">${{$product['variants'][0]['price']}}</span>
                                    @else
                                    <span class="current_price">${{$product['variants'][0]['price']}}</span>
                                    @endif
                                </div>
                                <div class="product_desc">
                                    <p>{!! $product['description'] !!}</p>
                                </div>
                                <div class="action_links list_action_right">
                                    <ul>
                                        <li class="add_to_cart"><a href="javascript:;" title="Add to cart">Add to Cart</a></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endforeach
                </div>

                <div class="row shop_wrapper" ng-repeat="category in categories">
                    <div class="col-lg-4 col-md-4 col-sm-6 col-12 " ng-repeat="product in category.products">
                        <div class="single_product" id="prod-cont-@{{product.id}}">
                            <div class="product_thumb">
                                <a class="primary_img" href="/a/product-detail/@{{product.slug}}?merchant_id=@{{product.merchant_id}}"><img ng-src="@{{product.src}}" alt=""></a>
                                <div class="label_product">
                                    <span class="label_sale" ng-show="product.activeVariant.is_on_sale">Sale</span>
                                    <span class="label_new" ng-show="product.isFavorite"><span style="position:relative" class="lnr lnr-heart"></span></span>
                                </div>
                                <div class="action_links">
                                    <ul>
                                        <li class="add_to_cart" ng-hide="product.item_id"><a href="javascript:;" ng-click="addCartItem(product)" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>
                                        <li class="add_to_cart" ng-show="product.item_id"><a href="javascript:;" ng-click="changeCartQuantity(product, '-')" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-circle-minus"></span></a></li>
                                        <li class="add_to_cart" ng-show="product.item_id"><a href="javascript:;" ng-click="changeCartQuantity(product, '+')" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-plus-circle"></span></a></li>
                                        <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box" ng-click="addModal(product)" > <span class="lnr lnr-magnifier"></span></a></li>
                                        <li class="wishlist" ng-hide="product.isFavorite"><a href="javascript:;" ng-click="addFavorite(product)" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li>
                                        <li class="wishlist" ng-show="product.isFavorite"><a href="javascript:;" ng-click="deleteFavorite(product)" data-tippy="Remove from Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="product_content grid_content">
                                <h4 class="product_name"><a href="/a/product-detail/@{{product.slug}}?merchant_id=@{{product.merchant_id}}">@{{product.name}}</a></h4>
                                <div class="price_box" ng-show="product.activeVariant.is_on_sale"> 
                                    <span class="current_price">@{{product.activeVariant.sale| currency}}</span>
                                    <span class="old_price">@{{product.activeVariant.price| currency}}</span>
                                </div>
                                <div class="price_box" ng-hide="product.activeVariant.is_on_sale"> 
                                    <span class="current_price">@{{product.activeVariant.price| currency}}</span>
                                </div>
                                <label>Cantidad</label>
                                <input type="tel" style="width:30px" ng-model="product.quantity"/>
                            </div>
                            <div class="product_content list_content">
                                <h4 class="product_name"><a href="/a/product-detail/@{{product.slug}}?merchant_id=@{{product.merchant_id}}">@{{product.name}}</a></h4>
                                <div class="price_box" ng-show="product.activeVariant.is_on_sale"> 
                                    <span class="current_price">@{{product.activeVariant.sale| currency}}</span>
                                    <span class="old_price">@{{product.activeVariant.price| currency}}</span>
                                </div>
                                <div class="price_box" ng-hide="product.activeVariant.is_on_sale"> 
                                    <span class="current_price">@{{product.activeVariant.price| currency}}</span>
                                </div>
                                <div class="product_desc" ng-bind-html="product.description">
                                </div>
                                <input type="tel" style="width:30px" ng-model="product.quantity"/>
                                <div class="action_links list_action_right">
                                    <ul>
                                        <li class="add_to_cart" ng-hide="product.item_id"><a href="javascript:;" ng-click="addCartItem(product)" title="Add to cart">Comprar</a></li>
                                        <li class="add_to_cart" ng-show="product.item_id"><a href="javascript:;" ng-click="changeCartQuantity(product, '-')" title="Add to cart"><span class="lnr lnr-circle-minus"></span></a></li>
                                        <li class="add_to_cart" ng-show="product.item_id"><a href="javascript:;" ng-click="changeCartQuantity(product, '+')" title="Add to cart"><span class="lnr lnr-plus-circle"></span></a></li>
                                        <li class="quick_button"><a href="#" data-tippy="quick view" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true" data-bs-toggle="modal" data-bs-target="#modal_box"  ng-click="addModal(product)"> <span class="lnr lnr-magnifier"></span></a></li>
                                        <li class="wishlist" ng-hide="product.isFavorite"><a href="javascript:;" ng-click="addFavorite(product)" data-tippy="Add to Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li>
                                        <li class="wishlist" ng-show="product.isFavorite"><a href="javascript:;" ng-click="deleteFavorite(product)" data-tippy="Remove from Wishlist" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"><span class="lnr lnr-heart"></span></a></li>

                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @include(config("app.views").'.pagination')

                <!--shop toolbar end-->
                <!--shop wrapper end-->
            </div>
            <div class="col-lg-3 col-md-12">
                <!--sidebar widget start-->
                <aside class="sidebar_widget">
                    <div class="widget_inner">
                        <div class="widget_list widget_filter">
                            <h3>Filtra por precio</h3>
                            <form> 
                                <div id="slider-range"></div>   
                                <button ng-click="filterPrice()">Filtra</button>
                                <input type="text" name="text" id="amount" ng-model="range" />   
                            </form> 
                        </div>
                        @if(count($data['side_categories'])>0)
                        <div class="widget_list widget_color">
                            <h3>Categorías</h3>
                            <ul>
                                @foreach($data['side_categories'] as $cat)
                                @if(isset($data['merchant_id']))
                                <li>
                                    <a href="/a/products/{{$cat['url']}}?merchant_id={{$data['merchant_id']}}">{{$cat['name']}}  <span>({{$cat['tots']}})</span></a> 
                                </li>
                                @else                           
                                <li>
                                    <a href="/a/products/{{$cat['url']}}">{{$cat['name']}}  <span>({{$cat['tots']}})</span></a> 
                                </li>
                                @endif
                                @endforeach

                            </ul>
                        </div>
                        @endif
                    </div>
                </aside>
                <!--sidebar widget end-->
                <script>
                            var viewData = '@json($data)';
                </script>
            </div>
        </div>
    </div>
</div>
<!--shop  area end-->
@endsection
