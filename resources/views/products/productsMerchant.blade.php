@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="ProductsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
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

                                    <img src="{{ $img['file']}}" style="width: 100%"/>


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
                                    <a href="javascript:;" ng-click="addCartItem({{ $productVariant['id']}},1,this)" class="editar">agregar</a>
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
        </div>
    </div>
</div>
@endsection
