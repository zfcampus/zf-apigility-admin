(function(_) {'use strict';

angular.module('ag-admin').factory('Hal', function () {
    return {
        props: function (resource) {
            resource = this.stripLinks(resource);
            return this.stripEmbedded(resource);
        },

        stripLinks: function (resource) {
            if (typeof resource !== 'object') {
                return resource;
            }

            if (Array.isArray(resource)) {
                var self = this;
                _.forEach(resource, function (resourceItem, key) {
                    resource[key] = self.stripLinks(resourceItem);
                });
                return resource;
            }

            if (! resource._links) {
                return resource;
            }

            var clone = _.cloneDeep(resource);
            delete clone._links;
            return clone;
        },
        stripEmbedded: function (resource) {
            if (typeof resource != 'object') {
                return resource;
            }

            if (Array.isArray(resource)) {
                var self = this;
                _.forEach(resource, function (resourceItem, key) {
                    resource[key] = self.stripEmbedded(resourceItem);
                });
                return resource;
            }

            if (! resource._embedded) {
                return resource;
            }

            var clone = _.cloneDeep(resource);
            delete clone._embedded;
            return clone;
        },
        pluckCollection: function (prop, resource) {
            if (typeof resource != 'object' || Array.isArray(resource)) {
                return [];
            }
            if (! resource._embedded) {
                return [];
            }
            if (! resource._embedded[prop]) {
                return [];
            }

            /* Deep clone of embedded resource/collection */
            return _.cloneDeep(resource._embedded[prop]);
        },
        getLink: function (rel, resource) {
            if (typeof resource != 'object' || Array.isArray(resource)) {
                return false;
            }

            if (! resource._links || ! resource._links[rel] || ! resource._links[rel].href) {
                return false;
            }

            return resource._links[rel].href;
        }
    };
});

})(_);
