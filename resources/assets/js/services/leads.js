angular.module('besafe')
        .service('Leads',['$q', '$http', function ($q, $http) {

            var sendLead = function (data) {
                let url = "/api/leads";
                var def = $q.defer();
                $http({
                    method: 'post',
                    url: url,
                    data:data
                })
                        .then(function (data) {
                                def.resolve(data.data);
                            },function(response) {
                            def.reject("Failed to sendLead");
                        });

                return def.promise;
                /**/

            }

            return {
                sendLead:sendLead,
            };
        }])