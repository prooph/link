ProcessManager.Process = DS.Model.extend({
    name : DS.attr("string"),
    processType : DS.attr("string"),
    startMessage : DS.attr(),
    tasks : DS.attr()
});

ProcessManager.ProcessSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash) {

        if (type === ProcessManager.Process) {
            hash.tasks = Em.hashToObject(hash.tasks, ProcessManager.Object);

            this._super(type, hash);
        }
    }
});
