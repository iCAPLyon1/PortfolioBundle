'use strict';

widgetsApp
    .controller("widgetController", ["$scope", "widgetManager", "$attrs", "tinyMceConfig",
        function($scope, widgetManager, $attrs, tinyMceConfig) {
            $scope.widgetType = $attrs['widgetContainer'];
            $scope.tinyMceConfig = tinyMceConfig;

            $scope.create = function() {
                widgetManager.create($scope.widgetType);
            };

            $scope.edit = function(widget) {
                widgetManager.edit(widget);
            };

            $scope.delete = function(widget) {
                widgetManager.delete(widget);
            };

            $scope.cancelEdition = function(widget) {
                widgetManager.cancelEditing(widget, true);
            };

            $scope.save = function(widget) {
                return widgetManager.save(widget);
            };
        }
    ]);