ProcessManager.Serializable = Ember.Mixin.create({
    serialize: function ()
    {
        var result = {};
        for (var key in $.extend(true, {}, this))
        {
            // Skip these
            if (key === 'isInstance' ||
                key === 'isDestroyed' ||
                key === 'isDestroying' ||
                key === 'concatenatedProperties' ||
                typeof this[key] === 'function')
            {
                continue;
            }
            result[key] = this[key];
        }
        return result;
    }
});

ProcessManager.Object = Ember.Object.extend(ProcessManager.Serializable);


