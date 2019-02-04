angular.module('besafe')

        .controller('FoodMessagesCtrl', function ($scope, Food) {
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
                let url = "order_by=id,desc&page=" + $scope.page;
                Food.getMessages(url).then(function (data) {
                    if (data.page == data.last_page) {
                        $scope.loadMore = false;
                    }
                    let itemsCont = data.data;
                    for (item in itemsCont) {
                        itemsCont[item] = $scope.buildItemData(itemsCont[item]);
                    }
                    $scope.items = $scope.items.concat(itemsCont);
                    console.log("Load more",$scope.loadMore);

                },
                        function (data) {

                        });
            }
            $scope.updateItem = function (item) {

                Food.updateMessageItem(item).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.deleteItem = function (item) {

                Food.deleteMessageItem(item).then(function (data) {

                },
                        function (data) {

                        });
            }

        })