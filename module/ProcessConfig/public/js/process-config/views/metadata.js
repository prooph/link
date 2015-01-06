App.MetadataView = Ember.View.extend({
    connectorMetadataView : function () {
        var viewKey = this.get("controller.uiMetadataKey") + "View",
            controllerKey = this.get("controller.uiMetadataKey") + "Controller",
            controller;

        if (Ember.isEmpty(App[viewKey])) {
            Ember.Logger.error("Connector Metadata View: ", viewKey, " is not defined");

            return Ember.View.create({
                template : Ember.Handlebars.compile('<p class="alert alert-warning">{{t "metadata_not_available"}}</p>')
            })
        }

        if (Ember.isEmpty(App[controllerKey])) {
            Ember.Logger.warn("No connector metadata controller found for: ", controllerKey, " Using TaskController instead.");

            controller = this.get("controller");
        } else {
            controller = App[controllerKey].create({
                model : this.get("controller.model"),
                parentController : this.get("controller")
            });
        }

        return App[viewKey].create({controller : controller});
    }.property("controller.uiMetadataKey"),
    template : Ember.Handlebars.compile("{{view view.connectorMetadataView}}")
});