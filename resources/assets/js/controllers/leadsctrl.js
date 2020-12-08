angular.module('besafe')

        .controller('LeadCtrl', ['$scope', 'Leads', 'Modals', function ($scope, Leads, Modals) {
                $scope.data = {};
                $scope.key = "";
                $scope.submitted = false;
                $scope.category;
                angular.element(document).ready(function () {
                    var res = viewData;
                    console.log("res", res);
                    let container = JSON.parse(res);
                    $scope.key = container.key;
                });

                $scope.send = function (isvalid) {
                    $scope.submitted = true;
                    if (isvalid) {
                        grecaptcha.ready(function () {
                            grecaptcha.execute($scope.key, {action: 'submit'}).then(function (token) {
                                $scope.data.captcha = token;
                                Modals.showLoader();
                                Leads.sendLead($scope.data).then(function (data) {
                                    Modals.hideLoader();
                                    $scope.data = {};
                                    Modals.showToast("Mensaje enviado",$("#contact-form"));
                                },
                                        function (data) {
                                        });
                            });
                        });
                    }
                }
                $scope.lead = function (isvalid,type) {
                    $scope.submitted = true;
                    if (isvalid) {
                        grecaptcha.ready(function () {
                            grecaptcha.execute($scope.key, {action: 'submit'}).then(function (token) {
                                $scope.data.captcha = token;
                                $scope.data.type = type;
                                Modals.showLoader();
                                Leads.sendLead($scope.data).then(function (data) {
                                    Modals.hideLoader();
                                    $scope.data = {};
                                    $scope.submitted = false;
                                    Modals.showToast("Mensaje enviado",$("#contact-form"));
                                },
                                        function (data) {
                                        });
                            });
                        });
                    }
                }
            }])
       