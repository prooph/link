ProcessManager.SqlconnectorMetadataController = Ember.ObjectController.extend({
    actions : {
        addFilter : function() {
            if (Em.isEmpty(this.get("metadata").get("filter"))) this.get("metadata").set("filter", PM.Object.create({}));

            var filterKey = this.get("tempFilter").get("key");

            delete this.get("tempFilter").key;

            this.get("metadata").get("filter").set(filterKey, this.get("tempFilter"));

            this.set("tempFilter", PM.Object.create({}));
        },
        removeFilter : function(filter) {
            var filterObj = this.get("metadata").get("filter").serialize();

            delete filterObj[filter.key];

            this.get("metadata").set("filter", PM.Object.create(filterObj));
        }
    },
    init : function () {
        this._super();

        if (Ember.isEmpty(this.get("metadata"))) {
            var connector = this.get("parentController").get("connectors")[this.get("parentController.selectedConnector")];
            if (Ember.isEmpty(connector.metadata)) {
                this.set("metadata", PM.Object.create({}));
            } else {
                this.set("metadata", Em.hashToObject(connector.metadata, PM.Object));
            }
        }

        this.set("tempFilter", PM.Object.create({}));
    },

    tempFilter : null,

    isSingleResultType : function () {
        var dataTypeObj = PM.DataTypes.findBy("value", this.get("parentController.selectedDataType"));

        if (Ember.isEmpty(dataTypeObj)) return false;

        return dataTypeObj.native_type !== "collection";
    }.property("parentController.selectedDataType"),

    metadataFilterList : function() {
        var filters = this.get("metadata").get("filter") || PM.Object.create({});

        var filterList = [];

        for (key in filters.serialize()) {
            filterList.push(this.__toFormFilter( key, filters.get(key)));
        }

        return filterList;
    }.property('metadata.filter'),

    possibleDataTypeProperties : function () {
        var dataTypeObj = PM.DataTypes.findBy("value", this.get("parentController.selectedDataType"));

        if (Ember.isEmpty(dataTypeObj)) return [];

        if (dataTypeObj.native_type === "collection") dataTypeObj = dataTypeObj.properties.item;

        var properties = [];

        for (prop in dataTypeObj.properties) {
            properties.push(prop);
        }

        if (! properties.contains(this.get("tempFilter").get("column"))) this.get("tempFilter").set("column", null);

        return properties;
    }.property("parentController.selectedDataType"),

    possibleOperands : ["=", "<", "<=", ">", ">=", "like", "ilike"],

    __toFormFilter : function (filterKey, filterValue) {
        //FilterValue is already an object so we only need to add the filterKey
        if (filterValue instanceof PM.Object) {
            return filterValue.set("key", filterKey);
        }

        //Filter is a simple Key-Value-Pair which resolves to equal filter
        return {
            'key' : filterKey,
            'column' : filterKey,
            'operand' : "=",
            'value' : filterValue
        };
    },

    autoFilterKey : function () {
        var column = this.get("tempFilter.column");

        this.get("tempFilter").set("key", column);

        return column;
    }.property("tempFilter.column")
});
