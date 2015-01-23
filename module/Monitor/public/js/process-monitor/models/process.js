App.Process = DS.Model.extend({
    name : DS.attr("string"),
    process_type : DS.attr("string"),
    start_message : DS.attr(),
    tasks : DS.attr(),
    events : DS.attr(),
    started_at : DS.attr("date"),
    finished_at : DS.attr("date"),
    status : DS.attr("string")
});

App.ProcessSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash, prop) {
        if (type === App.Process) {
            hash.tasks = Em.hashToObject(hash.tasks, App.Object);
            hash.events = Em.hashToObject(hash.events, App.Object);
        }
        return this._super(type, hash, prop);
    }
});
