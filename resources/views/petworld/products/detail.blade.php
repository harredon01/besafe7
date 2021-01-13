@extends(config("app.views").'.layouts.app')
@section('title', 'Petworld '.$data["product"]['name'])
@section('meta_description', $data["product"]['description'])
@section('content')
<!-- Product Details Block -->
<main class="product-details-section" ng-controller="ProductDetailCtrl">
    <div class="container">
        <div class="pm-product-details">
            <div class="row">
                <!-- Blog Details Image Block -->
                <div class="col-md-6">
                    <div class="image-block">
                        <!-- Zoomable IMage -->
                        @if(count($data["product"]["files"])>0)
                        <img class="lazyload" data-src="{{$data["product"]["files"][0]['file']}}" data-zoom-image="{{$data["product"]["files"][0]['file']}}" alt=""/>

                        <!-- Product Gallery with Slick Slider -->
                        <div>
                            <!-- Slick Single -->
                            @foreach ($data["product"]["files"] as $file)
                            <a href="#" class="gallary-item" data-image="{{$file['file']}}"
                               data-zoom-image="{{$file['file']}}">
                                <img data-src="{{$file['file']}}" class="lazyload" width="250" alt=""/>
                            </a>
                            @endforeach
                        </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 mt-5 mt-md-0">
                    <div class="description-block">
                        <div class="header-block">
                            <h3>{{$data["product"]['name']}}</h3>
                            <div class="navigation">
                                <a href="#"><i class="ion-ios-arrow-back"></i></a>
                                <a href="#"><i class="ion-ios-arrow-forward"></i></a>
                            </div>
                        </div>
                        <!-- Rating Block -->
                        @if ($data["product"]['rating'])
                        <div class="rating-block d-flex  mt--10 mb--15">
                            <div class="rating-widget">
                                @for ($x = 1; $x <= 5; $x++) 
                                @if ($data["product"]['rating'] >= $x)
                                <a href="#" class="single-rating"><i class="fas fa-star"></i></a>
                                @elseif ($data["product"]['rating'] < $x && $data["product"]['rating'] >= ($x-1)&&$x<5)
                                <a href="#" class="single-rating"><i class="fas fa-star-half-alt"></i></a>
                                @else
                                <a href="#" class="single-rating"><i class="far fa-star"></i></a>
                                @endif
                                @endfor
                            </div>
                            <p class="rating-text"><a href="#comment-form">({{$data["product"]['rating_count']}} customer review)</a></p>
                        </div>
                        @endif
                        <!-- Price -->
                        <p class="price" ng-show="product.activeVariant.is_on_sale"><span class="old-price">@{{product.activeVariant.price| currency}}</span>@{{product.activeVariant.sale| currency}}</p>

                        <p class="price" ng-hide="product.activeVariant.is_on_sale">@{{product.activeVariant.price| currency}}</p>

                        <!-- Blog Short Description -->
                        <div class="product-short-para">
                            <p>
                                {!! $data["product"]['description'] !!}
                            </p>
                        </div>
                        <div class="status">
                            <i class="fas fa-check-circle"></i>@{{product.activeVariant.quantity}} En inventario
                        </div>
                        <!-- Amount and Add to cart -->
                        <form action="./" class="add-to-cart" id="add-cart-form">
                            <select class="nice-select" ng-change="selectVariant(product)" ng-model="product.variant_id">
                                <option  ng-repeat="variant in product.variants" ng-value="@{{variant.id}}" value="@{{variant.id}}">@{{variant.description}}</option>
                            </select>
                            <div class="count-input-block">
                                <input type="number" ng-model="product.quantity" class="form-control text-center" value="1">
                            </div>
                            <div class="btn-block" id="prod-cont-@{{product.id}}">
                                <a href="javascript:;" ng-click="addCartItem(product)" class="btn btn-rounded btn-outlined--primary">Agregar al carrito</a>
                            </div>
                        </form>
                        <!-- Wishlist And Compare -->
                        <div class="btn-options" style="display:none">
                            <a href="wishlist.html"><i class="ion-ios-heart-outline"></i>Add to Wishlist</a>
                            <a href="compare.html"><i class="ion-ios-shuffle"></i>Add to Compare</a>
                        </div>
                        <!-- Products Tag & Category Meta -->
                        <div class="product-meta mt--30">
                            <p>Categorias: 
                                @foreach ($data["product"]["categories"] as $cat)
                                <a href="/a/products/{{$cat['url']}}?merchant_id={{$data["product"]["merchants"][0]['id']}}" class="single-meta">{{$cat['name']}}</a>,
                                @endforeach
                            </p>
                            <p style="display:none">Tags: <a href="#" class="single-meta">Food</a></p>
                        </div>
                        <!-- Share Block 1 
                        <div class="share-block-1">
                            <ul class="social-btns">
                                <li><a href="#" class="facebook"><i class="far fa-thumbs-up"></i><span>likes 1</span></a></li>
                                <li><a href="#" class="twitter"><i class="fab fa-twitter"></i> <span>twitter</span></a></li>
                                <li><a href="#" class="google"><i class="fas fa-plus-square"></i> <span>share</span></a></li>
                            </ul>
                        </div>
                        <!-- Sharing Block 2 -->
                        <div class="share-block-2  mt--30">
                            <h4>Compartir</h4>
                            <div class="sharethis-inline-share-buttons"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <section class="review-section pt--60">
        <h2 class="sr-only d-none">Product Review</h2>
        <div class="container">

            <div class="product-details-tab">
                <ul class="nav nav-tabs" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">REVIEWS ({{count($data['product']['ratings'])}})</a>
                    </li>
                    <li class="nav-item" style="display:none">
                        <a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">DESCRIPTION</a>
                    </li>

                </ul>
                <div class="tab-content" id="myTabContent">
                    <div class="tab-pane show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                        <div class="review-wrapper">
                            @foreach ($data["product"]["ratings"] as $rating)
                            <h2 class="title-lg mb--20">1 REVIEW FOR AUCTOR GRAVIDA ENIM</h2>
                            <div class="review-comment mb--20">
                                <div class="avatar">
                                    <img src="image/icon-logo/author-logo.png" alt="">
                                </div>
                                <div class="text">
                                    <div class="rating-widget mb--15">
                                        @for ($x = 1; $x <= 5; $x++) 
                                        @if ($rating['rating'] >= $x)
                                        <span class="single-rating"><i class="fas fa-star"></i></span>
                                        @elseif ($rating['rating'] < $x && $rating['rating'] >= ($x-1)&&$x<5)
                                        <span class="single-rating"><i class="fas fa-star-half-alt"></i></span>
                                        @else
                                        <span class="single-rating"><i class="far fa-star"></i></span>
                                        @endif
                                        @endfor
                                    </div>
                                    <h6 class="author">{{$rating['pseudonim']}} –  <span class="font-weight-400">{{date('d-m-Y', strtotime($rating['created_at']))}}</span> </h6>
                                    <p>{{$rating['comment']}}</p>
                                </div>
                            </div>
                            @endforeach
                            <h2 class="title-lg mb--20 pt--15">Agrega una reseña</h2>
                            <div class="rating-row pt-2">
                                <p class="d-block">Tu calificacion</p>
                                <span class="rating-widget-block">
                                    <input type="radio" name="star" value="5" ng-model="rating.rating" ng-click="rating.rating='5'" id="star1">
                                    <label for="star1"></label>
                                    <input type="radio" name="star" value="4" ng-model="rating.rating" ng-click="rating.rating='4'" id="star2">
                                    <label for="star2"></label>
                                    <input type="radio" name="star" value="3" ng-model="rating.rating" ng-click="rating.rating='3'" id="star3">
                                    <label for="star3"></label>
                                    <input type="radio" name="star" value="2" ng-model="rating.rating" ng-click="rating.rating='2'" id="star4">
                                    <label for="star4"></label>
                                    <input type="radio" name="star" value="1" ng-model="rating.rating" ng-click="rating.rating='1'" id="star5">
                                    <label for="star5"></label>
                                </span>
                                <div id="toast-container"></div>
                                <form action="./" class="mt--15 site-form ">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="message">Comentario</label>
                                                <textarea name="message" ng-model="rating.comment" id="message" cols="30" rows="10" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        
                                        <div class="col-lg-4">
                                            <div class="submit-btn">
                                                <a href="javascript:;" ng-click="addRating()" class="btn btn-black">Enviar</a>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="home" role="tabpanel" aria-labelledby="home-tab">
                        <article>
                            <h2 class="d-none sr-only">tab article</h2>
                            <p>
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Nam fringilla augue nec est tristique auctor. Donec non est at libero vulputate rutrum. Morbi ornare lectus quis justo gravida semper. Nulla tellus mi, vulputate adipiscing cursus eu, suscipit id nulla.</p>
                            <p>
                                Pellentesque aliquet, sem eget laoreet ultrices, ipsum metus feugiat sem, quis fermentum turpis eros eget velit. Donec ac tempus ante. Fusce ultricies massa massa. Fusce aliquam, purus eget sagittis vulputate, sapien libero hendrerit est, sed commodo augue nisi non neque. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed tempor, lorem et placerat vestibulum, metus nisi posuere nisl, in accumsan elit odio quis mi. Cras neque metus, consequat et blandit et, luctus a nunc. Etiam gravida vehicula tellus, in imperdiet ligula euismod eget.
                            </p>
                        </article>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <section>
        <!-- Slider bLock 4 -->
        <div class="pt--60">
            <div class="container">

                <div class="block-title">
                    <h2>YOU MAY ALSO LIKE…</h2>
                </div>
                <div class="petmark-slick-slider border normal-slider" id='dissapear' data-slick-setting='{
                     "autoplay": true,
                     "autoplaySpeed": 3000,
                     "slidesToShow": 5,
                     "arrows": true
                     }'
                     data-slick-responsive='[
                     {"breakpoint":991, "settings": {"slidesToShow": 3} },
                     {"breakpoint":480, "settings": {"slidesToShow": 1,"rows" :1} }
                     ]'>
                    @foreach($data['related_products'][0]['products'] as $relProduct)
                    <div class="single-slide">
                        <div class="pm-product">
                            <div class="image">
                                <a href="/a/product-detail/{{$relProduct['slug']}}"><img class="lazyload" data-src="{{$relProduct['src']}}" alt=""></a>
                                @if($relProduct['variants'][0]['is_on_sale'])
                                <span class="onsale-badge">Sale!</span>
                                @endif
                            </div>
                            <div class="content">
                                <h3>{{$relProduct['name']}}</h3>
                                <div class="price text-red">
                                    @if($relProduct['variants'][0]['is_on_sale'])
                                    <span class="old">${{$relProduct['variants'][0]['price']}}</span>
                                    <span>${{$relProduct['variants'][0]['sale']}}</span>
                                    @else
                                    <span>${{$relProduct['variants'][0]['price']}}</span>
                                    @endif
                                </div>
                                <div class="btn-block">
                                    <a href="/a/product-detail/{{$relProduct['slug']}}" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                
                <div class="petmark-slick-slider border normal-slider" ng-show="related_products.length>0" data-slick-setting='{
                     "autoplay": true,
                     "autoplaySpeed": 3000,
                     "slidesToShow": 5,
                     "arrows": true
                     }'
                     data-slick-responsive='[
                     {"breakpoint":991, "settings": {"slidesToShow": 3} },
                     {"breakpoint":480, "settings": {"slidesToShow": 1,"rows" :1} }
                     ]'>    
                    <div class="single-slide" ng-repeat="relProduct in related_products">
                        <div class="pm-product">
                            <div class="image">
                                <a href="/a/product-detail/@{{relProduct.slug}}"><img ng-src="@{{relProduct.src}}" alt=""></a>
                                <span class="onsale-badge" ng-show="relProduct.activeVariant.is_on_sale">Sale!</span>
                            </div>
                            <div class="content">
                                <h3>@{{relProduct.name}}</h3>
                                <div class="price text-red">
                                    <span class="old" ng-show="relProduct.activeVariant.is_on_sale">@{{relProduct.activeVariant.price | currency}}</span>
                                    <span ng-show="relProduct.activeVariant.is_on_sale">@{{relProduct.activeVariant.sale | currency}}</span>
                                    <span ng-hide="relProduct.activeVariant.is_on_sale">@{{relProduct.activeVariant.price | currency}}</span>
                                </div>
                                <div class="btn-block" ng-hide='relProduct.item_id'>
                                    <select class="nice-select" ng-change="selectVariant(relProduct)" ng-model="relProduct.variant_id">
                                        <option  ng-repeat="variant in relProduct.variants" ng-value="@{{variant.id}}" value="@{{variant.id}}">@{{variant.description}}</option>
                                    </select>
                                    <a href="javascript:;" ng-click="addCartItem(relProduct)" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                </div>
                                <div class="btn-block" ng-show='relProduct.item_id'>
                                    <a href="javascript:;" style="width:15px" ng-click="changeCartQuantity(relProduct, '-')">-</a>
                                    <input type="tel" ng-model="relProduct.quantity"/>
                                    <a href="javascript:;" style="width:15px" ng-click="changeCartQuantity(relProduct, '+')">+</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <!-- <div class="pt--60">
             <div class="container">
 
                 <div class="block-title">
                     <h2>RELATED PRODUCTS</h2>
                 </div>
                 <div class="petmark-slick-slider border normal-slider" data-slick-setting='{
                      "autoplay": true,
                      "autoplaySpeed": 3000,
                      "slidesToShow": 5,
                      "arrows": true
                      }'
                      data-slick-responsive='[
                      {"breakpoint":991, "settings": {"slidesToShow": 3} },
                      {"breakpoint":480, "settings": {"slidesToShow": 1,"rows" :1} }
                      ]'>
 
                     <div class="single-slide">
                         <div class="pm-product">
                             <div class="image">
                                 <a href="product-details.html"><img src="image/product/home-2/product-1.jpg" alt=""></a>
                                 <span class="onsale-badge">Sale!</span>
                             </div>
                             <div class="content">
                                 <h3>Convallis quam sit</h3>
                                 <div class="price text-red">
                                     <span class="old">$200</span>
                                     <span>$300</span>
                                 </div>
                                 <div class="btn-block">
                                     <a href="cart.html" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="single-slide">
                         <div class="pm-product">
                             <div class="image">
                                 <a href="product-details.html"><img src="image/product/home-2/product-2.jpg" alt=""></a>
                                 <span class="onsale-badge">Sale!</span>
                             </div>
                             <div class="content">
                                 <h3>Convallis quam sit</h3>
                                 <div class="price text-red">
                                     <span class="old">$200</span>
                                     <span>$300</span>
                                 </div>
                                 <div class="btn-block">
                                     <a href="cart.html" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="single-slide">
                         <div class="pm-product">
                             <div class="image">
                                 <a href="product-details.html"><img src="image/product/home-2/product-3.jpg" alt=""></a>
                                 <span class="onsale-badge">Sale!</span>
                             </div>
                             <div class="content">
                                 <h3>Convallis quam sit</h3>
                                 <div class="price text-red">
                                     <span class="old">$200</span>
                                     <span>$300</span>
                                 </div>
                                 <div class="btn-block">
                                     <a href="cart.html" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="single-slide">
                         <div class="pm-product">
                             <div class="image">
                                 <a href="product-details.html"><img src="image/product/home-2/product-4.jpg" alt=""></a>
                                 <span class="onsale-badge">Sale!</span>
                             </div>
                             <div class="content">
                                 <h3>Convallis quam sit</h3>
                                 <div class="price text-red">
                                     <span class="old">$200</span>
                                     <span>$300</span>
                                 </div>
                                 <div class="btn-block">
                                     <a href="cart.html" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="single-slide">
                         <div class="pm-product">
                             <div class="image">
                                 <a href="product-details.html"><img src="image/product/home-2/product-5.jpg" alt=""></a>
                                 <span class="onsale-badge">Sale!</span>
                             </div>
                             <div class="content">
                                 <h3>Convallis quam sit</h3>
                                 <div class="price text-red">
                                     <span class="old">$200</span>
                                     <span>$300</span>
                                 </div>
                                 <div class="btn-block">
                                     <a href="cart.html" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                     <div class="single-slide">
                         <div class="pm-product">
                             <div class="image">
                                 <a href="product-details.html"><img src="image/product/home-2/product-5.jpg" alt=""></a>
                                 <span class="onsale-badge">Sale!</span>
                             </div>
                             <div class="content">
                                 <h3>Convallis quam sit</h3>
                                 <div class="price text-red">
                                     <span class="old">$200</span>
                                     <span>$300</span>
                                 </div>
                                 <div class="btn-block">
                                     <a href="cart.html" class="btn btn-outlined btn-rounded">Add to Cart</a>
                                 </div>
                             </div>
                         </div>
                     </div>
                 </div>
             </div>
 
         </div>
        -->
    </section>
    <script>
                var viewData = '@json($data)';
    </script>
</main>
<script type="text/javascript" src="https://platform-api.sharethis.com/js/sharethis.js#property=5fa5a79fcc85000012ec2cee&product=inline-share-buttons" async="async"></script>
@endsection
