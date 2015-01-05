ProcessManager.SqlconnectorMetadataView = Ember.View.extend({
    template : Ember.Handlebars.compile("Sqlconnector Metadata {{controller.data_type}}")
});

Ember.Logger.debug("DEBUG: SqlconnectorMetadataView loaded");
