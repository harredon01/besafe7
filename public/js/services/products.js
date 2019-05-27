angular.module('besafe')
        .service('Cart', function ($q, $http) {

            var addCartItem = function (product_variant_id,merchant_id,quantity,extras) {

                var def = $q.defer();

                $http({
                    method: "post",
                    url: "/api/cart/add",
                    data: {
                        product_variant_id:product_variant_id,
                        merchant_id:merchant_id,
                        quantity:quantity,
                        extras: extras
                    }
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var updateCartItem = function (item_id,quantity) {

                var def = $q.defer();

                $http({
                    method: "post",
                    url: "/api/cart/update",
                    data: {
                        item_id:item_id,
                        quantity:quantity
                    }
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var getCart = function () {
                var def = $q.defer();
                $http({
                    method: "GET",
                    url: "/api/cart/get"
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });
                return def.promise;
            };
            var getCheckoutCart = function () {
                var def = $q.defer();
                $http({
                    method: "GET",
                    url: "/api/cart/checkout"
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });
                return def.promise;
            };
            
            var clearCart = function () {

                var def = $q.defer();

                $http({
                    method: "post",
                    url: "/api/cart/clear",
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            return {
                getCheckoutCart:getCheckoutCart,
                getCart:getCart,
                addCartItem: addCartItem,
                updateCartItem: updateCartItem,
                clearCart: clearCart
            };
        })
        .service('Products', function ($q, $http) {

            var addCartItem = function (product_variant_id,merchant_id,quantity,extras) {

                var def = $q.defer();

                $http({
                    method: "post",
                    url: "/api/cart/add",
                    data: {
                        product_variant_id:product_variant_id,
                        merchant_id:merchant_id,
                        quantity:quantity,
                        extras: extras
                    }
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var updateCartItem = function (item_id,quantity) {

                var def = $q.defer();

                $http({
                    method: "post",
                    url: "/api/cart/update",
                    data: {
                        item_id:item_id,
                        quantity:quantity
                    }
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            var getCart = function () {
                var def = $q.defer();
                $http({
                    method: "GET",
                    url: "/api/cart/get"
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });
                return def.promise;
            };
            var getCheckoutCart = function () {
                var def = $q.defer();
                $http({
                    method: "GET",
                    url: "/api/cart/checkout"
                })
                        .success(function (data) {
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });
                return def.promise;
            };
            
            var clearCart = function () {

                var def = $q.defer();

                $http({
                    method: "post",
                    url: "/api/cart/clear",
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get nearby");
                        });

                return def.promise;
            };
            return {
                getCheckoutCart:getCheckoutCart,
                getCart:getCart,
                addCartItem: addCartItem,
                updateCartItem: updateCartItem,
                clearCart: clearCart
            };
        })