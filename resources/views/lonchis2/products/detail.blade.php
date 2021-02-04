@extends(config("app.views").'.layouts.app')
@section('title', 'Petworld '.$data["product"]['name'])
@section('meta_description', $data["product"]['description'])
@section('content')
<div ng-controller="ProductDetailCtrl">
    <!--product details start-->
    <div class="product_details mt-70 mb-70">
        <div class="container">
            <div class="row">
                <div class="col-lg-6 col-md-6">
                    <div class="product-details-tab">
                        @if(count($data["product"]["files"])>0)
                        <div id="img-1" class="zoomWrapper single-zoom">
                            <a href="#">
                                <img id="zoom1" src="{{$data["product"]["files"][0]['file']}}" data-zoom-image="{{$data["product"]["files"][0]['file']}}" alt="big-1">
                            </a>
                        </div>
                        <div class="single-zoom-thumb">
                            <ul class="s-tab-zoom owl-carousel single-product-active" id="gallery_01">
                                @foreach ($data["product"]["files"] as $file)
                                <li>
                                    <a href="#" class="elevatezoom-gallery active" data-update="" data-image="{{$file['file']}}" data-zoom-image="{{$file['file']}}">
                                        <img src="{{$file['file']}}" alt="zo-th-1"/>
                                    </a>
                                </li>
                                @endforeach
                            </ul>
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 col-md-6">
                    <div class="product_d_right">
                        <form action="#">

                            <h1><a href="javascript:;">{{$data["product"]['name']}}</a></h1>
                            @if ($data["product"]['rating'])
                            <div class=" product_ratting">
                                <ul>
                                    @for ($x = 1; $x <= 5; $x++) 
                                    @if ($data["product"]['rating'] >= $x)
                                    <li><a href="#"><i class="icon-star"></i></a></li>
                                    @elseif ($data["product"]['rating'] < $x && $data["product"]['rating'] >= ($x-1)&&$x<5)
                                    <li><a href="#"><i class="icon-star-half-full"></i></a></li>
                                    @else
                                    <li><a href="#"><i class="icon-star-empty"></i></a></li>
                                    @endif
                                    @endfor
                                    <li class="review"><a href="#"> ({{$data["product"]['rating_count']}} reseñas ) </a></li>
                                </ul>

                            </div>
                            @endif
                            <div class="price_box" ng-show="product.activeVariant.is_on_sale">
                                <span class="current_price">@{{product.activeVariant.sale| currency}}</span>
                                <span class="old_price">@{{product.activeVariant.price| currency}}</span>
                            </div>
                            <div class="price_box" ng-hide="product.activeVariant.is_on_sale">
                                <span class="current_price">@{{product.activeVariant.price| currency}}</span>
                            </div>
                            <div class="product_desc">
                                <p>{!! $data["product"]['description'] !!}</p>
                            </div>
                            <div class="product_variant quantity">
                                <label>Cantidad</label>
                                <input min="1" max="100" value="1" type="number" ng-model="product.quantity">
                                <button class="button" ng-click="addCartItem(product)" >Comprar</button>  
                            </div>
                            <div class=" product_d_action">
                                <ul>
                                    <li><a href="#" title="Add to wishlist">+ Add to Wishlist</a></li>
                                </ul>
                            </div>
                            <div class="product_meta">
                                <span>Categorías: 
                                    @foreach ($data["product"]["categories"] as $cat)
                                    <a href="/a/products/{{$cat['url']}}?merchant_id={{$data["product"]["merchants"][0]['id']}}">{{$cat['name']}}</a>,
                                    @endforeach
                                </span>
                            </div>

                        </form>
                        <div class="priduct_social">
                            <div class="sharethis-inline-share-buttons"></div>  
                        </div>

                    </div>
                </div>
            </div>
        </div>    
    </div>
    <!--product details end-->


    <!--product area start-->
    <section class="product_area related_products">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="section_title">
                        <h2>Productos Relacionados</h2>
                    </div>
                </div>
            </div> 
            <div class="row">
                <div class="col-12">
                    <div class="product_carousel product_column5 owl-carousel">
                        @foreach($data['related_products'][0]['products'] as $relProduct)
                        <article class="single_product">
                            <figure>
                                <div class="product_thumb">
                                    <a class="primary_img" href="/a/product-detail/{{$relProduct['slug']}}"><img src="{{$relProduct['src']}}" alt=""></a>
                                    <div class="label_product">
                                        @if($relProduct['variants'][0]['is_on_sale'])
                                        <span class="label_sale">Sale</span>
                                        @endif
                                    </div>
                                    <div class="action_links">
                                        <ul>
                                            <li class="add_to_cart"><a href="/a/product-detail/{{$relProduct['slug']}}" data-tippy="Add to cart" data-tippy-placement="top" data-tippy-arrow="true" data-tippy-inertia="true"> <span class="lnr lnr-cart"></span></a></li>

                                        </ul>
                                    </div>
                                </div>
                                <figcaption class="product_content">
                                    <h4 class="product_name"><a href="/a/product-detail/{{$relProduct['slug']}}">{{$relProduct['name']}}</a></h4>
                                    <div class="price_box"> 

                                        @if($relProduct['variants'][0]['is_on_sale'])
                                        <span class="current_price">${{$relProduct['variants'][0]['sale']}}</span>
                                        <span class="old_price">${{$relProduct['variants'][0]['price']}}</span>
                                        @else
                                        <span class="current_price">${{$relProduct['variants'][0]['price']}}</span>
                                        @endif
                                    </div>
                                </figcaption>
                            </figure>
                        </article>
                        @endforeach
                    </div>
                </div>
            </div>  
        </div>
    </section>
</div>
<!--product area end-->
<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=5fa5a79fcc85000012ec2cee&product=inline-share-buttons" async="async"></script>
@endsection
