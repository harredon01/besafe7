                    @if (count($products) > 0)
                    <div ng-controller="ProductCtrl">
                        Products<br><br>
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
                                        {{ $product->price }}
                                    </p>
                                    <div class="product-attributes" style="display: none">
                                        {{ $product->attributes }}
                                    </div>
                                    <a href="javascript:;" ng-click="addCartItem({{ $productVariant->id }})" class="editar">agregar</a>
                                </div>
                                
                                @endforeach
                                <a href="javascript:;" ng-click="addCartItem({{ $product->id }})" class="editar">agregar</a>
                            </li>
                            @endforeach
                        </ul>
                        {!! $products->render() !!}
                    </div>

                    @endif
