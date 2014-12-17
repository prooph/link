Ember.Handlebars.helper('task-desc', function(task) {
    if (task.task_type === "collect_data") {

        var source = (typeof task.source === "undefined")? "source" : Handlebars.Utils.escapeExpression(task.source);
        var sourceType = (typeof task.ginger_type === "undefined")? "data" : Handlebars.Utils.escapeExpression(task.ginger_type);
        return new Ember.Handlebars.SafeString('Collect ' + sourceType + ' from ' + source);
    }

    if (task.task_type === "process_data") {
        var target = (typeof task.target === "undefined")? "target" : Handlebars.Utils.escapeExpression(task.target);
        var preferredType = 'data';

        if (typeof task.preferred_type != "undefined") {
            preferredType = task.preferred_type;
        } else if (typeof  task.allowed_types != "undefined") {
            preferredType = task.allowed_types[0];
        }

        preferredType = Handlebars.Utils.escapeExpression(preferredType);

        return new Ember.Handlebars.SafeString('Process ' + preferredType + ' with ' + target);
    }

    return Handlebars.Utils.escapeExpression(task.task_type);
}, "task_type", "source", "target", "ginger_type", "allowed_types", "preferred_type");