window.ProcessManager = Ember.Application.create({
    rootElement : '#app'
});

ProcessManager.ApplicationAdapter = DS.FixtureAdapter.extend();

//Route Config
ProcessManager.Router.map(function() {
    this.resource('manager', { path: '/' });
});

ProcessManager.ManagerRoute = Ember.Route.extend({
    model: function () {
        return this.store.find('process');
    }
});

ProcessManager.Process = DS.Model.extend({
    name : DS.attr("string")
});

ProcessManager.Process.FIXTURES = [
    {
        id: 1,
        name : "Testname"
    }
];

ProcessManager.ManagerController = Ember.ArrayController.extend({
    nameAddition : function () {
    }
});


