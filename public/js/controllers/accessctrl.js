angular.module('besafe')

        .controller('PersonalTokensCtrl', function ($scope, Passport, $rootScope) {
            $scope.data = {};
            $scope.data.scopes = [];
            $scope.personalAccessTokens;
            $scope.showForm = false;
            $scope.showToken = false;
            angular.element(document).ready(function () {
                $scope.getPersonalAccessTokens();
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    $scope.data.scopes = [""];
                    Passport.createPersonalAccessToken($.param($scope.data)).then(function (data) {
                        $scope.token = data.accessToken;
                        $scope.personalAccessTokens.push(data.token);
                        $rootScope.$broadcast('getTokens');
                        $scope.data = {};
                        $scope.data.scopes = [];
                        $scope.submitted = false;
                        $scope.showToken = true;
                        $scope.showForm = false;
                    },
                            function (data) {

                            });
                }
            }
            $scope.getPersonalAccessTokens = function () {
                Passport.getPersonalAccessTokens().then(function (data) {
                    $scope.personalAccessTokens = data;

                },
                        function (data) {

                        });
                Passport.getScopes().then(function (data) {
                    $scope.scopes = data;

                },
                        function (data) {

                        });
            }
            $scope.deletePersonalToken = function (id) {
                Passport.deletePersonalAccessTokens(id).then(function () {
                    for (item in $scope.personalAccessTokens) {
                        if ($scope.personalAccessTokens[item].id == id) {
                            $scope.personalAccessTokens.splice(item, 1);
                        }
                    }
                },
                        function (data) {
                        });
            }
            $scope.clean = function () {
                $scope.data = {};
            }
            $scope.newPersonalAccessToken = function () {
                $scope.showForm = true;
                $scope.showToken = false;
            }

        }).controller('OauthClientsCtrl', function ($scope, Passport, Users) {
    $scope.data = {};
    $scope.oauthClients;
    $scope.showForm = false;

    angular.element(document).ready(function () {
        $scope.getOauthClients();
    });
    $scope.save = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            if ($scope.data.id) {
                Passport.updateOauthClient($.param($scope.data), $scope.data.id).then(function (data) {
                    $scope.updateOauthClient(data);
                    $scope.showForm = false;

                    $scope.data = {};
                    $scope.submitted = false;
                },
                        function (data) {

                        });
            } else {
                Passport.createOauthClient($.param($scope.data)).then(function (data) {
                    $scope.oauthClients.push(data);
                    $scope.showForm = false;

                    $scope.data = {};
                    $scope.submitted = false;
                },
                        function (data) {

                        });
            }

        }
    }
    $scope.updateOauthClient = function (oauthClient) {
        for (item in $scope.oauthClients) {
            if ($scope.oauthClients[item].id == oauthClient.id) {
                $scope.oauthClients.splice(item, 1);
                $scope.oauthClients.push(oauthClient);
            }
        }
    }
    $scope.getOauthClients = function () {
        Passport.getOauthClients().then(function (data) {
            $scope.oauthClients = data;

        },
                function (data) {

                });
    }
    $scope.newOauthClient = function () {
        $scope.showForm = true;
    }
    $scope.editOauthClient = function (id) {
        $scope.showForm = true;
        $scope.data.id = angular.element(document.querySelector('tr#oauthClient-' + id + ' span.id')).html();
        $scope.data.name = angular.element(document.querySelector('tr#oauthClient-' + id + ' span.name')).html();
        $scope.data.redirect = angular.element(document.querySelector('tr#oauthClient-' + id + ' span.redirect')).html();
    }
    $scope.deleteOauthClient = function (id) {
        Passport.deleteOauthClient(id).then(function (data) {
            for (item in $scope.oauthClients) {
                if ($scope.oauthClients[item].id == id) {
                    console.log("deleting oauthClient", $scope.oauthClients[item]);
                    $scope.oauthClients.splice(item, 1);
                }
            }
        },
                function (data) {
                });
    }
    $scope.clean = function () {
        $scope.data = {};
    }

}).controller('TokensCtrl', function ($scope, Passport, $rootScope) {
            $scope.tokens;
            angular.element(document).ready(function () {
                $scope.getTokens();
            });
            $rootScope.$on('getTokens', function (event, args) {
                $scope.getTokens();
            });
            $scope.getTokens = function () {
                Passport.getTokens().then(function (data) {
                    $scope.tokens = data;

                },
                        function (data) {

                        });
            }
            $scope.deleteToken = function (id) {
                Passport.deleteTokens(id).then(function () {
                    for (item in $scope.tokens) {
                        if ($scope.tokens[item].id == id) {
                            $scope.tokens.splice(item, 1);
                        }
                    }
                },
                        function (data) {
                        });
            }

        })