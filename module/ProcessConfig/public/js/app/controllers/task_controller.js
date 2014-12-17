ProcessManager.TaskController = Ember.ObjectController.extend({
    isCollectDataTask : function() {
        return this.get("model").task_type === "collect_data";
    }.property('model.task_type'),
    isProcessDataTask : function () {
        return this.get("model").task_type === "process_data";
    }.property('model.task_type')
});