(function() {
    'use strict';

angular.module('ag-admin').controller(
    'AuthenticationController',
    function ($scope, $state, $stateParams, flash, AuthenticationRepository, agFormHandler) {

    $scope.inEdit                           = !!$stateParams.edit;
    $scope.showSetupButtons                 = false;
    $scope.showHttpBasicAuthenticationForm  = false;
    $scope.showHttpBasicAuthentication      = false;
    $scope.showHttpDigestAuthenticationForm = false;
    $scope.showHttpDigestAuthentication     = false;
    $scope.showOAuth2AuthenticationForm     = false;
    $scope.showOAuth2Authentication         = false;
    $scope.removeAuthenticationForm         = false;
    $scope.httpBasic                        = null;
    $scope.httpDigest                       = null;
    $scope.oauth2                           = null;

    var enableSetupButtons = function () {
        $scope.showSetupButtons             = true;
        $scope.showHttpBasicAuthentication  = false;
        $scope.showHttpDigestAuthentication = false;
        $scope.showOAuth2Authentication     = false;
        $scope.removeAuthenticationForm     = false;
        $scope.httpBasic                    = null;
        $scope.httpDigest                   = null;
        $scope.oauth2                       = null;
    };

    var fetchAuthenticationDetails = function (force) {
        AuthenticationRepository.fetch({cache: !force}).then(
            function (authentication) {
                if (authentication.type == 'http_basic') {
                    $scope.showSetupButtons             = false;
                    $scope.showHttpBasicAuthentication  = true;
                    $scope.showHttpDigestAuthentication = false;
                    $scope.showOAuth2Authentication     = false;
                    $scope.httpBasic                    = authentication;
                    $scope.httpDigest                   = null;
                    $scope.oauth2                       = null;
                } else if (authentication.type == 'http_digest') {
                    $scope.showSetupButtons             = false;
                    $scope.showHttpDigestAuthentication = true;
                    $scope.showHttpBasicAuthentication  = false;
                    $scope.showOAuth2Authentication     = false;
                    $scope.digest_domains               = authentication.digest_domains.split(' ');
                    $scope.httpDigest                   = authentication;
                    $scope.httpDigest.digest_domains    = authentication.digest_domains.split(' ');
                    $scope.httpBasic                    = null;
                    $scope.oauth2                       = null;
                } else if (authentication.type == 'oauth2') {
                    $scope.showSetupButtons             = false;
                    $scope.showOAuth2Authentication     = true;
                    $scope.showHttpDigestAuthentication = false;
                    $scope.showHttpBasicAuthentication  = false;
                    $scope.oauth2                       = authentication;
                    $scope.httpDigest                   = null;
                    $scope.httpBasic                    = null;
                } else {
                    enableSetupButtons();
                }
            }
        ).catch(
            function (err) {
                enableSetupButtons();
                return false;
            }
        );
    };

    var createAuthentication = function (type, options) {
        AuthenticationRepository.createAuthentication(type, options).then(
            function (authentication) {
                flash.success = 'Authentication created';
                fetchAuthenticationDetails(true);
                $scope.removeAuthenticationForm = false;
                $scope.resetForm();
            }
        ).catch(
            function (response) {
                agFormHandler.reportError(response, $scope);
            }
        );
    };

    $scope.cancelEdit = function () {
        $state.go($state.$current.name, {edit: null});
    };

    $scope.startEdit = function () {
        $state.go($state.$current.name, {edit: true});
    };

    var updateAuthentication = function (type, options) {
        if (options.hasOwnProperty('digest_domains') && typeof options.digest_domains === 'object' && Array.isArray(options.digest_domains)) {
            options.digest_domains = options.digest_domains.join(' ');
        }
        AuthenticationRepository.updateAuthentication(type, options).then(
            function (authentication) {
                agFormHandler.resetForm($scope);
                flash.success = 'Authentication updated';
                fetchAuthenticationDetails(true);
                $state.go($state.current, {edit: ''}, {reload: true});
            }
        ).catch(
            function (response) {
                agFormHandler.reportError(response, $scope);
            }
        );
    };

    $scope.resetForm = function () {
        agFormHandler.resetForm($scope);
        $scope.showHttpBasicAuthenticationForm  = false;
        $scope.showHttpDigestAuthenticationForm = false;
        $scope.showOAuth2AuthenticationForm     = false;
        $scope.digest_domains                   = '';
        $scope.dsn                              = '';
        $scope.htdigest                         = '';
        $scope.htpasswd                         = '';
        $scope.nonce_timeout                    = '';
        $scope.password                         = '';
        $scope.realm                            = '';
        $scope.route_match                      = '';
        $scope.username                         = '';
    };

    $scope.showAuthenticationSetup = function () {
        if ($scope.showHttpBasicAuthenticationForm || $scope.showHttpDigestAuthenticationForm || $scope.showOAuth2AuthenticationForm) {
            return false;
        }
        return $scope.showSetupButtons;
    };

    $scope.createHttpBasicAuthentication = function () {
        var options = {
            accept_schemes : [ 'basic' ],
            realm          : $scope.realm,
            htpasswd       : $scope.htpasswd
        };
        createAuthentication('http-basic', options);
    };

    $scope.createHttpDigestAuthentication = function () {
        var options = {
            accept_schemes : [ 'digest' ],
            realm          : $scope.realm,
            htdigest       : $scope.htdigest,
            digest_domains : $scope.digest_domains.join(' '),
            nonce_timeout  : $scope.nonce_timeout
        };
        createAuthentication('http-digest', options);
    };

    $scope.createOAuth2Authentication = function () {
        var options = {
            dsn_type    : $scope.dsn_type,
            database    : $scope.database,
            dsn         : $scope.dsn,
            route_match : $scope.route_match,
            username    : $scope.username,
            password    : $scope.password
        };
        createAuthentication('oauth2', options);
    };

    $scope.updateHttpBasicAuthentication = function () {
        var options = {
            realm          :  $scope.httpBasic.realm,
            htpasswd       :  $scope.httpBasic.htpasswd
        };
        updateAuthentication('http-basic', options);
    };

    $scope.updateHttpDigestAuthentication = function () {
        var options = {
            realm          : $scope.httpDigest.realm,
            htdigest       : $scope.httpDigest.htdigest,
            digest_domains : $scope.httpDigest.digest_domains.join(' '),
            nonce_timeout  : $scope.httpDigest.nonce_timeout
        };
        updateAuthentication('http-digest', options);
    };

    $scope.updateOAuth2Authentication = function () {
        var options = {
            dsn_type    : $scope.oauth2.dsn_type,
            database    : $scope.oauth2.database,
            dsn         : $scope.oauth2.dsn,
            route_match : $scope.oauth2.route_match,
            username    : $scope.oauth2.username,
            password    : $scope.oauth2.password
        };
        updateAuthentication('oauth2', options);
    };

    $scope.removeAuthentication = function () {
        var type;
        if ($scope.showHttpBasicAuthentication) {
            type = 'http-basic';
        } else if ($scope.showHttpDigestAuthentication) {
            type = 'http-digest';
        } else if ($scope.showOAuth2Authentication) {
            type = 'oauth2';
        }
        if (! type) {
            flash.error = 'Could not delete authentication; could not determine authentication type.';
            return;
        }
        AuthenticationRepository.removeAuthentication(type).then(
            function (response) {
                flash.success = 'Authentication removed';
                fetchAuthenticationDetails(true);
            }
        );
    };

    fetchAuthenticationDetails(true);
});

})();
