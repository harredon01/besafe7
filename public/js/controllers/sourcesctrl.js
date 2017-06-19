angular.module('besafe')

        .controller('SourcesCtrl', function ($scope, LocationService, Billing, $rootScope) {
            $scope.data = {};
            $scope.months = [];
            $scope.years = [];
            $scope.sources = [];
            $scope.cityVisible = false;
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
                    $scope.data.plan_id = "Plan-2";
                    $scope.data.object_id = 1;
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
            $scope.getSources = function () {
                Billing.getSources($rootScope.gateway).then(function (data) {

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
            $rootScope.$on('GatewaySelected', function (event, args) {
                if ($rootScope.gateway == $scope.localGateway) {
                    $scope.getSources();
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
    $scope.subscriptions;
    $rootScope.gateway = "PayU";
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
        Billing.getSubscriptions($rootScope.gateway).then(function (data) {
            $scope.subscriptions = data.subscriptions;

        },
                function (data) {

                });
    }
    $scope.deleteSubscription = function (subscription_id) {
        Billing.deleteSubscription(subscription_id, $rootScope.gateway).then(function (data) {
            subscription_id = "" + subscription_id;
            for (item in $scope.subscriptions) {
                if ($scope.subscriptions[item].subscription_id == subscription_id) {
                    console.log("deleting subscription", $scope.subscriptions[item]);
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
    $scope.updateSubscription = function (subscription) {
        for (item in $scope.subscriptions) {
            if ($scope.subscriptions[item].subscription_id == subscription.subscription_id) {
                $scope.subscriptions.splice(item, 1);
                $scope.subscriptions.push(subscription);
            }
        }
    }
    $scope.editSubscription = function (subscription_id) {
        $scope.data.subscription_id = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.subscription_id')).html();
        $scope.data.type = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.type')).html();
        $scope.data.firstName = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.firstName')).html();
        $scope.data.lastName = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.lastName')).html();
        $scope.data.subscription = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.subscription')).html();
        $scope.data.phone = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.phone')).html();
        $scope.data.postal = angular.element(document.querySelector('li#subscription-' + subscription_id + ' span.postal')).html();
    }

}).controller('PlansCtrl', function ($scope, Billing, $rootScope) {
    function stripeCreateToken() {
        Stripe.source.create({
            type: 'card',
            card: {
                number: $('.card-number').val(),
                cvc: $('.card-cvc').val(),
                exp_month: $('.card-expiry-month').val(),
                exp_year: $('.card-expiry-year').val(),
            },
            owner: {
                address: {
                    postal_code: $('.address_zip').val()
                }
            }
        }, stripeResponseHandler);
    }

    function stripeResponseHandler(status, response) {

        // Grab the form:
        var $form = $('#payment-form');

        if (response.error) { // Problem!

            // Show the errors on the form
            $form.find('.payment-errors').text(response.error.message);
            $form.find('button').prop('disabled', false); // Re-enable submission

        } else { // Source was created!

            // Get the source ID:
            var source = response.id;

            // Insert the source into the form so it gets submitted to the server:
            $form.append($('<input type="hidden" name="source" />').val(source));

            // Submit the form:
            $form.get(0).submit();

        }
    }
    $scope.data = {};
    $scope.subscriptions;
    $scope.cityVisible = false;
    $scope.save = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            var existing = false;
            if ($scope.data.subscription_id) {
                existing = true;
            }
            Billing.createSubscription($.param($scope.data), $rootScope.gateway).then(function (data) {
                if (existing) {
//                            $scope.updateSubscription(data.subscription);
                } else {
//                            $scope.subscriptions.push(data.subscription);
                }

                $scope.data = {};
                $scope.submitted = false;
            },
                    function (data) {

                    });
        }
    }
    $scope.saveSimple = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            var existing = false;
            if ($scope.data.subscription_id) {
                existing = true;
            }
            Billing.createSubscription($.param($scope.data), $rootScope.gateway).then(function (data) {
                if (existing) {
//                            $scope.updateSubscription(data.subscription);
                } else {
//                            $scope.subscriptions.push(data.subscription);
                }

                $scope.data = {};
                $scope.submitted = false;
            },
                    function (data) {

                    });
        }
    }

    $scope.clean = function () {
        $scope.data = {};
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
            $scope.hasDefault = false;

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
                            $scope.hasDefault = true;
                            console.log($scope.hasDefault);
                        }

                    }
                }

            });

            $scope.saveSimple = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    $scope.data.plan_id = "Plan-2";
                    $scope.data.object_id = 1;
                    $scope.data.default = true;
                    delete $scope.data['cvc'];
                    delete $scope.data['expMonth'];
                    delete $scope.data['expYear'];
                    delete $scope.data['number'];
                    delete $scope.data['postal'];
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
            }
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    $scope.stripeCreateToken();
                }
            }
            $scope.getSources = function (source) {
                Billing.getSources(source).then(function (data) {
                    $scope.sources = data.sources;

                },
                        function (data) {

                        });
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
                    $scope.data.plan_id = "Plan-2";
                    $scope.data.object_id = 1;
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
            }
        })
