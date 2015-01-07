App.ConnectorsCreateController = Em.ObjectController.extend({
    init : function () {
        this._super();

        this.__generateName();
    },


    isDataTypeSelected : Ember.computed.notEmpty("data_type"),
    isNotEmptyName     : Ember.computed.notEmpty("name"),
    isFileTypeSelected : Ember.computed.notEmpty("metadata.file_type"),

    isDuplicateName : function () {
        if (Em.isEmpty(this.get("name"))) return false;


    },
    availableDataTypes : function () {
        return App.DataTypes;
    }.property(),

    nameWithDefault : function(key, value, previousValue) {
        if (arguments.length > 1) {
            this.set("name", value);
            return;
        }

        if (this.get("isGenerateName") && ! Em.isEmpty(this.get("dataType"))) {
            return this.__generateName();
        }

        return this.get("name");
    }.property("dataType", "isGenerateName"),

    isGenerateName : function () {
        return Em.isEmpty(this.get("name")) || this.get("name") === this.get("__lastGeneratedName");
    }.property("dataType"),
    __lastGeneratedName : null,
    __generateName : function () {
        this.set("lastGeneratedName", this.get("dataType") + " File");

        return this.get("lastGeneratedName");
    }
});

App.ConnectorsCreateRoute = Em.Route.extend({
    model : function () {
        return App.Object.create({
            data_type : null,
            name : null,
            metadata : App.Object.create({
                file_type : null
            }),
            writable : false,
            readable : false
        });
    }
});
