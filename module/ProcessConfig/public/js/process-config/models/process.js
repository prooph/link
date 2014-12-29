ProcessManager.Process = DS.Model.extend({
    name : DS.attr("string"),
    processType : DS.attr("string"),
    startMessage : DS.attr(),
    tasks : DS.attr()
});
