Em.hashToObject = function(hash, objectType) {
    if (hash instanceof Em.Object || typeof hash == "function" || (!$.isPlainObject(hash) && !$.isArray(hash))) return hash;

    for (key in hash) {
        hash[key] = Em.hashToObject(hash[key], objectType);
    }

    if (!$.isPlainObject(hash)) return hash;

    if (typeof objectType == "undefined") {
        objectType = Em.Object;
    }

    return objectType.create(hash);
}

Em.Serializable = Ember.Mixin.create({
    toHash: function ()
    {
        var result = {};
        for (var key in $.extend(true, {}, this))
        {
            // Skip these
            if (key === 'isInstance' ||
                key === 'isDestroyed' ||
                key === 'isDestroying' ||
                key === 'concatenatedProperties' ||
                typeof this[key] === 'function')
            {
                continue;
            }

            if (this[key] instanceof Em.Object && typeof Em.toHash === "function") result[key] = this.toHash.apply(this[key]);
            else result[key] = this[key];


        }
        return result;
    }
});

Ember.StatusIcon = function (status) {
    var statusIcon = "",
        statusTextClass = "";

    switch (status) {
        case "running":
            statusIcon = "glyphicon-flash";
            statusTextClass = "warning";
            break;
        case "succeed":
            statusIcon = "glyphicon-ok";
            statusTextClass = "success";
            break;
        case "failed":
            statusIcon = "glyphicon-remove";
            statusTextClass = "danger";
            break;
        default:
            statusIcon = "glyphicon-question-sign";
            statusTextClass = "danger";
            break;
    }

    return new Ember.Handlebars.SafeString('<span class="glyphicon ' + statusIcon + ' text-' + statusTextClass + '"></span>');
};

Ember.Handlebars.helper('process-status', function (process) {
    return Ember.StatusIcon(process.get("status"));
}, "status");

/**
 * This helper requires the methods:
 * - App.connectorName
 * - App.connectorIcon
 * - App.gingerTypeName
 *
 * and translations for:
 * - task.task
 * - task.collect_data.value
 * - task.process_data.value
 * - task.manipulate_payload.value
 * - task.new
 */
Ember.Handlebars.helper('task-desc', function(task) {
    var prefix = Em.I18n.t('task.task') + " " +task.id + ": ";
    var gingerTypeName = App.gingerTypeName;

    if (task.task_type === Em.I18n.t("task.collect_data.value")) {

        var source = (Em.isEmpty(task.source))? "source" : App.connectorName(task.source);
        var sourceType = (typeof task.ginger_type === "undefined")? "data" : gingerTypeName(task.ginger_type);

        source = Handlebars.Utils.escapeExpression(source);
        sourceType = Handlebars.Utils.escapeExpression(sourceType);

        return new Ember.Handlebars.SafeString(
            prefix +
                ' <span class="glyphicon ' + App.connectorIcon(task.source, 'glyphicon-import') + '" title="' + source + '"></span> ' +
                ' <span class="glyphicon glyphicon-arrow-right"></span>' +
                ' ' + sourceType);
    }

    if (task.task_type === Em.I18n.t("task.process_data.value")) {
        var target = (Em.isEmpty(task.target))? "target" : App.connectorName(task.target);
        var preferredType = 'data';

        if (typeof task.preferred_type != "undefined") {
            preferredType = task.preferred_type;
        } else if (typeof  task.allowed_types != "undefined") {
            preferredType = task.allowed_types[0];
        }

        target = Handlebars.Utils.escapeExpression(target);
        preferredType = Handlebars.Utils.escapeExpression(gingerTypeName(preferredType));

        return new Ember.Handlebars.SafeString(
            prefix +
                ' ' + preferredType +
                ' <span class="glyphicon glyphicon-arrow-right"></span>' +
                ' <span class="glyphicon ' + App.connectorIcon(task.target, 'glyphicon-export') + '" title="' + target + '"></span> ');
    }

    if (task.task_type === Em.I18n.t("task.manipulate_payload.value")) {
        return prefix +
            ' <span class="glyphicon glyphicon-play"></span>' +
            task.get("manipulation_script");
    }

    if (Em.isEmpty(task.task_type)) {
        return prefix + Em.I18n.t('task.new');
    }

    return new Ember.Handlebars.SafeString(prefix + Handlebars.Utils.escapeExpression(task.task_type));
}, "task_type", "source", "target", "ginger_type", "allowed_types", "preferred_type", "manipulation_script");

Ember.Handlebars.helper("task-status", function (task) {
    var status = null;

    task.get("events").forEach(function (event) {
        switch (event.name) {
            case "TaskEntryMarkedAsRunning":
                status = "running";
                break;
            case "TaskEntryMarkedAsDone":
                status = "succeed";
                break;
            case "TaskEntryMarkedAsFailed":
                status = "failed";
                break;
        }
    });

    return Ember.StatusIcon(status);
}, "events.@each");

