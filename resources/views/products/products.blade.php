@extends('layouts.app')

@section('content')
<div class="container-fluid" ng-controller="ProductsCtrl">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
                <div class="panel-heading">Products

                </div>
                <div class="panel-body">
                    @if (count($errors) > 0)
                    <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error}}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif
                    @if (count($products) > 0)
                    <div >
                        <ul>
                            @foreach ($products as $product)
                            <li>
                                <h2>
                                    {{ $product->name }}
                                </h2>
                                <p>
                                    {{ $product->description }}
                                </p>
                                <p>
                                    
                                </p>
                                @foreach ($product->productVariants as $productVariant)
                                <div class="product">
                                    <p>
                                        {{ $productVariant->price }}
                                    </p>
                                    <p>
                                        quantity: {{ $productVariant->quantity }}
                                    </p>
                                    <div class="product-attributes" style="display: none">
                                        {{ $productVariant->attributes }}
                                    </div>
                                    <a href="javascript:;" ng-click="addCartItem({{ $productVariant->id }},1)" class="editar">agregar</a>
                                </div>
                                
                                @endforeach
                                <a href="/products/{{ $product->slug }}">agregar</a>
                            </li>
                            @endforeach
                        </ul>
                        {!! $products->render() !!}
                    </div>

                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
