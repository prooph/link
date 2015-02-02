App.TaskEventController = Ember.ObjectController.extend({
    isInfoEvent : function () {
        switch (this.get("model.name")) {
            case "ProcessWasSetUp":
            case "TaskEntryMarkedAsRunning":
            case "TaskEntryMarkedAsDone":
                return true;
            default:
                return false;
        }
    }.property(),
    isWarningEvent : function () {
        return false;
    }.property(),
    isErrorEvent : function () {
        switch (this.get("model.name")) {
            case "TaskEntryMarkedAsFailed":
                return true;
            case "LogMessageReceived":
                return this.__getLogMessageCode() >= 400;
            default:
                return false;
        }
    }.property(),
    isLogEvent : Ember.computed.equal("model.name", "LogMessageReceived"),
    description : function () {
        console.log(this.get("model"));
        switch (this.get("model.name")) {
            case "ProcessWasSetUp":
                return Em.I18n.t('event.process_started');
                break;
            case "TaskEntryMarkedAsRunning":
                return Em.I18n.t('event.started_event');
            case "TaskEntryMarkedAsDone":
                return Em.I18n.t("event.done_event");
            case "TaskEntryMarkedAsFailed":
                return Em.I18n.t("event.failed_event");
            case "LogMessageReceived":
                return this.__logMessageDescription(this.__getLogMessageCode());
            default:
                return Em.I18n.t("event.unknown") + this.get("model.name");
        }
    }.property(),
    occurredOn : function () {
        var occurredOn = new Date(this.get("model.occurred_on"));

        return occurredOn.toLocaleString();
    }.property(),
    logMessageDetails : function () {
        switch (this.__getLogMessageCode()) {
            case 500:
                return this.__getSystemErrorDetails();
            default:
                return this.__getTechnicalLogMsg();
        }
    }.property(),
    __logMessageDescription : function (logMessageCode) {
        switch (logMessageCode) {
            case 500:
                return Em.I18n.t("event.log_message.system_error");
            default:
                return Em.I18n.t("event.log_message.unknown") + logMessageCode;
        }
    },
    __getLogMessageCode : function () {
        return this.get("model.payload.message.payload.msgCode");
    },
    __getSystemErrorDetails : function () {
        var payload = this.get("model.payload.message.payload");

        return new Ember.Handlebars.SafeString(
            '<p><strong>Error:</strong></p>' +
            '<p>' + payload.technicalMsg + '</p>' +
            '<p><strong>TRACE: </strong></p>' +
            '<pre>' + payload.msgParams.trace + '</pre>'
        );
    },
    __getTechnicalLogMsg : function () {
        return this.get("model.payload.message.payload.technicalMsg");
    }
});