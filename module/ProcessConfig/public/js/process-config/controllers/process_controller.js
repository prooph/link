App.ProcessRoute = Ember.Route.extend({
    renderTemplate : function () {
        this.render('process');

        this.render(
            'process.tasks',
            {
                into: 'process',
                outlet: 'leftpanel'
            }
        );
    }
});

App.TasksIndexRoute = Ember.Route.extend({
    model : function () {
        return this.modelFor("process");
    },
    renderTemplate : function () {
        this.render(
            'process.tasks',
            {
                into: 'process',
                outlet: 'leftpanel'
            }
        );
    }
});

App.ProcessEditRoute = Ember.Route.extend({
    renderTemplate : function () {
        this.render(
            'process.edit',
            {
                into: 'process',
                outlet: 'leftpanel'
            }
        );
    }
});