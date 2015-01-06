App.Connector = DS.Model.extend({
    name : DS.attr("string"),
    dataType : DS.attr("string"),
    metadata : DS.attr()
});

App.ConnectorSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash) {

        if (type === App.Connector) {
            hash.metadata = Em.hashToObject(hash.metadata, App.Object);

            this._super(type, hash);
        }
    }
});
