angular.module('besafe')
        .controller('AdminVariantsCtrl',['$scope', 'ProductImport', function ($scope, ProductImport) {
            $scope.data = {};
            $scope.items = [];
            $scope.loadMore = true,
            $scope.page = 0;
            angular.element(document).ready(function () {
                $scope.getItems();
            });
            
            $scope.buildItemData = function (item) {
                //item.attributes = JSON.parse(item.attributes);
                return item;
            }

            $scope.getItems = function () {
                $scope.page++;
                let url = "order_by=id,desc&includes=product.merchants&page=" + $scope.page;
                ProductImport.getVariants(url).then(function (data) {
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    console.log("Items",data.data);
                    let itemsCont = data.data;
                    for (item in itemsCont) {
                        itemsCont[item] = $scope.buildItemData(itemsCont[item]);
                    }
                    $scope.items = $scope.items.concat(itemsCont);
                },
                        function (data) {

                        });
            }
            $scope.updateItem = function (item) {

                ProductImport.updateVariantItem(item).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.deleteItem = function (item) {

                ProductImport.deleteVariantItem(item).then(function (data) {

                },
                        function (data) {

                        });
            }

        }])