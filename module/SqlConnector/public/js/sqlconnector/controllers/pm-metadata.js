PM.SqlconnectorMetadataController = Ember.ObjectController.extend({
    actions : {
        addFilter : function() {
            if (Em.isEmpty(this.get("metadata").get("filter"))) this.get("metadata").set("filter", PM.Object.create({}));

            var tempFilter = this.get("tempFilter").toHash();

            var filterKey = tempFilter.key;

            delete tempFilter.key;

            var filters = this.get("metadata").get("filter").toHash();

            //We allow the selection of a column for max two times to specify a between like filter
            //otherwise we will show an error
            if (typeof filters[filterKey + "_1"] !== "undefined") {
                this.set("isDuplicateColumn", true);
                return;
            } else {
                this.set("isDuplicateColumn", false);
            }

            if (typeof filters[filterKey] !== "undefined") {
                var oneFilter = filters[filterKey];

                if (oneFilter.operand === "=" || oneFilter.operand === "like" || oneFilter.operand === "ilike"
                    || tempFilter.operand === "=" || tempFilter.operand === "like" || tempFilter.operand === "ilike") {
                    this.set("isDuplicateColumn", true);
                    return;
                } else {
                    this.set("isDuplicateColumn", false);
                }

                delete filters[filterKey];
                filters[filterKey + "_1"] = oneFilter;
                filterKey = filterKey + "_2";
            }

            filters[filterKey] = tempFilter;

            this.get("metadata").set("filter", Em.hashToObject(filters, PM.Object));

            this.set("tempFilter", PM.Object.create({}));

            $("#first-filter-input").focus();
        },
        removeFilter : function(filter) {
            var filterObj = this.get("metadata").get("filter").toHash();

            delete filterObj[filter.key];

            this.get("metadata").set("filter", Em.hashToObject(filterObj, PM.Object));
        },
        addOrderBy : function () {
            if (Em.isEmpty(this.get("tempOrderByProp"))) return;
            if (Em.isEmpty(this.get("tempOrderByOrder"))) return;

            var orderBy = this.get("metadata").getWithDefault("order_by", "");

            if (! Em.isEmpty(orderBy)) orderBy = orderBy + ", ";

            orderBy = orderBy + this.get("tempOrderByProp") + " " + this.get("tempOrderByOrder");

            this.get("metadata").set("order_by", orderBy);

            this.set("tempOrderByProp", null);
            this.set("tempOrderByOrder", null);
        },
        removeOrderBy : function (order) {
            var orderByStr = this.get("orderByArr").filter(function(orderBy) {
                return orderBy != order;
            }).join(", ");

            this.get("metadata").set("order_by", orderByStr);
        }
    },
    init : function () {
        this._super();

        if (Ember.isEmpty(this.get("metadata"))) {
            var connector = this.get("parentController").get("connectors")[this.get("parentController.selectedConnector")];
            if (Ember.isEmpty(connector.metadata)) {
                this.set("model.metadata", PM.Object.create({}));
            } else {
                this.set("model.metadata", Em.hashToObject(connector.metadata, PM.Object));
            }
        }

        var filter = this.get("metadata").get("filter");

        //Workaround because empty filter translates to array instead of object
        if ($.isArray(filter)) {
            this.get("metadata").set("filter", PM.Object.create());
        }

        this.set("tempFilter", PM.Object.create({}));
    },

    tempFilter : null,
    isDuplicateColumn : false,
    tempOrderByProp : null,
    tempOrderByOrder : null,

    isSingleResultType : function () {
        var dataTypeObj = PM.DataTypes.findBy("value", this.get("parentController.selectedDataType"));

        if (Ember.isEmpty(dataTypeObj)) return false;

        return dataTypeObj.native_type !== "collection";
    }.property("parentController.selectedDataType"),

    metadataFilterList : function() {
        var filters = this.get("metadata").get("filter") || PM.Object.create({});

        var filterList = [];

        for (key in filters.toHash()) {
            filterList.push(this.__toFormFilter( key, filters.get(key)));
        }

        return filterList;
    }.property('metadata.filter'),

    availableDataTypeProperties : function () {
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

    availableOperands : ["=", "<", "<=", ">", ">=", "like", "ilike"],

    __toFormFilter : function (filterKey, filterValue) {
        //FilterValue is already an object so we only need to add the filterKey
        if (filterValue instanceof PM.Object) {
            var filter = filterValue.toHash();
            filter['key'] = filterKey;
            return filter;
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
    }.property("tempFilter.column"),

    isIdentifier : function () {
        var dataTypeObj = PM.DataTypes.findBy("value", this.get("parentController.selectedDataType"));

        if (Ember.isEmpty(dataTypeObj) || dataTypeObj.native_type !== "collection") {
            var isIdentifier = this.get("metadata").get("identifier") || false;

            if (isIdentifier) this.get("metadata").set("filter", PM.Object.create({}));

            return isIdentifier;
        }

        if (dataTypeObj.native_type === "collection") {
            this.get("metadata").set("identifier", false);
        }

        return false;
    }.property('parentController.selectedDataType', 'metadata.identifier'),

    availableOrderByProperties : function () {
        var dataTypeProperties = this.get("availableDataTypeProperties");

        var orderByProps = this.get("orderByArr").map(function (order) {
            return order.split(" ")[0];
        });

        return dataTypeProperties.filter(function (prop) {
            return ! orderByProps.contains(prop);
        })
    }.property("availableDataTypeProperties", "metadata.order_by"),

    orderByArr : function () {
        var orderBy =  this.get("metadata").get("order_by");

        if (Em.isEmpty(orderBy)) return [];

        return orderBy.split(",").map(function(order) {
            return $.trim(order);
        });
    }.property("metadata.order_by"),

    orderOptions : ["ASC", "DESC"]
});
