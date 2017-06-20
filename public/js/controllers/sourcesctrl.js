angular.module('besafe')

        .controller('SourcesCtrl', function ($scope, LocationService, Billing, $rootScope) {
            $scope.data = {};
            $scope.months = [];
            $scope.years = [];
            $scope.sources = [];
            $scope.buying = false;
            $scope.editSource = false;
            $scope.config = {};
            angular.element(document).ready(function () {
                console.log(JSON.stringify($scope.config));
                var sources = $scope.config.sources;
                $scope.localGateway = $scope.config.gateway;
                console.log($scope.localGateway);


                for (item in sources) {
                    //if(sources[item].id = ;

                }

            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                console.log("Saving: " + isvalid);
                if (isvalid) {
                    $scope.stripeCreateToken();
                }
            }
            $scope.edit = function () {
                if ($scope.editSource) {
                    $scope.editSource = false;
                } else {
                    $scope.editSource = true;
                }
            }

            $scope.stripeCreateToken = function () {
                Stripe.source.create({
                    type: 'card',
                    card: {
                        number: $scope.data.number,
                        cvc: $scope.data.cvc,
                        exp_month: $scope.data.expMonth,
                        exp_year: $scope.data.expYear,
                    },
                    owner: {
                        address: {
                            postal_code: $scope.data.postal
                        }
                    }
                }, stripeResponseHandler);
            }

            function stripeResponseHandler(status, response) {


                if (response.error) { // Problem!

                    // Show the errors on the form
                    //$form.find('.payment-errors').text(response.error.message);
                    //$form.find('button').prop('disabled', false); // Re-enable submission

                } else { // Source was created!
                    // Get the source ID:
                    $scope.data.source = response.id;
                    $scope.saveSource();
                }
            }
            $scope.loadMonths = function () {
                console.log("Loading months");
                var date = new Date();
                for (i = 0; i < 15; i++) {
                    $scope.years.push(date.getFullYear() + i);
                }
                for (i = 0; i < 12; i++) {
                    $scope.months.push(1 + i);
                }
            }
            $scope.saveSource = function () {
                $scope.data.default = true;
                delete $scope.data['cvc'];
                delete $scope.data['expMonth'];
                delete $scope.data['expYear'];
                delete $scope.data['number'];
                delete $scope.data['postal'];
                console.log("Data sent");
                console.log(JSON.stringify($scope.data));
                console.log(JSON.stringify($.param($scope.data)));
                Billing.createSource($.param($scope.data), $rootScope.gateway).then(function (data) {
                    console.log("Return source");
                    console.log(JSON.stringify(data));
                    if (data.type == "card") {
                        var card = data.card;
                        card.type = "card";
                        $scope.sources.push(card);
                    }
                    $scope.data = {};
                    $scope.submitted = false;
                },
                        function (data) {

                        });
            }
            $scope.getSources = function (gateway) {
                Billing.getSources(gateway).then(function (data) {

                    var sources = data.sources;
                    for (item in sources) {
                        if (sources[item].type == "card") {
                            var card = sources[item].card;
                            card.type = "card";
                            card.id = sources[item].id;
                            card.is_default = sources[item].is_default;
                            $scope.sources.push(card);
                        }
                    }
                },
                        function (data) {

                        });
            }
            $scope.deleteSource = function (source) {
                Billing.deleteSource(source.id, $rootScope.gateway).then(function (data) {
                    for (item in $scope.sources) {
                        if ($scope.sources[item].id == source.id) {
                            console.log("deleting source", $scope.sources[item]);
                            $scope.sources.splice(item, 1);
                        }
                    }
                },
                        function (data) {
                        });
            }
            $scope.selectSource = function (source) {
                $rootScope.$broadcast('SourceSelected', {source: source.id});
            }
            $rootScope.$on('GetSources', function (event, args) {
                $scope.getSources(args.gateway);
                $scope.buying = true;
            });
            $rootScope.$on('GatewaySelected', function (event, args) {
                if ($rootScope.gateway == $scope.localGateway) {
                    $scope.getSources($scope.localGateway);
                    $scope.loadMonths();
                    $scope.data.cvc = "123";
                    $scope.data.postal = "33132";
                    $scope.data.expYear = "2020";
                    $scope.data.expMonth = "12";
                }
            });
            $scope.clean = function () {
                $scope.data = {};
            }
            $scope.setAsDefault = function (source) {
                var data = {};
                data.source = source.id
                Billing.setAsDefault($.param(data), $rootScope.gateway).then(function (data) {
                    for (item in $scope.sources) {
                        $scope.sources[item].is_default = false;
                        if ($scope.sources[item].id == source.id) {
                            $scope.sources[item].is_default = true;
                        }
                    }
                },
                        function (data) {
                            window.location.reload();
                        });
            }


}).controller('SubscriptionsCtrl', function ($scope, Billing, $rootScope) {
    $scope.data = {};
    $scope.subscriptions = [];
    $scope.plans = [];
    $scope.cityVisible = false;
    angular.element(document).ready(function () {
        $scope.getSubscriptions();
    });
    $scope.save = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            var existing = false;
            if ($scope.data.subscription_id) {
                existing = true;
            }
            Billing.createSubscription($.param($scope.data), $rootScope.gateway).then(function (data) {
                if (existing) {
                    $scope.updateSubscription(data.subscription);
                } else {
                    $scope.subscriptions.push(data.subscription);
                }

                $scope.data = {};
                $scope.submitted = false;
            },
                    function (data) {

                    });
        }
    }
    $scope.getSubscriptions = function () {
        Billing.getSubscriptions($scope.localGateway).then(function (data) {
            $scope.subscriptions = data.subscriptions;
            for (x in $scope.subscriptions) {
                var obj = $scope.subscriptions[x].object;
                $scope.subscriptions[x].object_name = obj.name;
                $scope.subscriptions[x].object_code = obj.code;
                $scope.subscriptions[x].object_status = obj.status;
                $scope.subscriptions[x].object_ends = obj.ends_at;
            }
        },
                function (data) {

                });
    }
    $scope.getPlans = function () {
        Billing.getPlans().then(function (data) {
            $scope.plans = data.plans;

        },
                function (data) {

                });
    }
    $scope.deleteSubscription = function (subscription) {
        Billing.deleteSubscription(subscription.source_id, subscription.gateway).then(function (data) {
            var subscription_id = "" + subscription.source_id;
            for (item in $scope.subscriptions) {
                if ($scope.subscriptions[item].source_id == subscription_id) {
                    $scope.subscriptions.splice(item, 1);
                }
            }
        },
                function (data) {
                });
    }
    $scope.clean = function () {
        $scope.data = {};
    }
    $scope.updateSubscription = function (subscription_id, plan_id) {
        var data = {plan_id: plan_id};
        Billing.editSubscription($.param(data), subscription_id, $rootScope.gateway).then(function (data) {
            subscription_id = "" + subscription_id;
            for (item in $scope.subscriptions) {
                if ($scope.subscriptions[item].subscription_id == subscription_id) {
                    console.log("deleting subscription", $scope.subscriptions[item]);
                    $scope.subscriptions.splice(item, 1);

                }
            }
            $scope.subscriptions.push(data.subscription);
        },
                function (data) {
                });
    }
    $scope.editSubscription = function () {
        $scope.getPlans();
    }

}).controller('PlansCtrl', function ($scope, Billing, $rootScope) {

    $scope.selectPlan = function (plan) {
        $rootScope.$broadcast('PlanSelected', {plan: plan});
    }
})
        .controller('GatewaysCtrl', function ($scope, Billing, $rootScope) {

            $scope.data = {};
            $scope.subscriptions;
            $scope.cityVisible = false;

            $scope.selectGateway = function (gateway) {
                $rootScope.gateway = gateway;
                $rootScope.$broadcast('GatewaySelected');
            }
        })
        .controller('CreateSourceStripeCtrl', function ($scope, Billing, $rootScope) {
            $scope.data = {};
            $scope.months = [];
            $scope.years = [];
            $scope.sources = [];
            $scope.localGateway = "";
            $scope.sourceSelected = false;
            $scope.showSources = false;
            $scope.buying = true;
            $scope.hasDefault = false;
            $scope.data.object_id = 1;

            angular.element(document).ready(function () {
                $scope.loadMonths();
                console.log(JSON.stringify($scope.config));
                var sources = $scope.config.sources;
                $scope.localGateway = $scope.config.gateway;

                console.log($scope.localGateway);
                for (item in sources) {
                    console.log(JSON.stringify(sources[item]));
                    if (sources[item].gateway == $scope.localGateway) {
                        if (sources[item].has_default) {
                            $scope.sourceSelected = true;
                            $scope.hasDefault = true;
                            console.log("Has default");
                        }
                    }
                }

            });

            $scope.saveSimple = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    $scope.createSubscription();
                }
            }
            $scope.selectSource = function (source) {
                var data = {};
                data.source = source.id
                Billing.setAsDefault($.param(data), $rootScope.gateway).then(function (data) {
                    for (item in $scope.sources) {
                        $scope.sources[item].is_default = false;
                        if ($scope.sources[item].id == source.id) {
                            $scope.sources[item].is_default = true;
                        }
                    }
                },
                        function (data) {
                            window.location.reload();
                        });
            }
            $scope.setAsDefault = function (source) {

            }
            $scope.useDefault = function () {
                delete $scope.data['source'];
                $scope.sourceSelected = true;
                document.getElementById('simple').click();
            }
            $scope.getSources = function () {
                $scope.sourceSelected = false;
                Billing.getSources($scope.localGateway).then(function (data) {

                    var sources = data.sources;
                    for (item in sources) {
                        if (sources[item].type == "card") {
                            var card = sources[item].card;
                            card.type = "card";
                            card.id = sources[item].id;
                            card.is_default = sources[item].is_default;
                            $scope.sources.push(card);
                        }
                    }
                    if ($scope.sources.length > 0) {
                        $scope.showSources = true;
                    } else {
                        $scope.showSources = false;
                    }

                },
                        function (data) {

                        });
            }
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    $scope.stripeCreateToken();
                }
            }
            $scope.loadMonths = function () {
                var date = new Date();
                for (i = 0; i < 15; i++) {
                    $scope.years.push(date.getFullYear() + i);
                }
                for (i = 0; i < 12; i++) {
                    $scope.months.push(1 + i);
                }
                $scope.data.number = "4242424242424242";
                $scope.data.cvc = "123";
                $scope.data.postal = "33132";
                $scope.data.expYear = "2020";
                $scope.data.expMonth = "12";
            }
            $scope.createSubscription = function () {

                $scope.data.default = true;
                delete $scope.data['cvc'];
                delete $scope.data['expMonth'];
                delete $scope.data['expYear'];
                delete $scope.data['number'];
                delete $scope.data['postal'];
                console.log("Data sent");
                console.log(JSON.stringify($scope.data));
                console.log(JSON.stringify($.param($scope.data)));
                Billing.createSubscription($.param($scope.data), $rootScope.gateway).then(function (data) {
                    if ("status" in data) {
                        if (data.status == 'active') {
                            console.log("Subscription created");
                            $scope.data = {};
                            $scope.submitted = false;
                            return true;
                        }
                    }
                    $scope.errors = data;
                    $scope.showErrors = true;
                },
                        function (data) {

                        });
            }

            $scope.stripeCreateToken = function () {
                Stripe.source.create({
                    type: 'card',
                    card: {
                        number: $scope.data.number,
                        cvc: $scope.data.cvc,
                        exp_month: $scope.data.expMonth,
                        exp_year: $scope.data.expYear,
                    },
                    owner: {
                        address: {
                            postal_code: $scope.data.postal
                        }
                    }
                }, stripeResponseHandler);
            }
            $rootScope.$on('PlanSelected', function (event, args) {
                $scope.data.plan_id = args.plan;
            });
            $rootScope.$on('ObjectSelected', function (event, args) {
                $scope.data.object_id = args.object_id;
            });

            function stripeResponseHandler(status, response) {


                if (response.error) { // Problem!

                    // Show the errors on the form
                    //$form.find('.payment-errors').text(response.error.message);
                    //$form.find('button').prop('disabled', false); // Re-enable submission

                } else { // Source was created!
                    // Get the source ID:
                    $scope.data.source = response.id;
                    $scope.createSubscription();

                }
            }
        })
