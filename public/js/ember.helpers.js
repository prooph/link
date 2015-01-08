Em.hashToObject = function(hash, objectType) {
    if (hash instanceof Em.Object || typeof hash == "function" || (!$.isPlainObject(hash) && !$.isArray(hash))) return hash;

    for (key in hash) {
        hash[key] = Em.hashToObject(hash[key], objectType);
    }

    if (!$.isPlainObject(hash)) return hash;

    if (typeof objectType == "undefined") {
        objectType = Em.Object;
    }

    return objectType.create(hash);
}

Em.Serializable = Ember.Mixin.create({
    toHash: function ()
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

            if (this[key] instanceof Em.Object && typeof Em.toHash === "function") result[key] = this.toHash.apply(this[key]);
            else result[key] = this[key];


        }
        return result;
    }
});

