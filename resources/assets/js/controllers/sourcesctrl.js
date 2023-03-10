angular.module('besafe')

        .controller('SourcesCtrl',['$scope', 'Billing', '$rootScope', function ($scope, Billing, $rootScope) {
            $scope.data = {};
            $scope.months = [];
            $scope.years = [];
            $scope.branches = [
                {"name": "Visa", "value": "VISA"},
                {"name": "Mastercard", "value": "MASTERCARD"},
                {"name": "Visa Debito", "value": "VISA_DEBIT"},
                {"name": "Diners", "value": "DINERS"},
                {"name": "American Express", "value": "AMEX"}
            ];
            $scope.sources = [];
            $scope.buying = false;
            $scope.editSource = false;
            $scope.showStripe = false;
            $scope.hideDefault = false;
            $scope.config = {};
            angular.element(document).ready(function () {
                $scope.localGateway = $scope.config.gateway;
            });
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                console.log("Saving: " + isvalid);
                if (isvalid) {
                    $scope.showStripe = true;
                }
            }
            $scope.edit = function () {
                if ($scope.editSource) {
                    $scope.editSource = false;
                } else {
                    $scope.editSource = true;
                }
            }
            // Create an instance of Elements
            var elements = stripe.elements({locale: $translate.use()});

            // Custom styling can be passed to options when creating an Element.
            // (Note that this demo uses a wider set of styles than the guide below.)
            var style = {
                base: {
                    color: '#32325d',
                    lineHeight: '24px',
                    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
                    fontSmoothing: 'antialiased',
                    fontSize: '16px',
                    '::placeholder': {
                        color: '#aab7c4'
                    }
                },
                invalid: {
                    color: '#fa755a',
                    iconColor: '#fa755a'
                }
            };

            // Create an instance of the card Element
            var card = elements.create('card', {style: style});

            // Add an instance of the card Element into the `card-element` <div>
            card.mount('#card-element');

            // Handle real-time validation errors from the card Element.
            card.addEventListener('change', function (event) {
                var displayError = document.getElementById('card-errors');
                if (event.error) {
                    displayError.textContent = event.error.message;
                } else {
                    displayError.textContent = '';
                }
            });

            // Handle form submission
            var form = document.getElementById('payment-form');
            var ownerInfo = {
                owner: {
                    name: $scope.data.name,
                    address: {
                        line1: $scope.data.address,
                        city: $scope.data.city,
                        postal_code: $scope.data.postal,
                        country: $scope.data.country,
                    },
                    email: $scope.data.email
                },
            };
            form.addEventListener('submit', function (event) {
                event.preventDefault();

                stripe.createSource(card, ownerInfo).then(function (result) {
                    if (result.error) {
                        // Inform the user if there was an error
                        var errorElement = document.getElementById('card-errors');
                        errorElement.textContent = result.error.message;
                    } else {
                        // Send the source to your server
                        stripeSourceHandler(result.source);
                    }
                });
            });

            $scope.stripeCreateToken = function () {
                
            }

            function stripeSourceHandler(token) {
                $scope.data.source = token;
                $scope.saveSource();
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
                        $scope.editSource = false;
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
                    if (sources.length == 0) {
                        $scope.editSource = true;
                        $scope.hidedefault = true;
                        $scope.data.default = true;
                    } else {
                        $scope.hidedefault = false;
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
            $scope.loadTestData = function (source) {

                $scope.data.cvc = "123";
                $scope.data.postal = "33132";
                $scope.data.expYear = "2020";
                $scope.data.expMonth = "12";

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
                    $scope.loadTestData();
                }
            });
            $scope.clean = function () {
                $scope.data = {};
            }
            $scope.setAsDefault = function (source) {
                var data = {};
                data.source = source.id
                Billing.setAsDefault(data, $rootScope.gateway).then(function (data) {
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


        }]).controller('SourcesPayuCtrl',['$scope', 'Billing', '$rootScope', function ($scope, Billing, $rootScope) {
    $scope.data = {};
    $scope.months = [];
    $scope.years = [];
    $scope.branches = [
        {"name": "Visa", "value": "VISA"},
        {"name": "Mastercard", "value": "MASTERCARD"},
        {"name": "Visa Debito", "value": "VISA_DEBIT"},
        {"name": "Diners", "value": "DINERS"},
        {"name": "American Express", "value": "AMEX"}
    ];
    $scope.sources = [];
    $scope.buying = false;
    $scope.showErrors = false;
    $scope.editSource = false;
    $scope.errors = [];
    $scope.config = {};
    angular.element(document).ready(function () {

        $scope.localGateway = "PayU";

    });
    $scope.save = function (isvalid) {
        $scope.submitted = true;
        $scope.showErrors = false;
        console.log("Saving: " + isvalid);
        if (isvalid) {
            $scope.saveSource();
        }
    }
    $scope.edit = function () {
        if ($scope.editSource) {
            $scope.editSource = false;
        } else {
            $scope.editSource = true;
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
        console.log("Data sent");
        console.log(JSON.stringify($scope.data));
        console.log(JSON.stringify($.param($scope.data)));
        Billing.createSource($.param($scope.data), $scope.localGateway).then(function (result) {
            console.log("Reply to create");
            console.log(JSON.stringify(result));
            if (result.status == "success") {
                $scope.getSources();
                $scope.data = {};
                $scope.data.country = "CO";
                $scope.submitted = false;
                $scope.editSource = false;
            } else {
                $scope.editSource = true;
                $scope.errors = result;
                $scope.showErrors = true;
            }

        },
                function (data) {

                });
    }
    $scope.getSources = function () {
        Billing.getSources($scope.localGateway).then(function (data) {
            console.log("Sources");
            $scope.sources = [];
            var sources = data.sources;
            console.log(JSON.stringify(sources));
            for (item in sources) {
                var card = sources[item];
                card.type = "card";
                $scope.sources.push(card);
            }
            if (sources.length == 0) {
                $scope.editSource = true;
            }
        },
                function (data) {

                });
    }
    $scope.deleteSource = function (source) {
        Billing.deleteSource(source.token, $scope.localGateway).then(function (data) {
            for (item in $scope.sources) {
                if ($scope.sources[item].token == source.token) {
                    console.log("deleting source", $scope.sources[item]);
                    $scope.sources.splice(item, 1);
                    if ($scope.sources.length == 0) {
                        $scope.editSource = true;
                    }
                }
            }
        },
                function (data) {
                });
    }
    $scope.loadTestData = function () {
        $scope.data.name = "Hoovert Arredondo";
        $scope.data.line1 = "Calle 73 # 0-24";
        $scope.data.line2 = "Apto 202";
        $scope.data.line3 = "";
        $scope.data.postalCode = "";
        $scope.data.city = "Bogota";
        $scope.data.state = "Cundinamarca";
        $scope.data.country = "CO";
        $scope.data.phone = "3105507245";
        $scope.data.document = "1020716535";
        $scope.data.number = "4111111111111111";
        $scope.data.expMonth = "10";
        $scope.data.expYear = "2020";
        $scope.data.branch = "VISA";

    }
    $scope.selectSource = function (source) {
        $rootScope.$broadcast('SourceSelected', {source: source.id});
    }
    $rootScope.$on('GetSources', function (event, args) {
        $scope.getSources();
        $scope.buying = true;
    });
    $rootScope.$on('GatewaySelected', function (event, args) {
        if ($rootScope.gateway == $scope.localGateway) {
            $scope.getSources();
            $scope.loadTestData();
        }
    });
    $scope.clean = function () {
        $scope.data = {};
    }
    $scope.setAsDefault = function (source) {
        var data = {};
        data.source = source.token
        Billing.setAsDefault(data, $scope.localGateway).then(function (data) {
            for (item in $scope.sources) {
                $scope.sources[item].is_default = false;
                if ($scope.sources[item].token == source.token) {
                    $scope.sources[item].is_default = true;
                }
            }
        },
                function (data) {
                    window.location.reload();
                });
    }


}]).controller('SubscriptionsCtrl',['$scope', 'Billing', function ($scope, Billing) {
    $scope.data = {};
    $scope.subscriptions = [];
    $scope.plans = [];
    $scope.showEdit = false;
    $scope.localGateway = "";
    angular.element(document).ready(function () {
        $scope.getSubscriptions();
    });
    $scope.save = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            var data = {plan_id: $scope.data.plan_id};
            Billing.editSubscription(data, $scope.data.subscription_id, $scope.localGateway).then(function (data) {
                $scope.data.subscription_id = "" + $scope.data.subscription_id;
                for (item in $scope.subscriptions) {
                    if ($scope.subscriptions[item].source_id == $scope.data.subscription_id) {
                        console.log("deleting subscription", $scope.subscriptions[item]);
                        $scope.subscriptions.splice(item, 1);

                    }
                }
                $scope.subscriptions.push(data.subscription);
            },
                    function (data) {
                    });
        }
    }
    $scope.getSubscriptions = function () {
        Billing.getSubscriptions().then(function (data) {
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
    $scope.editSubscription = function (subscription) {
        $scope.data.subscription_id = subscription.id;
        $scope.getPlans();
        $scope.showEdit = true;
    }

}]).controller('PlansCtrl',['$scope', '$rootScope', function ($scope, $rootScope) {

    $scope.selectPlan = function (plan) {
        $rootScope.$broadcast('PlanSelected', {plan: plan});
    }
}])
        .controller('GatewaysCtrl', function ($scope, $rootScope) {

            $scope.data = {};
            $scope.objectSelected = false;


            $scope.selectGateway = function (gateway) {
                $rootScope.gateway = gateway;
                $rootScope.$broadcast('GatewaySelected');
            }
            $rootScope.$on('ObjectSelected', function (event, args) {
                $scope.objectSelected = true;
            });
        })
        .controller('SubscriptionPlansStripeCtrl', function ($scope, Billing, $rootScope) {
            $scope.data = {};
            $scope.months = [];
            $scope.years = [];
            $scope.sources = [];
            $scope.localGateway = "";
            $scope.sourceSelected = false;
            $scope.showSources = false;
            $scope.buying = true;
            $scope.data.object_id = 1;

            angular.element(document).ready(function () {
                $scope.localGateway = "Stripe";
            });

            $scope.saveSimple = function (isvalid) {
                $scope.submitted = true;
                if (isvalid) {
                    $scope.createSubscription();
                }
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
            }
            $scope.editSources = function () {
                window.location.href = "/sources";
            }
            $scope.loadTestData = function () {
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
                Billing.createSubscription($.param($scope.data), $scope.localGateway).then(function (data) {
                    if ("status" in data) {
                        if (data.status == 'success') {
                            console.log("Subscription created");
                            $scope.data = {};
                            $scope.submitted = false;
                            window.location.href = "/subscriptions";
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
            $rootScope.$on('GatewaySelected', function (event, args) {
                if ($rootScope.gateway == $scope.localGateway) {
                    console.log(JSON.stringify($scope.config));
                    var sources = $scope.config.sources;
                    console.log($scope.localGateway);
                    for (item in sources) {
                        console.log(JSON.stringify(sources[item]));
                        if (sources[item].gateway == $scope.localGateway) {
                            if (sources[item].has_default) {
                                $scope.sourceSelected = true;
                                console.log("Has default");
                            }
                        }
                    }
                    $scope.loadMonths();
                    $scope.loadTestData();
                }
            });
        })
        .controller('SubscriptionPlansPayuCtrl',['$scope', 'Billing', '$rootScope', function ($scope, Billing, $rootScope) {
    $scope.data = {};
    $scope.months = [];
    $scope.years = [];
    $scope.branches = [
        {"name": "Visa", "value": "VISA"},
        {"name": "Mastercard", "value": "MASTERCARD"},
        {"name": "Visa Debito", "value": "VISA_DEBIT"},
        {"name": "Diners", "value": "DINERS"},
        {"name": "American Express", "value": "AMEX"}
    ];
    $scope.sources = [];
    $scope.localGateway = "";
    $scope.sourceSelected = false;
    $scope.showSources = false;
    $scope.buying = true;
    $scope.hasDefault = false;
    $scope.newCard = false;
    $scope.data.object_id = 1;

    angular.element(document).ready(function () {
        $scope.localGateway = "PayU";
    });

    $scope.saveSimple = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            $scope.createSubscription();
        }
    }
    $scope.selectSource = function (source) {
        $scope.data.source = source.token;
        $scope.submitted = true;
        if ($scope.data.plan_id && $scope.data.object_id) {
            $scope.sourceSelected = true;
            $scope.createSubscription();
        } 
    }
    $scope.newCardTrigger = function () {
        $scope.newCard = true;
        $scope.sourceSelected = false;
    }
    $scope.getSources = function () {
        $scope.sourceSelected = false;
        $scope.newCard = false;
        Billing.getSources($scope.localGateway).then(function (data) {
            console.log("Sources");
            $scope.sources = [];
            var sources = data.sources;
            console.log(JSON.stringify(sources));
            for (item in sources) {
                var card = sources[item];
                card.type = "card";
                $scope.sources.push(card);
            }
            if (sources.length == 0) {
                $scope.newCard = true;
            }
        },
                function (data) {

                });
    }

    $scope.save = function (isvalid) {
        $scope.submitted = true;
        if (isvalid) {
            $scope.data.new = true;
            $scope.createSubscription();
        }
    }

    $scope.loadMonths = function () {
        var date = new Date();
        for (i = 0; i < 15; i++) {
            $scope.years.push(date.getFullYear() + i);
        }
        for (i = 0; i < 12; i++) {
            $scope.months.push( 1 + i);
        }
        console.log(JSON.stringify($scope.months));
    }
    $scope.loadTestData = function () {
        console.log("Loading test data");
        $scope.data.name = "Hoovert Arredondo";
        $scope.data.line1 = "Calle 73 # 0-24";
        $scope.data.line2 = "Apto 202";
        $scope.data.line3 = "";
        $scope.data.postalCode = "";
        $scope.data.city = "Bogota";
        $scope.data.state = "Cundinamarca";
        $scope.data.country = "CO";
        $scope.data.phone = "3105507245";
        $scope.data.document = "1020716535";
        $scope.data.number = "4111111111111111";
        $scope.data.expMonth = "10";
        $scope.data.expYear = "2020";
        $scope.data.branch = "VISA";
    }
    $scope.createSubscription = function () {
        console.log("Data sent");
        console.log(JSON.stringify($scope.data));
        console.log(JSON.stringify($.param($scope.data)));
        Billing.createSubscription($.param($scope.data), $scope.localGateway).then(function (data) {
            if ("status" in data) {
                if (data.status == 'success') {
                    console.log("Subscription created");
                    $scope.data = {};
                    window.location.href = "/subscriptions";
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
    $rootScope.$on('PlanSelected', function (event, args) {
        $scope.data.plan_id = args.plan;
    });
    $rootScope.$on('ObjectSelected', function (event, args) {
        $scope.data.object_id = args.object_id;
    });
    $rootScope.$on('GatewaySelected', function (event, args) {
        console.log($rootScope.gateway);
        console.log($scope.localGateway);
        console.log($rootScope.gateway == $scope.localGateway);
        if ($rootScope.gateway == $scope.localGateway) {
            console.log(JSON.stringify($scope.config));
            var sources = $scope.config.sources;
            console.log($scope.localGateway);
            for (item in sources) {
                console.log(JSON.stringify(sources[item]));
                if (sources[item].gateway == $scope.localGateway) {
                    if (sources[item].has_default) {
                        $scope.sourceSelected = true;
                        $scope.hasDefault = true;
                        console.log("Has default");
                    } else {
                        $scope.sourceSelected = false;
                        $scope.hasDefault = false;
                        $scope.getSources();
                    }
                } else {
                    $scope.sourceSelected = false;
                    $scope.hasDefault = false;
                    $scope.getSources();
                }

            }
            $scope.loadMonths();
            $scope.loadTestData();
        }
    });
}])
