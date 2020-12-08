@extends(config("app.views").'.layouts.app')

@section('content')
<section class="blog-page-section with-sidebar" ng-controller="MerchantDetailCtrl">
    <div class="container">
        <div class="row">
            @if(isset($merchant['name']))
            <div class="col-lg-8 col-xl-9 left-slide-margin">
                <div class="blog-post post-details single-block">

                    <a href="javascript:;" class="blog-image">
                        <img src="{{$merchant['icon']}}" alt="">
                    </a>




                    <div class="blog-content mt--30">
                        <header>
                            <div class="post-category text-primary">
                                <i class="fas fa-folder"></i>
                                @foreach($merchant['categories'] as $item)
                                <a href="#">{{$item['name']}}</a>, 
                                @endforeach
                            </div>
                            <h3 class="blog-title"> <a href="javascript:;" >{{$merchant['name']}} </a></h3>
                            <div class="post-meta">
                                <span class="post-author">
                                    <i class="fas fa-user"></i>
                                    <span class="text-gray">Experiencia : </span>
                                    {{ $merchant['attributes']['years_experience']}} años
                                </span><br/>
                                
                                @if($merchant['unit_cost']>0)
                                <span class="post-date">
                                    <i class="far fa-calendar-alt"></i>
                                    <span class="text-gray">Precio por hora promedio : </span>
                                    {{$merchant['unit_cost']}}
                                </span><br/>
                                @endif
                                
                                <span class="post-author">
                                    <i class="fas fa-at"></i>
                                    <a href="mailto:{{ $merchant['email']}}">{{ $merchant['email']}}</a> 
                                </span><br/>
                                <span class="post-author">
                                    <i class="fas fa-address-card"></i>
                                    <span class="text-gray">Dirección : </span>
                                    {{ $merchant['address']}} 
                                </span><br/>
                                <span class="post-author">
                                    <i class="fas fa-phone"></i>
                                    <span class="text-gray">Teléfono : </span>
                                    <a href="tel:{{ $merchant['telephone']}}">{{ $merchant['telephone']}}</a>
                                </span>
                            </div>
                        </header>
                        <article>
                            <h3 class="d-none sr-only">Descripcion</h3>
                            <p class="p-0">{{ $merchant['description']}}</p>

                            <blockquote style="display:none">
                                <p>Quisque semper nunc vitae erat pellentesque, ac placerat arcu consectetur. In venenatis elit ac
                                    ultrices convallis.
                                    Duis est nisi, tincidunt ac urna sed, cursus blandit lectus. In ullamcorper sit amet ligula ut
                                    eleifend. Proin dictum
                                    tempor ligula, ac feugiat metus. Sed finibus tortor eu scelerisque scelerisque.</p>
                            </blockquote>
                            @if(isset($merchant['attributes']['experience']))
                            <h4>Experiencia</h4>
                            @foreach($merchant['attributes']['experience'] as $item)
                            <p>{{$item['name']}}</p>
                            @endforeach
                            @endif
                            @if(isset($merchant['attributes']['service']))
                            <h4>Servicios</h4>
                            @foreach($merchant['attributes']['service'] as $item)
                            <p>{{$item['name']}}</p>
                            @endforeach
                            @endif
                            @if(isset($merchant['attributes']['specialty']))
                            <h4>Especialidad</h4>
                            @foreach($merchant['attributes']['specialty'] as $item)
                            <p>{{$item['name']}}</p>
                            @endforeach
                            @endif
                        </article>
                    </div>
                </div>
                <div class="share-block single-block">
                    <h3>Compartir</h3>
                    <div class="sharethis-inline-share-buttons"></div>
                </div>
                <!--div class="related-post-block single-block">
                    <h3>Related Post</h3>
                    <div class="row">
                  <div class="col-md-4 mt--30">
                    <div class="related-post">
                      <div class="image">
                        <img src="image/others/blog-related-1.jpg" alt="">
                      </div>
                      <div class="content">
                        <h4 class="post-date">December 16, 2014</h4>
                        <h2 class="post-title"><a href="blog-details.html">POST WITH Image</a></h2>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mt--30">
                    <div class="related-post">
                      <div class="image">
                        <img src="image/others/blog-related-2.jpg" alt="">
                      </div>
                      <div class="content">
                        <h4 class="post-date">August 31, 2018</h4>
                        <h2 class="post-title"><a href="blog-details-video.html">POST WITH Video</a></h2>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mt--30">
                    <div class="related-post">
                      <div class="image">
                        <img src="image/others/blog-related-3.jpg" alt="">
                      </div>
                      <div class="content">
                        <h4 class="post-date">January 19, 2011</h4>
                        <h2 class="post-title"><a href="blog-details-audio.html">POST WITH Audio</a></h2>
                      </div>
                    </div>
                  </div>
                
                </div>
                </div-->


            </div>
            <div class="col-lg-4 col-xl-3">
                <div class="sidebar-widget">
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Caracteristicas</h2>
                        <ul class="sidebar-list">
                            @if(isset($merchant['attributes']['booking_active']) && $merchant['attributes']['booking_active'])
                            <li><a href="javascript:;"> Reservas Online</a></li>
                            @endif
                            @if(isset($merchant['attributes']['virtual_provider']) && $merchant['attributes']['virtual_provider'])
                            <li><a href="javascript:;"> Video Conferencias con {{$merchant['attributes']['virtual_provider']}}</a></li>
                            @endif
                            @if(isset($merchant['attributes']['has_store']) && $merchant['attributes']['has_store'])
                            <li><a href="javascript:;">Tiene tienda</a></li>
                            @endif
                            @if(isset($merchant['attributes']['store_active']) && $merchant['attributes']['store_active'])
                            <li><a href="javascript:;">Compras en linea</a></li>
                            @endif
                        </ul>
                    </div>
                    @if(count($side_categories)>0)
                    <div class="single-sidebar">
                        <h2 class="sidebar-title">Tienda</h2>
                        <ul class="sidebar-list">
                            @foreach($side_categories as $cat)
                            <li><a href="/a/products/{{$cat['url']}}?merchant_id={{$merchant['id']}}" >{{$cat['name']}} ({{$cat['tots']}})</a></li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    <button class="place-order w-100" id="book-button" ng-click="booking()">Reservar</button>
                    <div class="single-sidebar">
                        <a class="promo-image overflow-image">
                            <img src="image/product/slidebar-promo-product.jpg" alt="">
                        </a>
                    </div>
                </div>
            </div>
            <section class="review-section pt--60" style="width: 100%">
                <h2 class="sr-only d-none">Product Review</h2>
                <div class="container">

                    <div class="product-details-tab">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="profile-tab" data-toggle="tab" href="#profile" role="tab" aria-controls="profile" aria-selected="false">REVIEWS ({{count($merchant['ratings'])}})</a>
                            </li>
                            <li class="nav-item" style="display:none">
                                <a class="nav-link" id="home-tab" data-toggle="tab" href="#home" role="tab" aria-controls="home" aria-selected="true">DESCRIPTION</a>
                            </li>

                        </ul>
                        <div class="tab-content" id="myTabContent">
                            <div class="tab-pane show active" id="profile" role="tabpanel" aria-labelledby="profile-tab">
                                <div class="review-wrapper">
                                    @foreach ($merchant["ratings"] as $rating)
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
                                            <input type="radio" name="star" value="5" ng-model="rating.rating" ng-click="rating.rating = '5'" id="star1">
                                            <label for="star1"></label>
                                            <input type="radio" name="star" value="4" ng-model="rating.rating" ng-click="rating.rating = '4'" id="star2">
                                            <label for="star2"></label>
                                            <input type="radio" name="star" value="3" ng-model="rating.rating" ng-click="rating.rating = '3'" id="star3">
                                            <label for="star3"></label>
                                            <input type="radio" name="star" value="2" ng-model="rating.rating" ng-click="rating.rating = '2'" id="star4">
                                            <label for="star4"></label>
                                            <input type="radio" name="star" value="1" ng-model="rating.rating" ng-click="rating.rating = '1'" id="star5">
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

                                                <div class="col-12">
                                                    <div class="submit-btn">
                                                        <a href="javascript:;" ng-click="addRating()" style="float: right" class="btn btn-black right">Enviar</a>
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
            @endif
        </div>
        <script>
                    var viewData = '@json($merchant)';
        </script>
</section>

@endsection
