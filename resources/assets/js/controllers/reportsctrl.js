angular.module('besafe')

        .controller('ReportsCtrl', ['$scope', 'Merchants', 'Modals', function ($scope, Merchants, Modals) {
                $scope.data = {};
                $scope.user = {};
                $scope.current = 1;
                $scope.last = 1;
                $scope.total = 1;
                $scope.per_page = 1;
                $scope.reports = [];
                $scope.categories = [];
                $scope.regionVisible = false;
                $scope.editMerchant = false;
                $scope.submitted = false;
                $scope.category;
                angular.element(document).ready(function () {
                    var res = viewData.replace(/App\\Models\\/g, "");
//                    res = res.replace(/"{/g, "'{");
//                    res = res.replace(/}"/g, "}'");
                    console.log("res", res);
                    let container = JSON.parse(res);

                    console.log("Data", container);
                    $scope.category = container.category;
                    $scope.reports = container.data;
                    $scope.current = container.page;
                    $scope.per_page = container.per_page;
                    $scope.last = container.last_page;
                    $scope.total = container.total;
                    document.getElementById("dissapear").remove();
                });

                $scope.goTo = function (page) {
                    $scope.current = page;
                    $scope.reports = [];
                    Modals.showLoader();
                    var where = "category_id=" + $scope.category.id + "&page=" + $scope.current + "&limit=" + 2;
                    if ($scope.category.type.includes("nearby")) {
                        Merchants.getReportsNearby(where).then(function (data) {
                            $scope.loadResults(data)
                        },
                                function (data) {
                                });
                    } else {
                        Merchants.getReports(where).then(function (data) {
                            $scope.loadResults(data)
                        },
                                function (data) {
                                });
                    }
                }

                $scope.loadResults = function (data) {
                    $scope.reports = data.data;
                    $scope.current = parseInt(data.page);
                    $scope.per_page = parseInt(data.per_page);
                    $scope.last = parseInt(data.last_page);
                    $scope.total = parseInt(data.total);
                    Modals.hideLoader();
                }
            }])
        .controller('ReportDetailCtrl', ['$scope', 'Merchants', 'Modals', function ($scope, Merchants, Modals) {
                $scope.report = {};

                angular.element(document).ready(function () {
                    var res = viewData.replace(/App\\Models\\/g, "");
//                    res = res.replace(/"{/g, "'{");
//                    res = res.replace(/}"/g, "}'");
                    console.log("res", res);
                    let container = JSON.parse(res);

                    console.log("Data", container);
                    $scope.report = container;
                    //document.getElementById("dissapear").remove();
                });

            }])