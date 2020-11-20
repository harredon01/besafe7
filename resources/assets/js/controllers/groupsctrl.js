angular.module('besafe')

        .controller('GroupsSelectionCtrl',['$scope', 'Groups', '$rootScope', 'Users', function ($scope, Groups, $rootScope, Users) {
            $scope.data = {};
            $scope.invites = [];

            $scope.groups = [];
            $scope.contacts = [];
            $scope.chosen = [];
            $scope.searchText = "";
            $scope.objectSelected = false;
            $scope.editGroup = false;
            $scope.isActive = false;
            $scope.save = function (isvalid) {
                $scope.submitted = true;
                console.log("Saving: " + isvalid);
                if (isvalid) {
                    $scope.saveGroup();
                }
            }
            $scope.groupToggle = function () {
                if ($scope.editGroup) {
                    $scope.editGroup = false;
                } else {
                    $scope.editGroup = true;
                    if ($scope.contacts.length == 0) {
                        $scope.getContacts();
                    }
                }
            }
            $scope.activation = function () {
                if ($scope.isActive) {
                    $scope.isActive = false;
                } else {
                    $scope.isActive = true;
                }
            }

            $scope.saveGroup = function () {
                console.log("Data sent");
                console.log(JSON.stringify($scope.data));
                console.log(JSON.stringify($.param($scope.data)));
                var formData = {name: $scope.data.name, is_public: true, contacts: $scope.invites, completeData: $scope.chosen};
                Groups.saveGroup($.param(formData)).then(function (data) {
                    console.log("Return group");
                    console.log(JSON.stringify(data));
                    if (data.status == "success") {
                        $scope.selectGroup(data.group);
                        $scope.groups.push(data.group);
                    }
                    $scope.data = {};
                    $scope.submitted = false;
                },
                        function (data) {

                        });
            }
            $scope.getGroups = function () {

                Groups.getGroups().then(function (data) {

                    var groups = data.data;
                    for (item in groups) {
                        var group = groups[item];
                        group.is_selected = false;
                        $scope.groups.push(group);
                    }
                    if (groups.length == 0) {
                        $scope.editGroup = true;
                    }
                },
                        function (data) {

                        });
            }
            $scope.getContacts = function () {

                Users.getContacts($scope.searchText).then(function (data) {
                    $scope.contacts = [];
                    $scope.contacts = data.data;
                },
                        function (data) {

                        });
            }
            $scope.selectContact = function (contact) {
                $scope.invites.push(contact.contact_id);
                $scope.chosen.push(contact);
                for(item in $scope.contacts){
                    if($scope.contacts[item].contact_id == contact.contact_id){
                        $scope.contacts.splice(item, 1);
                    }
                }
            }
            $scope.deleteContact = function (contact) {
                for(item in $scope.chosen){
                    if($scope.chosen[item].contact_id == contact.contact_id){
                        $scope.chosen.splice(item, 1);
                    }
                }
                for(item in $scope.invites){
                    if($scope.invites[item] == contact.contact_id){
                        $scope.chosen.splice(item, 1);
                    }
                }
            }

            $scope.selectGroup = function (group) {
                group.is_selected = true
                $scope.isActive = false;
                $rootScope.$broadcast('ObjectSelected', {object_id: group.id});
            }
            $rootScope.$on('PlanSelected', function (event, args) {
                $scope.isActive = true;
                if ($scope.groups.length == 0) {
                    $scope.getGroups();
                }
            });

            $scope.clean = function () {
                $scope.data = {};
                $scope.invites = [];
            }

        }])
