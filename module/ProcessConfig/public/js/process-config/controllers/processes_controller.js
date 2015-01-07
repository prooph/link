App.ProcessesController = Ember.ArrayController.extend({
});

App.ProcessesRoute = Ember.Route.extend({
    model: function () {
        return this.store.all('process');
    }
});

App.ProcessesIndexRoute = Ember.Route.extend({
    model : function () {
        return this.modelFor('processes');
    }
});