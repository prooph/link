ProcessManager.ManagerCreateController = Ember.ObjectController.extend({
    actions : {
        setDataTypeSelectFocused : function () {
            this.set("dataTypeSelectFocused", true);
        },
        unsetDataTypeSelectFocused : function () {
            this.set("dataTypeSelectFocused", false);
        },
        createProcess : function () {

            var process = this.store.createRecord('process', {
                "name" : this.get("processName"),
                "processType" : this.get("processType"),
                "startMessage" : {
                    messageType : this.get("messageType"),
                    dataType : this.get("dataType")
                },
                "tasks" : []
            });

            process.save().catch($.failNotify);
        }
    },
    processType : null,
    isNonProcessSelected : function () {
        return this.get("processType") == null;
    }.property("processType"),
    isAProcessSelected : function () {
        return this.get("processType") != null;
    }.property("processType"),
    isLinearProcess : function () {
        return this.get("processType") == "linear_messaging";
    }.property("processType"),
    isForeachProcess : function () {
        return this.get("processType") == "parallel_for_each";
    }.property("processType"),

    messageType : null,
    isNonMessageSelected : function () {
        return this.get("messageType") == null;
    }.property("messageType"),
    isAMessageTypeSelected : function () {
        return this.get("messageType") != null;
    }.property("messageType"),
    isCollectDataMessage : function () {
        return this.get("messageType") == "collect-data"
    }.property("messageType"),
    isDataCollectedMessage : function () {
        return this.get("messageType") == "data-collected"
    }.property("messageType"),

    dataType : null,
    isDataTypeSelected : function () {
        return this.get("dataType") != null;
    }.property("dataType"),

    dataTypeSelectFocused : false,
    isNotDataTypeSelectFocused : function () {
        return !this.get("dataTypeSelectFocused");
    }.property("dataTypeSelectFocused"),

    isNotValid : function () {
        return this.get("isNonMessageSelected")
            || this.get("isNonProcessSelected")
            || ! this.get("isDataTypeSelected");
    }.property("isNonMessageSelected", "isNonProcessSelected", "isDataTypeSelected"),

    processName : function () {
        var name = "";

        if (this.get("processType") == "linear_messaging") name = name + "Linear ";
        if (this.get("processType") == "parallel_for_each") name = name + "Foreach ";
        if (this.get("messageType") == "collect-data") name = name + "Collect Data ";
        if (this.get("messageType") == "data-collected") name = name + "Process Data ";
        if (this.get("dataType") != null) name = name + this.get("dataType").split("\\").join(".");

        return name;
    }.property("processType", "messageType", "dataType")
});