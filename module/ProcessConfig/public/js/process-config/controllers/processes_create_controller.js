App.ProcessesCreateController = Ember.ObjectController.extend({
    actions : {
        setGingerTypeSelectFocused : function () {
            this.set("gingerTypeSelectFocused", true);
        },
        unsetGingerTypeSelectFocused : function () {
            this.set("gingerTypeSelectFocused", false);
        },
        createProcess : function () {

            var process = this.store.createRecord('process', {
                "name" : this.get("processName"),
                "process_type" : this.get("process_type"),
                "start_message" : {
                    message_type : this.get("message_type"),
                    ginger_type : this.get("ginger_type")
                },
                "tasks" : []
            });
            var con = this;

            process.save().then(function () {
                con.transitionToRoute('processes');
            }).catch($.failNotify);
        }
    },

    processTypes : [],
    messageTypes : [],
    gingerTypes : [],

    isNonProcessSelected    : Ember.computed.empty("process_type"),
    isAProcessSelected      : Ember.computed.notEmpty("process_type"),
    isLinearProcess         : Ember.computed.equal("process_type", Em.I18n.t('process.linear.value')),
    isForeachProcess        : Ember.computed.equal("process_type", Em.I18n.t('process.foreach.value')),

    isNonMessageSelected    : Ember.computed.empty("message_type"),
    isAMessageTypeSelected  : Ember.computed.notEmpty("message_type"),
    isCollectDataMessage    : Ember.computed.equal("message_type", Em.I18n.t('message.collect_data.value')),
    isDataCollectedMessage  : Ember.computed.equal("message_type", Em.I18n.t('message.data_collected.value')),

    isGingerTypeSelected      : Ember.computed.notEmpty("ginger_type"),

    gingerTypeSelectFocused : false,
    isNotGingerTypeSelectFocused : function () {
        return !this.get("gingerTypeSelectFocused");
    }.property("gingerTypeSelectFocused"),

    isNotValid : function () {
        return this.get("isNonMessageSelected")
            || this.get("isNonProcessSelected")
            || ! this.get("isGingerTypeSelected");
    }.property("isNonMessageSelected", "isNonProcessSelected", "isGingerTypeSelected"),

    processName : function () {
        var name = "";

        if (this.get("process_type") == Em.I18n.t('process.linear.value')) name = name + "Linear ";
        if (this.get("process_type") == Em.I18n.t('process.foreach.value')) name = name + "Foreach ";
        if (this.get("message_type") == Em.I18n.t('message.collect_data.value')) name = name + "Collect ";
        if (this.get("message_type") == Em.I18n.t('message.data_collected.value')) name = name + "Process ";
        if (this.get("ginger_type") != null) name = name + App.gingerTypeName(this.get("ginger_type")) + " ";

        return name;
    }.property("process_type", "message_type", "ginger_type")
});

App.ProcessesCreateRoute = Ember.Route.extend({
    setupController : function(controller, model) {
        controller.setProperties({
            processTypes : [
                {
                    value : Em.I18n.t('process.linear.value'),
                    label : Em.I18n.t('process.linear.label')
                },
                {
                    value : Em.I18n.t('process.foreach.value'),
                    label : Em.I18n.t('process.foreach.label')
                }
            ],
            messageTypes : [
                {
                    value : Em.I18n.t('message.collect_data.value'),
                    label : Em.I18n.t('message.collect_data.label')
                },
                {
                    value : Em.I18n.t('message.data_collected.value'),
                    label : Em.I18n.t('message.data_collected.label')
                }
            ],
            gingerTypes : App.GingerTypes,
            model : model
        });
    },
    model : function () {
        return App.Object.create({
            process_type : null,
            message_type : null,
            ginger_type    : null
        });
    }
});