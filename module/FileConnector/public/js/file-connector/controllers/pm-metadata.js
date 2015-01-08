PM.FileConnectorMetadataController = Em.ObjectController.extend({
    isImport            : function () {
        if (this.get("parentController.task_type") === "collect_data") {
            if (Em.isEmpty(this.get("metadata.location")) || this.get("metadata.location") == "outbox") {
                this.get("metadata").set("location", "inbox");
            }
            return true;
        }
        return false;
    }.property("parentController.task_type"),

    isExport            : function () {
        if (this.get("parentController.task_type") === "process_data") {
            if (Em.isEmpty(this.get("metadata.location")) || this.get("metadata.location") == "inbox") {
                this.get("metadata").set("location", "outbox");
            }
            return true;
        }
        return false;
    }.property("parentController.task_type"),

    availableLocations : function () {
        return PM.Locations;
    }.property()
});