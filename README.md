Apigility Admin
===============

[![Build Status](https://travis-ci.org/zfcampus/zf-apigility-admin.png)](https://travis-ci.org/zfcampus/zf-apigility-admin)

Introduction
------------

The `zf-apigility-admin` modules delivers the backend management API and UI that is responsible
for the RAD development of API's.

> *NOTE:* it is advisable to NOT enable this module in production systems.

Installation
------------

Run the following `composer` command:

```console
$ composer require "zfcampus/zf-apigility-admin:~1.0-dev"
```

Alternately, manually add the following to your `composer.json`, in the `require` section:

```javascript
"require": {
    "zfcampus/zf-apigility-admin": "~1.0-dev"
}
```

And then run `composer update` to ensure the module is installed.

Finally, add the module name to your project's `config/application.config.php` under the `modules`
key:

```php
return array(
    /* ... */
    'modules' => array(
        /* ... */
        'ZF\Apigility\Admin',
    ),
    /* ... */
);
```

Configuration
-------------

There is no custom user level configuration for this module.

Since this particular module is responsible for providing API's and the UI experience, it has a
significant amount of configuration that it requires for it's proper functioning in a development
environment.  Since it is highly unlikely that developers would need to modify the system-level
configuration, it is omitted in this README, but is located at
[config/module.config.php](config/module.config.php), if you are interested in seeing what
the configuration entails.

Routes
------

This module exposes HTTP accessible API endpoints and static assets.

API Endpoints
-------------

All routes are prefixed with `/apigility` by default.

### `api/config`

This endpoint is for examining the application configuration, and providing
overrides of individual values in it. All overrides are written to a single
file, `config/autoload/development.php`; you can override that location in your
configuration via the `zf-configuration/config-file` key.

- Accept: `application/json`, `application/vnd.zfcampus.v1.config+json`

  The first will deliver representations as a flat array of key/value pairs,
  with the keys being dot-separated values, just as you would find in INI.

  The second will deliver the configuration as a tree.

- Content-Type: `application/json`, `application/vnd.zfcampus.v1.config+json`

  The first expects key/value pairs, with keys being dot-separated values, as
  you would find in INI files.

  The second expects a nested array/object of configuration.

- Methods: `GET`, `PATCH`

- Errors: `application/problem+json`

### `api/authentication`

This REST endpoint is for creating, updating, and deleting the authentication
configuration for your application. It uses the [authentication
resource](#authentication).

- Accept `application/json`

  Returns an [authentication resource](#authentication) on success.

- Content-Type: `application/json`

  Expects an [authentication resource](#authentication) with all details
  necessary for establishing HTTP authentication.

- HTTP methods: `GET`, `POST`, `PATCH`, `DELETE`

  `GET` returns a `404` response if no authentication has previously been setup.
  `POST` will return a `201` response on success. `PATCH` will return a `200`
  response on success. `DELETE` will return a `204` response on success.

- Errors: `application/problem+json`

### `api/db-adapter[/:adapter\_name]`

This REST endpoint is for creating, updating, and deleting named `Zend\Db`
adapters; it uses the [db-adapter resource](#db-adapter).

- Accept `application/json`

  Returns a [db-adapter resource](#db-adapter) on success.

- Content-Type: `application/json`

  Expects [db-adapter resource](#db-adapter) with all details necessary for
  creating a DB connection.

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`, `DELETE`

- Errors: `application/problem+json`

### `api/module/:name/authorization?version=:version`

This REST endpoint is for fetching and updating the authorization
configuration for your application. It uses the [authorization
resource](#authorization).

- Accept `application/json`

  Returns an [authorization resource](#authorization) on success.

- Content-Type: `application/json`

  Expects an [authorization resource](#authorization) with all details
  necessary for establishing HTTP authentication.

- HTTP methods: `GET`, `PUT`

  `GET` will always return an entity; if no configuration existed previously
  for the module, or if any given service at the given version was not listed
  in the configuration, it will provide the default values.

  `PUT` will return a `200` response on success, along with the updated
  entity.

- Errors: `application/problem+json`

### `api/db-adapter[/:adapter\_name]`

This REST endpoint is for creating, updating, and deleting named `Zend\Db`
adapters; it uses the [db-adapter resource](#db-adapter).

- Accept `application/json`

  Returns a [db-adapter resource](#db-adapter) on success.

- Content-Type: `application/json`

  Expects [db-adapter resource](#db-adapter) with all details necessary for
  creating a DB connection.

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`, `DELETE`

- Errors: `application/problem+json`


### `api/config/module?module={module name}`

This operates exactly like the `/admin/api/config` endpoint, but expects a known
module name. When provided, it allows you to introspect and manipulate the
configuration file for that module.

### `api/module.enable`

This endpoint will Apigility-enable (Apigilify) an existing module.

- Accept: `application/json`

  Returns a [Module resource](#module) on success.

- Content-Type: `application/json`

  Expects an object with the property "module" describing an existing ZF2 module.

  ```javascript
  {
  "module": "Status"
  }
  ```

- Methods: `PUT`

- Errors: `application/problem+json`

### `api/validators`

This endpoint provides a sorted list of all registered validator plugins; the
use case is for building a drop-down of available plugins when creating an
input filter for a service.

- Accept: `application/json`

  Returns an `application/json` response with the following format on success:

  ```javascript
  {
    "validators": [
      "list",
      "of",
      "validators"
    ]
  }
  ```

- Methods: `GET`

- Errors: `application/problem+json`

### `api/versioning`

This endpoint is for adding a new version to an existing API. If no version is
passed in the payload, the version number is simply incremented.

- Accept: `application/json`

  Returns the response `{ "success": true, "version": :version: }` on success,
  an API-Problem payload on error.

- Content-Type: `application/json`

  Expects an object with the property "module", providing the name of a ZF2,
  Apigility-enabled module; optionally, a "version" property may also be
  provided to indicate the specific version string to use.

  ```javascript
  {
    "module": "Status",
    "version": 10
  }
  ```

- Methods: `PATCH`

- Errors: `application/problem+json`

### `api/module[/:name]`

This is the canonical endpoint for [Module resources](#module).

- Accept: `application/json`

  Returns either a single [Module resource](#module) (when a `name` is provided)
  or a collection of Module resources (when no `name` is provided) on success.

- Content-Type: `application/json`

  Expects an object with the property "name" describing the module to create:

  ```javascript
  {
  "name": "Status"
  }
  ```

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`

- Errors: `application/problem+json`

### `api/module/:name/rpc[/:controller\_service\_name]`

This is the canonical endpoint for [RPC resources](#rpc).

- Accept: `application/json`

  Returns either a single [RPC resource](#rpc) (when a `controller\_service\_name`
  is provided) or a collection of RPC resources (when no
  `controller\_service\_name` is provided) on success.

- Content-Type: `application/json`

  Expects an object with the property "service_name" describing the endpoint to
  create:

  ```javascript
  {
    "service_name": "Status"
  }
  ```

  You may also provide any other options listed in the [RPC resource](#rpc).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PATCH`

- The query string variable `version` may be passed to the collection to filter
  results by version: e.g., `/admin/api/module/:name/rpc?version=2`.

- Errors: `application/problem+json`

### `api/module/:name/rpc/:controller\_service\_name/inputfilter[/:input\_filter\_name]`

This service is for creating, updating, and deleting named [input filters](#inputfilter)
associated with a given RPC service.

- Accept: `application/json`

  Returns either single [input filter](#inputfilter) (when an
  `input\_filter\_name` is provided) or a collection of input filters (when no
  `input\_filter\_name` is provided) on success. Typically, only one input
  filter will be associated with a given RPC service.

  Input filters returned will also compose a property `input\_filter\_name`,
  which is the identifier for the given input filter.

- Content-Type: `aplication/json`

  Expects an [input filter](#inputfilter).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PUT`, `DELETE`

- Errors: `application/problem+json`

### `api/module/:name/rest[/:controller\_service\_name]`

This is the canonical endpoint for [REST resources](#rest).

Can be used for any type of REST resource, including DB-Connected (and, in the
future, Mongo-Connected).

DB-Connected resources expect the following additional properties (and will
return them as well):

- `adapter\_name`: A named DB adapter service.
- `table\_name`: The DB table associated with this service.
- `hydrator\_name`: Optional; the name of a hydrator service used to hydrate rows
  returned by the database; defaults to ArraySerializable.
- `table\_service`: Optional; this is auto-generated by default, but an alternate
  TableGateway service may be provided.

- Accept: `application/json`

  Returns either a single [REST resource](#rest) (when a `controller\_service\_name`
  is provided) or a collection of REST resources (when no
  `controller\_service\_name` is provided) on success.

- Content-Type: `application/json`

  Expects an object with the property `resource\_name` describing the module to create:

  ```javascript
  {
    "resource_name": "Status"
  }
  ```

  You may also provide any other options listed in the [REST resource](#rest).

- Collection Methods: `GET`, `POST`, `DELETE`

- Resource Methods: `GET`, `PATCH`

- The query string variable `version` may be passed to the collection to filter
  results by version: e.g., `/admin/api/module/:name/rest?version=2`.

- Errors: `application/problem+json`

### `api/module/:name/rest/:controller\_service\_name/inputfilter[/:input\_filter\_name]`

This service is for creating, updating, and deleting named [input filters](#inputfilter)
associated with a given REST service.

- Accept: `application/json`

  Returns either single [input filter](#inputfilter) (when an
  `input\_filter\_name` is provided) or a collection of input filters (when no
  `input\_filter\_name` is provided) on success. Typically, only one input
  filter will be associated with a given REST service.

  Input filters returned will also compose a property `input\_filter\_name`,
  which is the identifier for the given input filter.

- Content-Type: `aplication/json`

  Expects an [input filter](#inputfilter).

- Collection Methods: `GET`, `POST`

- Resource Methods: `GET`, `PUT`, `DELETE`

- Errors: `application/problem+json`


ZF2 Events
----------

### Listeners

#### ZF\Apigility\Admin\Module

This listener is attached to `MvcEvent::EVENT_RENDER` at priority `100`.  It is responsible for
conditionally attaching a listener depending on if the controller service result is that of
an _entity_ or that of a _collection_.  For both result based listeners, they are attached
to the `ZF\Hal\Plugin\Hal` events of `renderEntity` and `renderCollection.entity`, which
ensures they will be dispatched when the HAL plugin has an opportunity to start rendering.

ZF2 Services
------------

### Models

Many of the model services provided by `zf-apigility-admin` either deal with the generation and
modification of PHP code, or the generation and modification of PHP based configuration files.

- `ZF\Apigility\Admin\Model\AuthenticationModel` - responsible for creating and modifying the
  authentication specific configuration of basic, digest and OAuth2 strategies. Sensitive
  information will be written to local configuration files while structural information is
  written to global and module files.
- `ZF\Apigility\Admin\Model\AuthorizationModelFactory` - responsible for writing the authorization
  specific details (the ACL matrix of allow/disallow) to the module configuration file.
- `ZF\Apigility\Admin\Model\ContentNegotiationModel` - responsible for writing custom content-
  negotiation selectors to the global configuration file.
- `ZF\Apigility\Admin\Model\ContentNegotiationResource` - REST resource that consumes the
  `ContentNegotiationModel` in order to expose an API endpoint for content-negotiation
  configuration management.
- `ZF\Apigility\Admin\Model\DbAdapterModel` - responsible for writing database adapter specific
  configuration between application level global and local configuration files.  Sensitive
  information is written to local configuration files.
- `ZF\Apigility\Admin\Model\DbAdapterResource` - REST resource that consumes the `DbAdapterModel`
  in order to expose an API endpoint for database adapter configuration management.
- `ZF\Apigility\Admin\Model\DbConnectedRestServiceModel` - responsible for writing the required
  configuration information necessary to expose a database table as a REST resource.
- `ZF\Apigility\Admin\Model\DocumentationModel` - responsible for writing a special named
  file in the module's configuration directory that will contain all custom API documentation
  for requests, responses, and all other documentable elements of an API.
- `ZF\Apigility\Admin\Model\InputFilterModel` - responsible for writing the input filter
  specification configuration for each module.
- `ZF\Apigility\Admin\Model\FiltersModel` - responsible for providing, through the API, a list of
  built-in filters and their metadata.
- `ZF\Apigility\Admin\Model\HydratorsModel` - responsible for configuring and managing the
  global list of hydrators.
- `ZF\Apigility\Admin\Model\ModuleModel` - responsible for aggregating module information including
  REST and RPC services and exposing this information through the API.  Additionally, when creating
  a new module, this will create the code artifacts necessary for an Apigility enabled module.
- `ZF\Apigility\Admin\Model\ModuleResource` - responsible for exposing the `ModuleModel` as a
  REST resource in the Apigility API.
- `ZF\Apigility\Admin\Model\RestServiceModel` - responsible for presenting REST services, as they
  are defined in `zf-rest` in a way that can be created and modified, to be used in the UI.
- `ZF\Apigility\Admin\Model\RestServiceResource` - responsible for consuming `RestServiceModel` and
  exposing this model as a REST resource in the Apigility API.
- `ZF\Apigility\Admin\Model\RestServiceModelFactory` - responsible for creating `RestServiceModel`'s
- `ZF\Apigility\Admin\Model\RpcServiceModel` - responsible for presenting RPC services, as they are
  defined in `zf-rpc` in a way that can be created and modified, to be used in the UI.
- `ZF\Apigility\Admin\Model\RpcServiceResource` - responsible for consuming `RpcServiceModel`'s and
  exposing this model as a REST resource in the Apigility API.
- `ZF\Apigility\Admin\Model\RpcServiceModelFactory` - responsible for creating `RpcServiceModel`'s
- `ZF\Apigility\Admin\Model\ValidatorsModel` - responsible for providing, through the API, a list of
  built-in validators.
- `ZF\Apigility\Admin\Model\ValidatorMetadataModel` - responsible for providing metadata about
  validators provided through, and in conjunction with the `ValidatorModel` and validator API.
- `ZF\Apigility\Admin\Model\VersioningModel` - responsible for modeling the workflow and module
  code creation artifacts that are required to increase the version of a particular Apigility
  based REST or RPC service.
- `ZF\Apigility\Admin\Model\VersioningModelFactory` - responsible for createing `VersioningModel`'s
