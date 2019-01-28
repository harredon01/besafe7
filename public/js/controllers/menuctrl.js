angular.module('besafe')

        .controller('MenuCtrl', function ($scope, Food) {
            $scope.data = {};
            $scope.articles;
            angular.element(document).ready(function () {
                $scope.getArticles();
            });

            $scope.getArticles = function () {
                Food.getArticles().then(function (data) {
                    $scope.articles = data.data;

                },
                        function (data) {

                        });
            }
            $scope.updateArticle = function (article) {

                Food.updateArticle(article).then(function (data) {

                },
                        function (data) {

                        });
            }

        })