angular.module('besafe')
        
        .service('ProductImport',['$q', '$http', function ($q, $http) {

            var getProducts = function (where) {
                let url = '/api/admin/store/products' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getProducts");
                        });
                return def.promise;
                /**/
            }
            var getVariants = function (where) {
                let url = '/api/admin/store/variants' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getVariants");
                        });
                return def.promise;
                /**/
            }
            var getMerchants = function (where) {
                let url = '/api/admin/store/merchants' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getMerchants");
                        });
                return def.promise;
                /**/
            }
            var getCategories = function (where) {
                let url = '/api/admin/store/categories' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to getCategories");
                        });
                return def.promise;
                /**/
            }
            var deleteProductItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/admin/store/products/'+item.id
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to deleteProductItem");
                        });
                return def.promise;
                /**/
            }
            var deleteMerchantItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/admin/store/merchants/'+item.id
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to deleteMerchantItem");
                        });
                return def.promise;
                /**/
            }
            var deleteVariantItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/admin/store/variants/'+item.id
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to deleteVariantItem");
                        });
                return def.promise;
                /**/
            }
            var deleteCategoryItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/admin/store/categories/'+item.id
                    })
                            .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to deleteCategoryItem");
                        });
                return def.promise;
                /**/
            }

            return {
                getProducts:getProducts,
                getVariants:getVariants,
                getMerchants:getMerchants,
                getCategories:getCategories,
                deleteProductItem:deleteProductItem,
                deleteMerchantItem:deleteMerchantItem,
                deleteVariantItem:deleteVariantItem,
                deleteCategoryItem:deleteCategoryItem
            };
        }])