ProcessManager.TaskController = Ember.ObjectController.extend({
    isCollectDataTask : function() {
        return this.get("model").task_type === "collect_data";
    }.property('model.task_type'),
    isProcessDataTask : function () {
        return this.get("model").task_type === "process_data";
    }.property('model.task_type'),
    taskTypes : [],
    connectors : []
});

ProcessManager.TaskRoute = Ember.Route.extend({
    model : function(params) {
        var process = this.modelFor('process');
        var task = process.get('tasks')[parseInt(params.task_id) - 1];

        if (Ember.isEmpty(task)) this.transitionTo('tasks.create')
        else return task;
    },
    setupController: function(controller, model) {
        controller.set('model', model);
        controller.taskTypes = ProcessManager.TaskTypes;
    },
    renderTemplate : function () {
        this.render(
            'task',
            {
                into : 'process',
                outlet: 'leftpanel'
            }
        )
    }
});

ProcessManager.TasksCreateRoute = Ember.Route.extend({
    activate : function () {
        var process = this.modelFor('process');

        var tasks = process.get('tasks');

        var newTask = {id : tasks.length + 1, task_type : ""};

        tasks.push(newTask);

        this.transitionTo('task', newTask);
    }
});

