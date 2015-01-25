window.Ginger = {

    __AppPrototype : function (config) {

        if (!config) config = {};

        riot.observable(this);

        this.tags = {};
        this.currentRouteMatch = {};
        $.extend(this, config);

        var self = this,
            routeListener = function (collection, index, action) {

                self.currentRouteMatch = {
                    collection : collection,
                    index : index,
                    action : action
                };

                self.trigger("route", {app : self, routeMatch : self.currentRouteMatch});
            };

        riot.route(routeListener);

        this.bootstrap = function (rootTag) {
            self.tags[rootTag] = riot.mount(rootTag, {app: self})[0];
            self.trigger("didRenderTag", {app : self, routeMatch:self.currentRouteMatch, tagName : rootTag, tag: self.tags[rootTag]});
            return self;
        };

        this.renderInto = function (parentTag, into, tag, opts) {
            if (!opts) opts = {};

            $(parentTag.root).children(into).html($('<' + tag + ' />'));
            self.tags[tag] = riot.mount(tag, {app : self, routeMatch : self.currentRouteMatch, opts : opts})[0];
            self.trigger("didRenderTag", {app : self, routeMatch:self.currentRouteMatch, tagName : tag, tag: self.tags[tag]});
        }

        this.ready = function () {
            this.trigger("ready");
            riot.route.exec(routeListener);
        }
    },
    App : {
        create : function (config) {
            return new Ginger.__AppPrototype(config);
        }
    },
    namespace : function(name){
        var parts = name.split('.');
        var current = Ginger;
        for (var i in parts) {
            if (!current[parts[i]]) {
                current[parts[i]] = {};
            }

            current = current[parts[i]];
        }
    },
    Helpers : {
        merge_tag_elements_with_obj : function(tag, obj) {
            _.forIn(obj, function (value, key) {
                if (_.has(this, key)) {
                    this[key].value = value;
                }
            }, tag);
        },
        form_to_plain_obj : function (formEl) {
            return _.mapValues(
                _.indexBy($(formEl).serializeArray(), "name"),
                function (valObj) {
                    return valObj.value;
                }
            )
        }
    }
}