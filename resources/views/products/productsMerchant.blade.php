@extends('layouts.app')

@section('content')
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
                                        @{{ productVariant.price }}
                                    </p>
                                    <a href="javascript:;" ng-click="addCartItem(productVariant,[])" class="editar">agregar</a>
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
                                    <a href="javascript:;" ng-click="addCartItem({{ $productVariant['id']}},1,1)" class="editar">agregar</a>
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
