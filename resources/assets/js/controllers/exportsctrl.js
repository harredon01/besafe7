angular.module('besafe')

        .controller('ExportsCtrl',['$scope', 'Users', 'Merchants', function ($scope, Users, Merchants) {
            $scope.data = {};
            $scope.orders;
            $scope.from;
            $scope.to;
            $scope.user;
            $scope.activeMerchant;
            $scope.activeMerchantObject;
            $scope.changeMerchant = true;
            $scope.showSearch = false;
            $scope.datPickerBuilt = false;
            $scope.loadMore = true,
            $scope.page = 0;
            angular.element(document).ready(function () {
                
                Users.getUser().then(function (data) {
                    console.log("Result", data);
                    $scope.user = data.user;
                    if($scope.user.id < 4){
                        $scope.showSearch = true;
                    }
                    $scope.getMerchantsPrivate()
                    //$scope.merchants = data.data;
                },
                        function (data) {

                        });
                //$scope.getMerchantsPrivate();
            });
            $scope.getMerchants = function () {
                $scope.page++;
                Merchants.searchMerchants($scope.searchTerms).then(function (data) {
                    console.log("Items", data.data);
                    $scope.merchants = data.data;
                },
                        function (data) {

                        });
            }
            $scope.getMerchantsPrivate = function () {
                $scope.page++;
                let where = "owner_id="+$scope.user.id+"&page="+$scope.page
                Merchants.getMerchantsPrivate(where).then(function (data) {
                    console.log("Items", data.data);
                    $scope.merchants = data.data;
                },
                        function (data) {

                        });
            }
            $scope.changeActiveMerchant = function () {
                $scope.changeMerchant = true;
            }
            $scope.cancelChangeMerchant = function () {
                $scope.changeMerchant = false;
            }
            $scope.selectMerchant = function (item) {
                
                $scope.page = 0;
                $scope.items = [];
                $scope.activeMerchantObject = item;
                $scope.activeMerchant = item.id + "";
                $scope.changeMerchant = false;
                if(!$scope.datPickerBuilt){
                    setTimeout(function(){ $scope.buildDatePicker(); }, 800);
                    
                }
            }

            $scope.buildDatePicker = function () {
                $scope.datPickerBuilt = true;
                var dateFormat = "yy-mm-dd",
                        from = $("#from")
                        .datepicker({
                            defaultDate: "-1m",
                            changeMonth: true,
                            numberOfMonths: 2,
                            dateFormat: dateFormat
                        })
                        .on("change", function () {
                            to.datepicker("option", "minDate", $scope.getDate(this));
                            $scope.from = $scope.getDate(this);
                        }),
                        to = $("#to").datepicker({
                    defaultDate: "+1w",
                    changeMonth: true,
                    numberOfMonths: 2,
                    dateFormat: dateFormat
                })
                        .on("change", function () {
                            from.datepicker("option", "maxDate", $scope.getDate(this));
                            $scope.to = $scope.getDate(this);
                        });
            }
            $scope.getDate = function (element) {
                console.log("Get date", element.value);
                var date;
                try {
                    date = $.datepicker.parseDate("yy-mm-dd", element.value);
                } catch (error) {
                    date = null;
                }

                return date;
            }
            $scope.startExport = function (typeEx) {
                if(typeEx == 'orders'){
                    $scope.getStoreExport()
                } else {
                    $scope.getContentExport(typeEx)
                }
            }


            $scope.getStoreExport = function () {
                console.log("Date", $scope.from, $scope.to);
                let container = {"from":$scope.from,"to":$scope.to,"merchant_id":$scope.activeMerchant,"type":'products'}
                Merchants.getStoreExport(container).then(function (data) {

                },
                        function (data) {

                        });
            }
            $scope.getContentExport = function (typeCont) {
                let container = {"merchant_id":$scope.activeMerchant,"type":typeCont}
                Merchants.getStoreContent(container).then(function (data) {

                },
                        function (data) {

                        });
            }

        }])