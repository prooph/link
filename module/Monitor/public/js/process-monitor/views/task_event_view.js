App.TaskEventView = Ember.View.extend({
    activeLogMessageDetailsToggle : function () {
        this.$(".log-message-details").hide().removeClass("hidden");
        this.$(".log-message-details-toggle").click(
            function (e) {
                e.preventDefault();

                var $target = $(e.target);

                $target.closest(".log-message-details-toggle")
                    .toggleClass("active")
                    .children(".glyphicon")
                    .toggleClass("glyphicon-arrow-down")
                    .toggleClass("glyphicon-arrow-up")
                    .closest(".event-container")
                    .children(".log-message-details").slideToggle(500);
            }
        );
    }.on("didInsertElement")
});