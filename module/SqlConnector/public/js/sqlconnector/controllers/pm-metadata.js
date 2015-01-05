ProcessManager.SqlconnectorMetadataController = Ember.ObjectController.extend({
    isSingleResultType : function () {
        var dataTypeObj = ProcessManager.DataTypes.findBy("value", this.get("parentController.selectedDataType"));

        if (Ember.isEmpty(dataTypeObj)) return false;

        return dataTypeObj.native_type !== "collection";
    }.property("parentController.selectedDataType")
});
