App.Connector = DS.Model.extend({
    name      : DS.attr("string"),
    processing_type : DS.attr("string"),
    writable  : DS.attr("boolean"),
    readable  : DS.attr("boolean"),
    metadata  : DS.attr()
});

App.ConnectorSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash, prop) {

        if (type === App.Connector) {
            hash.metadata = Em.hashToObject(hash.metadata, App.Object);
        }

        return this._super(type, hash, prop);
    }
});
