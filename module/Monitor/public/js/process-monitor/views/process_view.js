App.ProcessView = Ember.View.extend({
    activeAccordion : function () {
        this.$(".task-details").hide().removeClass("hidden");
        this.$(".task-summary").click(function(e) {
            e.preventDefault();

            var $target = $(e.target)
                $task = $target.closest(".task");

            $task.children(".list-group-item").toggleClass("active");
            $task.children(".task-details").slideToggle(500);
        });
    }.on("didInsertElement")
});