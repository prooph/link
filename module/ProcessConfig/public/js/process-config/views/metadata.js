App.MetadataView = Ember.ContainerView.extend({
    init : function () {
        this.set("currentView", this.getConnectorMetadataView());
        this.get('controller').on('uiMetadataKeyWillChange', this, this.triggerRefresh);
        return this._super();
    },
    getDefaultView : function () {
        return Ember.View.create({
            template : Ember.Handlebars.compile('<p class="alert alert-warning">{{t "metadata_not_available"}}</p>')
        })
    },
    getConnectorMetadataView : function () {
        if (Em.isEmpty(this.get("controller"))) return this.getDefaultView();

        var viewKey = this.get("controller").getCurrentUiMetadataKey() + "View",
            controllerKey = this.get("controller.uiMetadataKey") + "Controller",
            controller;

        if (Ember.isEmpty(App[viewKey])) {
            Ember.Logger.error("Connector Metadata View: ", viewKey, " is not defined");

            return this.getDefaultView();
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
    },
    refresh : function () {

        if (Em.isEmpty(this.get("controller"))) return;
        this.set("currentView", this.getConnectorMetadataView());
    },
    triggerRefresh : function () {
        Ember.run.next(this, function () {
            this.refresh();
        });
    },

    willClearRender: function()
    {
        this.get('controller').off('sourceDidChange', this, this.triggerRefresh);
    }
});