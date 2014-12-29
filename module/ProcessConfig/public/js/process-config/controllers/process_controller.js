ProcessManager.ProcessRoute = Ember.Route.extend({
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

ProcessManager.ProcessEditRoute = Ember.Route.extend({
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