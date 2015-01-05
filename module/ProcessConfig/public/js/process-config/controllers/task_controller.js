ProcessManager.TaskController = Ember.ObjectController.extend({
    actions : {
        saveTask : function () {
            var taskId = this.get("model.id"), m = this.get("model"), self = this;

            this.set("saveTask", true);

            if (Em.isEmpty(this.process.get("tasks")[taskId - 1])) {
                this.process.get("tasks").pushObject(m);
            }

            if (m.task_type != Em.I18n.t("task.collect_data.value")) {
                delete m["source"];
                delete m["data_type"];
            }

            if (m.task_type != Em.I18n.t("task.process_data.value")) {
                delete m["target"];
                delete m["allowed_types"];
                delete m["preferred_type"];
            } else {
                m.set("allowed_types", this.get("possibleDataTypes"));
            }

            if (m.task_type != Em.I18n.t("task.manipulate_payload.value")) {
                delete m["manipulation_script"];
            }

            this.process.save().then(function () {
                self.transitionToRoute('tasks.index');
            }).catch($.failNotify);
        }
    },

    taskTypes           : [],
    manipulationScripts : [],
    connectors          : {},
    process             : null,
    saveTask            : false,
    oldTask             : {},

    isCollectDataTask            : Ember.computed.equal("task_type", Em.I18n.t('task.collect_data.value')),
    isProcessDataTask            : Ember.computed.equal("task_type", Em.I18n.t('task.process_data.value')),
    isManipulatePayloadTask      : Ember.computed.equal("task_type", Em.I18n.t('task.manipulate_payload.value')),
    isATaskTypeSelected          : Ember.computed.notEmpty("task_type"),
    isASourceSelected            : Ember.computed.notEmpty("source"),
    isATargetSelected            : Ember.computed.notEmpty("model.target"),
    isManipulationScriptSelected : Ember.computed.notEmpty("manipulation_script"),

    isADataTypeSelected : function () {
        if (! Em.isEmpty(this.get("data_type"))) return true;
        if (! Em.isEmpty(this.get("preferred_type"))) return true;
        return false;
    }.property("data_type", "preferred_type"),

    isNotValid : function () {
        if (! this.get("isATaskTypeSelected")) return true;

        switch (this.get("task_type")) {
            case Em.I18n.t("task.collect_data.value"):
                if (! this.get("isASourceSelected")) return true;
                if (! this.get("isADataTypeSelected")) return true;
                break;
            case Em.I18n.t("task.process_data.value"):
                if (! this.get("isATargetSelected")) return true;
                if (! this.get("isADataTypeSelected")) return true;
                break;
            case Em.I18n.t("task.manipulate_payload.value"):
                if (! this.get("isManipulationScriptSelected")) return true;
                break;
        }

        return false;
    }.property("task_type", "source", "model.target", "data_type", "preferred_type", "manipulation_script"),

    possibleTaskTypes : function () {
        var process = this.get("process"),
            prevTaskId = this.get("model").id - 1,
            prevTask =  (prevTaskId == 0)? null : process.get("tasks")[prevTaskId - 1],
            taskTypes = [];

        if (Em.isEmpty(prevTask)) {
            taskTypes = this.__derivePossibleTaskTypesFromStartMessage(process.get("startMessage"));
        } else {
            taskTypes = this.__derivePossibleTaskTypesFromPrevTask(prevTask);
        }

        if (taskTypes.length == 1) {
            this.set("task_type", taskTypes[0]);
        }

        return taskTypes;
    }.property("process", "saveTask"),

    __derivePossibleTaskTypesFromPrevTask : function (prevTask) {
        var taskTypes = [];

        switch (prevTask.task_type) {
            case Em.I18n.t("task.collect_data.value"):
                taskTypes.push({
                    value : Em.I18n.t("task.process_data.value"),
                    label : Em.I18n.t("task.process_data.label")
                });
                break;
            default:
                taskTypes.push({
                    value : Em.I18n.t("task.collect_data.value"),
                    label : Em.I18n.t("task.collect_data.label")
                });
                taskTypes.push({
                    value : Em.I18n.t("task.process_data.value"),
                    label : Em.I18n.t("task.process_data.label")
                });
                break;
        }

        taskTypes.push({
            value : Em.I18n.t("task.manipulate_payload.value"),
            label : Em.I18n.t("task.manipulate_payload.label")
        });

        return taskTypes;
    },

    __derivePossibleTaskTypesFromStartMessage : function (startMessage) {
        var taskTypes = [];

        switch (startMessage.messageType) {
            case Em.I18n.t("message.collect_data.value"):
                taskTypes.push({
                    value : Em.I18n.t("task.collect_data.value"),
                    label : Em.I18n.t("task.collect_data.label")
                });
                break;
            case Em.I18n.t("message.data_collected.value"):
                taskTypes.push({
                    value : Em.I18n.t("task.process_data.value"),
                    label : Em.I18n.t("task.process_data.label")
                });
                break;
        }

        taskTypes.push({
            value : Em.I18n.t("task.manipulate_payload.value"),
            label : Em.I18n.t("task.manipulate_payload.label")
        });

        return taskTypes;
    },

    possibleSources : function () {
        if (this.get("model").task_type != Em.I18n.t('task.collect_data.value')) return [];

        var process = this.get("process"),
            prevTaskId = this.get("model").id - 1,
            sources = [];

        if (prevTaskId == 0) {
            sources = this.__derivePossibleSourcesFromStartMessage(process.get("startMessage"));
        } else {
            $.each(this.get("connectors"), function(name, connector) {
                if (Em.isEmpty(connector.allowed_messages)) return;

                if (connector.allowed_messages.contains(Em.I18n.t('message.collect_data.value'))) {
                    sources.push(name);
                }
            });

            return sources;
        }

        if (sources.length == 1) {
            this.set("source", sources[0]);
        }

        return sources;
    }.property('task_type'),

    __derivePossibleSourcesFromStartMessage : function (startMessage) {
        var sources = [];

        $.each(this.get("connectors"), function(name, connector) {
            if (Em.isEmpty(connector.allowed_messages)) return;

            if (connector.allowed_messages.contains(startMessage.messageType)) {
                sources.push(name);
            }
        });

        return sources;
    },

    possibleDataTypes : function () {
        var connectorName = null, dataTypes = [], setter = "";
        switch (this.get("task_type")) {
            case Em.I18n.t('task.collect_data.value'):
                connectorName = this.get("source");
                setter = "data_type";
                break;
            case Em.I18n.t('task.process_data.value'):
                connectorName = this.get("model.target");
                setter = "preferred_type";
                break;
        }

        if (Em.isEmpty(connectorName)) return dataTypes;

        if (!Em.isEmpty(this.get("connectors")[connectorName])) {
            dataTypes = this.get("connectors")[connectorName].allowed_types || [];
        }

        if (dataTypes.length == 1) this.set(setter, dataTypes[0]);
        var labeledDataTypes = dataTypes.map(function(dataType) {
            return ProcessManager.DataTypes.findBy('value', dataType);
        });

        return labeledDataTypes;
    }.property("task_type", "source", "model.target"),

    possibleTargets : function () {
        if (this.get("model").task_type != Em.I18n.t('task.process_data.value')) return [];

        var process = this.get("process"),
            prevTaskId = this.get("model").id - 1,
            targets = [];

        if (prevTaskId == 0) {
            targets = this.__derivePossibleTargetsFromStartMessage(process.get("startMessage"));
        } else {
            $.each(this.get("connectors"), function(name, connector) {
                if (Em.isEmpty(connector.allowed_messages)) return;

                if (connector.allowed_messages.contains(Em.I18n.t("message.process_data.value"))) {
                    targets.push(name);
                }
            });
        }

        if (targets.length == 1) this.set("model.target", targets[0]);

        return targets;
    }.property("task_type"),

    __derivePossibleTargetsFromStartMessage : function (startMessage) {
        if (startMessage.messageType != Em.I18n.t("message.data_collected.value")) return [];

        var targets = [];

        $.each(this.get("connectors"), function(name, connector) {
            if (Em.isEmpty(connector.allowed_messages)) return;

            if (connector.allowed_messages.contains(Em.I18n.t("message.process_data.value"))) {
                targets.push(name);
            }
        });

        return targets;
    },
    uiMetadataKey : function () {
        switch (this.get("task_type")) {
            case Em.I18n.t('task.collect_data.value'):
                connectorName = this.get("source");
                break;
            case Em.I18n.t('task.process_data.value'):
                connectorName = this.get("model.target");
                break;
            default:
                return false;
        }

        var connector = this.get("connectors")[connectorName];

        if (Ember.isEmpty(connector)) return null;

        return connector.ui_metadata_key;
    }.property("source", "model.target"),
    showMetadata : Ember.computed.notEmpty("uiMetadataKey")
});

