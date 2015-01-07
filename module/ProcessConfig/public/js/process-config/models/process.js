App.Process = DS.Model.extend({
    name : DS.attr("string"),
    process_type : DS.attr("string"),
    start_message : DS.attr(),
    tasks : DS.attr()
});

App.ProcessSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash) {

        if (type === App.Process) {
            hash.tasks = Em.hashToObject(hash.tasks, App.Object);

            this._super(type, hash);
        }
    }
});
