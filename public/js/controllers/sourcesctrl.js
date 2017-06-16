angular.module('besafe')

        .controller('SourcesCtrl', function ($scope, LocationService, Billing, $rootScope) {
            $scope.data = {};
            $scope.months = [];
            $scope.years = [];
            $scope.sources;
            $rootScope.gateway = "PayU";
            $scope.cityVisible = false;
            angular.element(document).ready(function () {
                $scope.getSources();
                $scope.loadMonths();
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    var existing = false;
                    if ($scope.data.source_id) {
                        existing = true;
                    }
                    Billing.createSource($.param($scope.data), $rootScope.gateway).then(function (data) {
                        if (existing) {
                            $scope.updateSource(data.source);
                        } else {
                            $scope.sources.push(data.source);
                        }

                        $scope.data = {};
                        $scope.submitted = false;
                    },
                            function (data) {

                            });
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
            }
            $scope.getSources = function () {
                Billing.getSources($rootScope.gateway).then(function (data) {
                    $scope.sources = data.sources;

                },
                        function (data) {

                        });
            }
            $scope.deleteSource = function (source_id) {
                Billing.deleteSource(source_id, $rootScope.gateway).then(function (data) {
                    source_id = "" + source_id;
                    for (item in $scope.sources) {
                        if ($scope.sources[item].source_id == source_id) {
                            console.log("deleting source", $scope.sources[item]);
                            $scope.sources.splice(item, 1);
                        }
                    }
                },
                        function (data) {
                        });
            }
            $scope.clean = function () {
                $scope.data = {};
            }
            $scope.updateSource = function (source) {
                for (item in $scope.sources) {
                    if ($scope.sources[item].source_id == source.source_id) {
                        $scope.sources.splice(item, 1);
                        $scope.sources.push(source);
                    }
                }
            }
            $scope.editSource = function (source_id) {
                $scope.data.source_id = angular.element(document.querySelector('li#source-' + source_id + ' span.source_id')).html();
                $scope.data.type = angular.element(document.querySelector('li#source-' + source_id + ' span.type')).html();
                $scope.data.firstName = angular.element(document.querySelector('li#source-' + source_id + ' span.firstName')).html();
                $scope.data.lastName = angular.element(document.querySelector('li#source-' + source_id + ' span.lastName')).html();
                $scope.data.source = angular.element(document.querySelector('li#source-' + source_id + ' span.source')).html();
                $scope.data.phone = angular.element(document.querySelector('li#source-' + source_id + ' span.phone')).html();
                $scope.data.postal = angular.element(document.querySelector('li#source-' + source_id + ' span.postal')).html();
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
                $scope.localGateway= $scope.config.gateway;
                for(item in sources){
                    if(sources[item].gateway ==$scope.localGateway){
                        if(sources[item].source){
                            $scope.hasDefault = true;
                        } else {
                            $scope.getSources();
                        } 
                        
                    }
                }
                console.log(JSON.stringify($scope.config));
            });
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
                    $scope.data.plan_id = response.id;
                    $scope.data.object_id = response.id;
                    Billing.createSubscription($.param($scope.data), $rootScope.gateway).then(function (data) {
                        console.log("Subscription created");

                        $scope.data = {};
                        $scope.submitted = false;
                    },
                            function (data) {

                            });

                }
            }
        })