App.Connector = DS.Model.extend({
    name      : DS.attr("string"),
    data_type : DS.attr("string"),
    writable  : DS.attr("boolean"),
    readable  : DS.attr("boolean"),
    metadata  : DS.attr()
});

App.ConnectorSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash) {

        if (type === App.Connector) {
            hash.metadata = Em.hashToObject(hash.metadata, App.Object);

            this._super(type, hash);
        }
    }
});
