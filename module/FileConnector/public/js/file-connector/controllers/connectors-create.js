App.ConnectorsCreateRoute = Em.Route.extend({
    setupController : function (controller, model) {
        this.controllerFor("connector.edit").setProperties({isNew : true, model : model});
    },
    model : function () {
        return App.Object.create({
            ginger_type : null,
            name : null,
            metadata : App.Object.create({
                file_type : null
            }),
            writable : false,
            readable : false
        });
    },
    renderTemplate : function () {
        this.render("connector/edit");
    }
});