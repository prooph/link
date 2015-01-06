App.ConnectorsIndexController = Em.ArrayController.extend({

});

App.ConnectorsIndexRoute = Em.Route.extend({
    model : function () {
        return this.store.all("connector");
    }
});