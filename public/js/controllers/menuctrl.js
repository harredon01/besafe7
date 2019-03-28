angular.module('besafe')

        .controller('MenuCtrl', function ($scope, Food) {
            $scope.data = {};
            $scope.items = [];
            $scope.loadMore = true,
            $scope.page = 0;
            $scope.contentType = "";
            $scope.config = {};
            angular.element(document).ready(function () {
                $scope.getItems();
                $scope.contentType = $scope.config.content;
            });

            $scope.buildItemData = function (item) {
                item.attributes = JSON.parse(item.attributes);
                return item;
            }

            $scope.getItems = function () {
                $scope.page++;
                let url = "type="+$scope.contentType+"&order_by=id,desc&page=" + $scope.page;
                Food.getMenu(url).then(function (data) {
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
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

                Food.updateMenuItem(item).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.deleteItem = function (item) {

                Food.deleteMenuItem(item).then(function (data) {

                },
                        function (data) {

                        });
            }

        })