(function() {'use strict';

angular.module('ag-admin').controller(
    'AuthenticationController',
    function ($scope, flash, AuthenticationRepository) {

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
        AuthenticationRepository.fetch({cache: !force})
            .then(function (authentication) {
                if (authentication.type == "http_basic") {
                    $scope.showSetupButtons             = false;
                    $scope.showHttpBasicAuthentication  = true;
                    $scope.showHttpDigestAuthentication = false;
                    $scope.showOAuth2Authentication     = false;
                    $scope.httpBasic                    = authentication;
                    $scope.httpDigest                   = null;
                    $scope.oauth2                       = null;
                } else if (authentication.type == "http_digest") {
                    $scope.showSetupButtons             = false;
                    $scope.showHttpDigestAuthentication = true;
                    $scope.showHttpBasicAuthentication  = false;
                    $scope.showOAuth2Authentication     = false;
                    $scope.digest_domains               = authentication.digest_domains.split(" ");
                    $scope.httpDigest                   = authentication;
                    $scope.httpDigest.digest_domains = authentication.digest_domains.split(" ");
                    $scope.httpBasic                    = null;
                    $scope.oauth2                       = null;
                } else if (authentication.type == "oauth2") {
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
            }, function (err) {
                enableSetupButtons();
                return false;
            }
        );
    };

    var createAuthentication = function (options) {
        AuthenticationRepository.createAuthentication(options).then(
            function success(authentication) {
                flash.success = 'Authentication created';
                fetchAuthenticationDetails(true);
                $scope.removeAuthenticationForm = false;
                $scope.resetForm();
            },
            function error(response) {
                flash.error('Unable to create authentication; please verify that the DSN is valid.');
            }
        );
    };

    var updateAuthentication = function (options) {
        if (options.hasOwnProperty('digest_domains') && typeof options.digest_domains === 'object' && Array.isArray(options.digest_domains)) {
            options.digest_domains = options.digest_domains.join(' ');
        }
        AuthenticationRepository.updateAuthentication(options).then(
            function success(authentication) {
                flash.success = 'Authentication updated';
                fetchAuthenticationDetails(true);
            },
            function error(response) {
                flash.error('Unable to update authentication; please verify that the DSN is valid.');
            }
        );
    };

    $scope.resetForm = function () {
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
            accept_schemes : [ "basic" ],
            realm          : $scope.realm,
            htpasswd       : $scope.htpasswd
        };
        createAuthentication(options);
    };

    $scope.createHttpDigestAuthentication = function () {
        var options = {
            accept_schemes : [ "digest" ],
            realm          : $scope.realm,
            htdigest       : $scope.htdigest,
            digest_domains : $scope.digest_domains.join(" "),
            nonce_timeout  : $scope.nonce_timeout
        };
        createAuthentication(options);
    };

    $scope.createOAuth2Authentication = function () {
        var options = {
            dsn         : $scope.dsn,
            username    : $scope.username,
            password    : $scope.password,
            route_match : $scope.route_match
        };
        createAuthentication(options);
    };

    $scope.updateHttpBasicAuthentication = function () {
        var options = {
            realm          :  $scope.httpBasic.realm,
            htpasswd       :  $scope.httpBasic.htpasswd
        };
        updateAuthentication(options);
    };

    $scope.updateHttpDigestAuthentication = function () {
        var options = {
            realm          : $scope.httpDigest.realm,
            htdigest       : $scope.httpDigest.htdigest,
            digest_domains : $scope.httpDigest.digest_domains.join(" "),
            nonce_timeout  : $scope.httpDigest.nonce_timeout
        };
        updateAuthentication(options);
    };

    $scope.updateOAuth2Authentication = function () {
        var options = {
            dsn         : $scope.oauth2.dsn,
            username    : $scope.oauth2.username,
            password    : $scope.oauth2.password,
            route_match : $scope.oauth2.route_match
        };
        updateAuthentication(options);
    };

    $scope.removeAuthentication = function () {
        AuthenticationRepository.removeAuthentication()
            .then(function (response) {
                flash.success = 'Authentication removed';
                fetchAuthenticationDetails(true);
            });
    };

    fetchAuthenticationDetails(true);
});

})();
