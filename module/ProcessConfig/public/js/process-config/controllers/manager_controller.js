App.ManagerController = Ember.ArrayController.extend({
});

App.ManagerRoute = Ember.Route.extend({
    model: function () {
        return this.store.all('process');
    }
});

App.ManagerIndexRoute = Ember.Route.extend({
    model : function () {
        return this.modelFor('manager');
    }
});