// from https://github.com/jasonaden/angular-hal/blob/master/src/Parser.js
(function() {
    var __bind = function(fn, me){ return function(){ return fn.apply(me, arguments); }; };

    (function(ng, mod) {
        var Parser, removeNamespace;
        removeNamespace = function(name, ns) {
            ns = ns ? ns + ':' : '';
            if (name.substr(0, ns.length) === ns) {
                return name.substr(ns.length);
            } else {
                return name;
            }
        };
        Parser = (function() {
            var Link, Links, Resource;

            function Parser(ns) {
                this.ns = ns;
                this.parse = __bind(this.parse, this);
            }

            Parser.prototype.parse = function(hal) {
                var json, _embedded, _links;
                json = angular.copy(hal);
                _links = json._links, _embedded = json._embedded;
                delete json._links;
                delete json._embedded;
                return new Resource(json, _links, _embedded, this.ns);
            };

            Resource = (function() {
                function Resource(data, links, embedded, ns) {
                    var em, name, prop, resourceLinks;
                    ns = ns ? ns : '';
                    angular.extend(this, data);
                    resourceLinks = links ? new Links(links) : {};
                    for (name in embedded) {
                        prop = embedded[name];
                        this[removeNamespace(name, ns)] = (function() {
                            var _i, _len, _results;
                            if (ng.isArray(prop)) {
                                _results = [];
                                for (_i = 0, _len = prop.length; _i < _len; _i++) {
                                    em = prop[_i];
                                    _results.push(new Parser(ns).parse(em, ns));
                                }
                                return _results;
                            } else {
                                return new Parser(ns).parse(prop, ns);
                            }
                        })();
                    }
                    this.links = function(name) {
                        var key;
                        if (name == null) {
                            name = '';
                        }
                        key = name === 'self' ? name : resourceLinks[name] ? name : ns + ':' + name;
                        if (resourceLinks[key]) {
                            return resourceLinks[key];
                        } else {
                            return resourceLinks;
                        }
                    };
                }

                return Resource;

            })();

            Links = (function() {
                function Links(links, ns) {
                    var link, lk, name;
                    if (!(links != null ? links.self : void 0)) {
                        throw 'Self link is required';
                    }
                    for (name in links) {
                        link = links[name];
                        this[name] = (function() {
                            var _i, _len, _results;
                            if (ng.isArray(link)) {
                                _results = [];
                                for (_i = 0, _len = link.length; _i < _len; _i++) {
                                    lk = link[_i];
                                    _results.push(new Link(lk, ns));
                                }
                                return _results;
                            } else {
                                return new Link(link, ns);
                            }
                        })();
                    }
                }

                return Links;

            })();

            Link = (function() {
                function Link(link, ns) {
                    if (!(link != null ? link.href : void 0)) {
                        throw 'href is required for all links';
                    }
                    this.href = link.href, this.name = link.name, this.profile = link.profile;
                    this.templated = !!link.templated;
                    this.title = link.title || '';
                }

                return Link;

            })();

            return Parser;

        })();
        return mod.constant('HALParser', Parser);
    })(angular, angular.module('HALParser', []));

}).call(this);
