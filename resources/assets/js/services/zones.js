angular.module('besafe')
        
        .service('Zones',['$q', '$http', function ($q, $http) {

            var getZones = function (where) {
                let url = '/api/admin/zones' ;
                if(where){
                    url = url+'?'+where;
                }
                var def = $q.defer();
                $http({
                    method: 'get',
                    url: url
                })
                        .success(function (data) {
                            // console.log(data);
                            def.resolve(data);
                        })
                        .error(function () {
                            def.reject("Failed to get getArticles");
                        });
                return def.promise;
                /**/
            }

            var updateZoneItem = function (item) {
                console.log("Updating zone item",item);
                var def = $q.defer();
                $http({
                        method: 'PATCH',
                        url: '/api/admin/zones/'+item.id,
                        data: item, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to updateZoneItem");
                        });
                return def.promise;
                /**/
            }
            var createZoneItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'POST',
                        url: '/api/admin/zones',
                        data: item, // pass in data as strings
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to createZoneItem");
                        });
                return def.promise;
                /**/
            }
            var deleteZoneItem = function (item) {
                var def = $q.defer();
                $http({
                        method: 'DELETE',
                        url: '/api/admin/zones/'+item.id
                    })
                            .success(function (data) {
                                def.resolve(data);
                            })
                        .error(function () {
                            def.reject("Failed to deleteZoneItem");
                        });
                return def.promise;
                /**/
            }

            return {
                getZones:getZones,
                updateZoneItem:updateZoneItem,
                createZoneItem:createZoneItem,
                deleteZoneItem:deleteZoneItem,
            };
        }])