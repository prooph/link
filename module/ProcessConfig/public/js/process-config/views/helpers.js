Ember.Handlebars.helper('task-desc', function(task) {
    if (task.task_type === "collect_data") {

        var source = (typeof task.source === "undefined")? "source" : task.source;
        var sourceType = (typeof task.ginger_type === "undefined")? "data" : task.ginger_type;
        return Ember.I18n.t('task.collect_data', {sourceType : sourceType, source : source});
    }

    if (task.task_type === "process_data") {
        var target = (typeof task.target === "undefined")? "target" : task.target;
        var preferredType = 'data';

        if (typeof task.preferred_type != "undefined") {
            preferredType = task.preferred_type;
        } else if (typeof  task.allowed_types != "undefined") {
            preferredType = task.allowed_types[0];
        }

        return Ember.I18n.t('task.process_data', {preferredType : preferredType, target : target});
    }

    if (Ember.isEmpty(task.task_type)) {
        return Ember.I18n.t('task.new');
    }

    return Handlebars.Utils.escapeExpression(task.task_type);
}, "task_type", "source", "target", "ginger_type", "allowed_types", "preferred_type");