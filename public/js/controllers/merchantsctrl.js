angular.module('besafe')

        .controller('MerchantsCtrl',['$scope', 'LocationService', 'Merchants','Users','Categories', function ($scope, LocationService, Merchants,Users,Categories) {
            $scope.data = {};
            $scope.user = {};
            $scope.page = 0;
            $scope.merchants=[];
            $scope.categories=[];
            $scope.regionVisible = false;
            $scope.editMerchant = false;
            $scope.submitted = false;
            $scope.category;
            angular.element(document).ready(function () {
                let container = JSON.parse(viewData);
                $scope.category = container.category;
                $scope.merchants = container.data;
            });

            $scope.getMerchants = function (page) {
                $scope.page=page;
                $scope.merchants = [];
                var where = "category_id="+$scope.category.id+"&page="+$scope.page;
                Merchants.getMerchants(where).then(function (data) {
                    $scope.merchants = data.merchants;

                },
                        function (data) {

                        });
            }
        }])