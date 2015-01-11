Ember.Handlebars.helper('task-desc', function(task) {
    var prefix = Em.I18n.t('task.task') + " " +task.id + ": ";
    var gingerTypeName = App.gingerTypeName;

    if (task.task_type === Em.I18n.t("task.collect_data.value")) {

        var source = (Em.isEmpty(task.source))? "source" : App.connectorName(task.source);
        var sourceType = (typeof task.ginger_type === "undefined")? "data" : gingerTypeName(task.ginger_type);
        return prefix + Em.I18n.t('task.collect_data.name', {sourceType : sourceType, source : source});
    }

    if (task.task_type === Em.I18n.t("task.process_data.value")) {
        var target = (Em.isEmpty(task.target))? "target" : App.connectorName(task.target);
        var preferredType = 'data';

        if (typeof task.preferred_type != "undefined") {
            preferredType = task.preferred_type;
        } else if (typeof  task.allowed_types != "undefined") {
            preferredType = task.allowed_types[0];
        }

        preferredType = gingerTypeName(preferredType);

        return  prefix + Em.I18n.t('task.process_data.name', {preferredType : preferredType, target : target});
    }

    if (task.task_type === Em.I18n.t("task.manipulate_payload.value")) {
        return prefix + Em.I18n.t("task.manipulate_payload.name", {manipulation_script : task.get("manipulation_script")})
    }

    if (Em.isEmpty(task.task_type)) {
        return prefix + Em.I18n.t('task.new');
    }

    return prefix + Handlebars.Utils.escapeExpression(task.task_type);
}, "task_type", "source", "target", "ginger_type", "allowed_types", "preferred_type", "manipulation_script");


App.gingerTypeName = function (gingerType) {
    return ginger_type_name(gingerType, App.GingerTypes);
};

App.connectorName = function (connectorId) {
    return connector_name(connectorId, App.Connectors);
}