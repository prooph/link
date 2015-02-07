window.Prooph = {};

Prooph.Link = {

    __AppPrototype : function (config) {

        if (!config) config = {};

        riot.observable(this);

        this.tags = {};
        this.routeMatch = {};
        $.extend(this, config);

        var self = this,
            routeListener = function (collection, index, action, childCollection, childIndex, childAction) {
                //All params are optional
                self.routeMatch = {
                    collection : collection,
                    index : index,
                    action : action,
                    childCollection : childCollection,
                    childIndex : childIndex,
                    childAction : childAction
                };

                self.trigger("route", {app : self, routeMatch : self.routeMatch});
            };

        riot.route(routeListener);

        this.bootstrap = function (rootTag) {
            self.tags[rootTag] = riot.mount(rootTag, {app: self})[0];
            self.trigger("didRenderTag", {app : self, routeMatch:self.routeMatch, tagName : rootTag, tag: self.tags[rootTag]});
            return self;
        };

        this.renderInto = function (parentTag, into, tag, opts) {
            if (!opts) opts = {};
            var node = $(parentTag.root).find(into).html($('<' + tag + ' />')).find(tag).get(0);

            if (node) {
                self.tags[tag] = riot.mountTo(node, tag, $.extend({app : self}, opts))[0];
                self.trigger("didRenderTag", {app : self, routeMatch:self.routeMatch, tagName : tag, tag: self.tags[tag]});
            }
        }

        this.ready = function () {
            this.trigger("ready");
            riot.route.exec(routeListener);
        }
    },
    App : {
        create : function (config) {
            return new Prooph.Link.__AppPrototype(config);
        }
    },
    namespace : function(name){
        var parts = name.split('.');
        var current = Prooph.Link;
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