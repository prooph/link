App.ConnectorsCreateController = Em.ObjectController.extend({

});

App.ConnectorsCreateRoute = Em.Route.extend({
    model : function () {
        return App.Connector.create();
    }
});