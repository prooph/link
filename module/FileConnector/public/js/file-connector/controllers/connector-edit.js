App.ConnectorEditController = Em.ObjectController.extend({
    init : function () {
        this._super();

        this.__generateName();
    },

    actions : {
        toggleReadable : function () {
            this.set("readable", ! this.get("readable"));
        },
        toggleWritable : function () {
            this.set("writable", ! this.get("writable"));
        },
        createConnector : function () {
            var connector = this.store.createRecord("connector", this.getProperties("name", "data_type", "metadata", "writable", "readable"));
            var self = this;
            connector.save().then(function () {
                self.transitionToRoute("connectors.index");
            }).catch($.failNotify);
        }
    },

    isNew : false,
    isDataTypeSelected : Ember.computed.notEmpty("data_type"),
    isFileTypeSelected : Ember.computed.notEmpty("metadata.file_type"),

    isNotValid : function () {
        if (! this.get("isValidName")) return true;
        if (! this.get("isDataTypeSelected")) return true;
        if (! this.get("isFileTypeSelected")) return true;
        if (! this.get("writable") && ! this.get("readable")) return true;

        return false;
    }.property("isValidName", "isDataTypeSelected", "isFileTypeSelected", "writable", "readable"),

    isValidName        : function () {
        if (Em.isEmpty(this.get("name"))) return false;

        return ! this.get("isDuplicateName");
    }.property("name"),

    isDuplicateName : function () {
        if (Em.isEmpty(this.get("name"))) return false;

        if (hash_find_by(App.SystemConnectors, "name", this.get("name"))) return true;

        if (this.get("store").all("connector").findBy("name", this.get("name"))) return true;

        return false;
    }.property("name"),

    availableDataTypes : function () {
        return App.DataTypes;
    }.property(),

    availableFileTypes : function () {
        return App.FileTypes;
    }.property(),

    nameWithDefault : function(key, value, previousValue) {
        if (arguments.length > 1) {
            this.set("name", value);
            return;
        }

        if (this.get("isGenerateName") && ! Em.isEmpty(this.get("data_type"))) {
            this.set("name", this.__generateName());
        }

        return this.get("name");
    }.property("data_type", "isGenerateName"),

    isGenerateName : function () {
        return Em.isEmpty(this.get("name")) || this.get("name") === this.get("__lastGeneratedName");
    }.property("data_type", "metadata.file_type"),
    __lastGeneratedName : null,
    __generateName : function () {

        if (Em.isEmpty(this.get("data_type"))) return null;

        var name = App.dataTypeName(this.get("data_type"));

        if (! Em.isEmpty(this.get("metadata.file_type"))) {
            name = name + " " + this.get("metadata.file_type").toUpperCase();
        }

        name = name + " File";

        this.set("__lastGeneratedName", name);

        return name;
    }
});

App.ConnectorEditRoute = Em.Route.extend({
    setupController : function (controller, model) {
        controller.setProperties({isNew : false, model : model});
    }
});
