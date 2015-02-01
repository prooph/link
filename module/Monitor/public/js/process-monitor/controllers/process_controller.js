App.ProcessController = Ember.ObjectController.extend({
    started_at : function () {
        if (Em.isEmpty(this.get("model.started_at"))) {
            return "-";
        }

        return this.get("model.started_at").toLocaleString();
    }.property("model.started_at"),
    finished_at : function () {
        if (Em.isEmpty(this.get("model.finished_at"))) {
            return "-";
        }

        return this.get("model.finished_at").toLocaleString();
    }.property("model.finished_at"),
    configUrl : function () {
        return App.ProcessConfigUrl + "#processes/" + this.get("model.id");
    }.property("model.id")
});