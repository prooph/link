ProcessManager.DataTypeSelect = Ember.Select.extend({
    focusIn : function () {
        this.get("controller").send("setDataTypeSelectFocused");
    },
    focusOut : function () {
        this.get("controller").send("unsetDataTypeSelectFocused");
    }
});

Ember.Handlebars.helper("data-type-select", ProcessManager.DataTypeSelect);

