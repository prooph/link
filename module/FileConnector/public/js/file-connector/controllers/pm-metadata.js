PM.FileConnectorMetadataController = Em.ObjectController.extend({
    isImport            : function () {
        console.log(this.get("parentController.task_type"))
        if (this.get("parentController.task_type") === "collect_data") return true;
        return false;
    }.property("parentController.task_type")
});