ProcessManager.TaskRoute = Ember.Route.extend({
    model : function(params) {
        var process = this.modelFor('process');
        var task = process.get('tasks').objectAt(parseInt(params.task_id) - 1);

        if (Ember.isEmpty(task)) this.transitionTo('tasks.create')
        else return task;
    },
    setupController: function(controller, model) {
        var oldTask = ProcessManager.Object.create(model.serialize());

        controller.set("taskTypes", ProcessManager.TaskTypes);
        controller.set("manipulationScripts", ProcessManager.ManipulationScrits);
        controller.set("connectors", ProcessManager.Connectors);
        controller.set("process", this.modelFor('process'));
        controller.set("oldTask", oldTask);
        controller.set("saveTask",false);

        this._super(controller, model)
    },
    renderTemplate : function () {
        this.render(
            'task',
            {
                into : 'process',
                outlet: 'leftpanel'
            }
        )
    },
    deactivate : function () {
        var con = this.controllerFor("task");

        if (! con.get("saveTask")) {
            var process = this.modelFor("process"), task = this.modelFor("task");

            if (! Em.isEmpty(process.get("tasks")[task.id - 1])) {
                process.get("tasks")[task.id - 1] = con.get("oldTask");
            }
        }
    }
});

ProcessManager.TasksCreateRoute = Ember.Route.extend({
    activate : function () {
        var process = this.modelFor('process'),
            tasks = process.get("tasks"),
            taskType = "";

        if (Em.isEmpty(tasks)) {

            switch (process.get("startMessage").messageType) {
                case Em.I18n.t('message.collect_data.value'):
                    taskType = Em.I18n.t('task.collect_data.value');
                    break;
                case Em.I18n.t('message.data_collected.value'):
                    taskType = Em.I18n.t('task.process_data.value');
                    break;
            }
        }

        var newTask = ProcessManager.Object.create({id : tasks.length + 1, task_type : taskType});

        this.transitionTo('task', newTask);
    }
});

ProcessManager.TaskDeleteRoute = Ember.Route.extend({
    afterModel : function (model) {
        var process = this.modelFor("process"), tasks = process.get("tasks"), self  = this;

        tasks.removeAt(model.id - 1);

        tasks.forEach(function(task, index) {
            task.set("id", index + 1);
        });

        return process.save().then(
            function () {
                self.transitionTo('tasks.index');
            },
            $.failNotify
        );
    }
});

