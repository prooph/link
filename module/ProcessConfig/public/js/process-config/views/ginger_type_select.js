App.GingerTypeSelect = Ember.Select.extend({
    focusIn : function () {
        this.get("controller").send("setGingerTypeSelectFocused");
    },
    focusOut : function () {
        this.get("controller").send("unsetGingerTypeSelectFocused");
    }
});

Ember.Handlebars.helper("data-type-select", App.GingerTypeSelect);

