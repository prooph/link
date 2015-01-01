ProcessManager.Process = DS.Model.extend({
    name : DS.attr("string"),
    processType : DS.attr("string"),
    startMessage : DS.attr(),
    tasks : DS.attr()
});

ProcessManager.ProcessSerializer = DS.RESTSerializer.extend({
    normalize : function(type, hash) {

        if (type === ProcessManager.Process) {
            var tasks = hash.tasks.map(function (task) {
                if (task instanceof ProcessManager.Process) return task;
                return ProcessManager.Object.create(task);
            });

            hash.tasks = tasks;
            //here we go
            this._super(type, hash);
        }
    }
});
