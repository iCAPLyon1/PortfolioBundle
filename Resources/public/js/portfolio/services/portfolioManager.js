'use strict';

portfolioApp
    .factory('portfolioManager', ["portfolioFactory", "widgetsManager", "commentsManager", function(portfolioFactory, widgetsManager, commentsManager){
        return {
            portfolio:   null,
            portfolioId: null,
            originalTitle: null,

            getPortfolio: function(portfolioId) {
                this.portfolioId = portfolioId;
                this.portfolio   = portfolioFactory.get({portfolioId: this.portfolioId}, function(portfolio) {
                    widgetsManager.init(portfolio.portfolioWidgets);
                    commentsManager.init(portfolioId, portfolio.comments);
                });

                return this.portfolio;
            },
            save: function(portfolio) {
                var self = this;
                var success = function() {
                    self.originalTitle = portfolio.title;
                    portfolio.isEditing = false;
                };
                var failed = function(error) {
                    console.error('Error occured while saving widget');
                    console.log(error);
                }

                return portfolio.$update(success, failed);
            },
            rename: function(portfolio) {
                portfolio.isEditing = true;
                this.originalTitle = portfolio.title;
            },
            cancelRename: function(portfolio) {
                portfolio.isEditing = false;
                portfolio.title = this.originalTitle;
            },
            updateViewCommentsDate: function () {
                this.portfolio.commentsViewAt = new Date();
                this.save(this.portfolio);
            }
        };
    }]);