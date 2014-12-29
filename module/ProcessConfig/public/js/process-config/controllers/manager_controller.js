ProcessManager.ManagerController = Ember.ArrayController.extend({
});

ProcessManager.ManagerRoute = Ember.Route.extend({
    model: function () {
        return this.store.all('process');
    }
});

ProcessManager.ManagerIndexRoute = Ember.Route.extend({
    model : function () {
        return this.modelFor('manager');
    }
});