# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

## 1.5.9 - 2016-10-12

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#369](https://github.com/zfcampus/zf-apigility-admin/pull/369) updates the
  migration script's `public/index.php` changes such that the stub generated now
  will vary the script location for `zf-development-mode` based on whether or
  not a Windows operating system is detected.

## 1.5.8 - 2016-10-11

### Added

- [#363](https://github.com/zfcampus/zf-apigility-admin/pull/363) adds an entry
  for `Zend\Validator\Uuid` to the validator metadata.
- [#368](https://github.com/zfcampus/zf-apigility-admin/pull/368) updates the 
  `bin/apigility-upgrade-to-1.5` script to also inject a stub into the
  `public/index.php` that will intercept `php public/index.php development
  [enable|disable]` commands, and proxy them to the v3 zf-development-mode
  tooling.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#365](https://github.com/zfcampus/zf-apigility-admin/pull/365) updates the 
  logic in the `DbAutodiscoveryModel` to catch and report exceptions due to
  metadata discovery issues (typically invalid character sets) that were
  previously returning an empty list, providing better diagnostic details to
  end-users.

## 1.5.7 - 2016-08-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#362](https://github.com/zfcampus/zf-apigility-admin/pull/362) adds an entry
  to remove `ZF\Apigility\Provider` from the module list in the
  `apigility-update-to-1.5` script. The package does not need to be listed as a
  module, as Composer will autoload all interfaces it defines.

## 1.5.6 - 2016-08-14

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#361](https://github.com/zfcampus/zf-apigility-admin/pull/361) updates the
  `ModuleModel` to vary the contents of a generated `module.config.php` based on
  the short-array notation configuration setting.
- This release updates the following dependencies to the listed minimum
  supported versions:
  - zfcampus/zf-apigility-admin-ui: 1.3.7
  - zfcampus/zf-configuration: 1.2.1

## 1.5.5 - 2016-08-12

### Added

- [#358](https://github.com/zfcampus/zf-apigility-admin/pull/358) adds
  documentation for the `zf-apigility-admin.path_spec` configuration value to
  both the README and the module configuration file.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#360](https://github.com/zfcampus/zf-apigility-admin/pull/360) fixes how the
  `ModuleModel` generates configuration, allowing it to generate short array
  syntax. The behavior is configurable using the
  `zf-configuration.enable_short_array` configuration value.

## 1.5.4 - 2016-08-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#357](https://github.com/zfcampus/zf-apigility-admin/pull/357) fixes an issue
  with detection of module versions when using Apigility-generated PSR-4
  modules.

## 1.5.3 - 2016-08-11

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#356](https://github.com/zfcampus/zf-apigility-admin/pull/356) fixes a fatal
  error when calling the versioning API, due to providing the
  `VersioningController` with an incorrect versioning model factory.
- [#356](https://github.com/zfcampus/zf-apigility-admin/pull/356) fixes issues
  when versioning API modules that are in PSR-4 layout. The `ModuleModel` now
  autodiscovers which layout (PSR-0 or PSR-4) is used by a given module.

## 1.5.2 - 2016-08-10

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#354](https://github.com/zfcampus/zf-apigility-admin/pull/354) updates the
  upgrade script to add dependencies required by Apigility 1.3 and earlier
  skeletons. These changes include:
  - adding zendframework/zend-mvc-i18n as a dependency
  - adding the `Zend\I18n` and `Zend\Mvc\I18n` modules to `config/modules.config.php`

## 1.5.1 - 2016-08-10

### Added

- [#353](https://github.com/zfcampus/zf-apigility-admin/pull/353) adds the
  `apigility-version` API, to allow reporting to the UI the current Apigility
  skeleton version. It returns the value of `Apigility\VERSION` if defined, and
  `@dev` if not.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.5.0 - 2016-08-09

### Added

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) updates the
  component to be forwards compatible with Zend Framework component v3 releases,
  while retaining support for v2 releases. This includes supporting both v2 and
  v3 versions of factory invocation, and triggering event listeners using syntax
  that works on both v2 and v3 releases of zend-eventmanager, amonst other
  changes.

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) adds a script
  to assist users in updating existing Apigility applications to use Zend
  Framework component v3 releases:

  ```bash
  $ ./vendor/bin/apigility-upgrade-to-1.5 -h
  ```

  In most cases, you can call it without arguments. Running the script updates
  your `composer.json` to remove several entries, update others, and add some;
  it then updates your list of modules, and then installs dependencies for you.

  If you need to update manually for any reason, you will need to
  [follow the steps in the README](README.md#upgrading-to-v3-zend-framework-components-from-1.5).

- [#321](https://github.com/zfcampus/zf-apigility-admin/pull/321) adds a
  `patchList()` stub to the REST resource class template, so that it's present
  by default.

- [#327](https://github.com/zfcampus/zf-apigility-admin/pull/327) adds support
  for working with modules that are in PSR-4 directory format. While the admin
  still does not create PSR-4 modules, it will now correctly interact with those
  that you manually convert to PSR-4.

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) extracts
  listeners previously defined in the `Module` class into their own classes.
  These include:

  - `ZF\Apigility\Admin\DisableHttpCacheListener`, which listens on the
    `MvcEvent::EVENT_FINISH` event at high priority in order to return cache
    busting headers in the returned response.
  - `ZF\Apigility\Admin\EnableHalRenderCollectionsListener`, which listens on
    the `MvcEvent::EVENT_ROUTE` event at low priority in order to set the
    "render collections" flag on zf-hal's `Hal` plugin if a controller from the
    module is matched.
  - `ZF\Apigility\Admin\InjectModuleResourceLinksListener`, which listens on the
    `MvcEvent::EVENT_RENDER` event at high priority in order to attach listeners to
    events on the zf-hal `Hal` plugin. These listeners were also previously
    defined in the `Module` class, and are now part of this new listener, as it
    aggregates some state used by each.
  - `ZF\Apigility\Admin\NormalizeMatchedControllerServiceNameListener`, which
    listens on the `MvcEvent::EVENT_ROUTE` at low priority in order to
    normalize the controller service name provided via the URI to a FQCN.
  - `ZF\Apigility\Admin\NormalizeMatchedInputFilterNameListener`, which listens
    on the `MvcEvent::EVENT_ROUTE` at low priority in order to normalize the
    input filter name provided via the URI to a FQCN.

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) extracts
  service factories previously defined in the `Module` class into their own
  classes. These include:
  - `ZF\Apigility\Admin\Model\AuthenticationModelFactory`
  - `ZF\Apigility\Admin\Model\AuthorizationModelFactory`
  - `ZF\Apigility\Admin\Model\ContentNegotiationModelFactory`
  - `ZF\Apigility\Admin\Model\ContentNegotiationResourceFactory`
  - `ZF\Apigility\Admin\Model\DbAdapterModelFactory`
  - `ZF\Apigility\Admin\Model\DbAdapterResourceFactory`
  - `ZF\Apigility\Admin\Model\DbAutodiscoveryModelFactory`
  - `ZF\Apigility\Admin\Model\DoctrineAdapterModelFactory`
  - `ZF\Apigility\Admin\Model\DoctrineAdapterResourceFactory`
  - `ZF\Apigility\Admin\Model\DocumentationModelFactory`
  - `ZF\Apigility\Admin\Model\FiltersModelFactory`
  - `ZF\Apigility\Admin\Model\InputFilterModelFactory`
  - `ZF\Apigility\Admin\Model\ModuleModelFactory`
  - `ZF\Apigility\Admin\Model\ModulePathSpecFactory`
  - `ZF\Apigility\Admin\Model\ModuleResourceFactory`
  - `ZF\Apigility\Admin\Model\ModuleVersioningModelFactory`
  - `ZF\Apigility\Admin\Model\ModuleVersioningModelFactoryFactory`
  - `ZF\Apigility\Admin\Model\RestServiceModelFactory`
  - `ZF\Apigility\Admin\Model\RestServiceModelFactoryFactory`
  - `ZF\Apigility\Admin\Model\RestServiceResourceFactory`
  - `ZF\Apigility\Admin\Model\RpcServiceModelFactoryFactory`
  - `ZF\Apigility\Admin\Model\RpcServiceResourceFactory`
  - `ZF\Apigility\Admin\Model\ValidatorMetadataModelFactory`
  - `ZF\Apigility\Admin\Model\ValidatorsModelFactory`
  - `ZF\Apigility\Admin\Model\VersioningModelFactory`
  - `ZF\Apigility\Admin\Model\VersioningModelFactoryFactory`

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) extracts
  controller factories previously defined in the `Module` class into their own
  classes, and updates several factories that already existed. Factories that
  existed were updated to follow both the zend-servicemanager v2 and v3
  signatures, to allow compatibility with both versions; as such, if you were
  extending these previously, you may potentially experience breakage due to
  signatures. The new classes include:
  - `ZF\Apigility\Admin\Controller\AuthenticationControllerFactory`
  - `ZF\Apigility\Admin\Controller\AuthenticationTypeControllerFactory`
  - `ZF\Apigility\Admin\Controller\AuthorizationControllerFactory`
  - `ZF\Apigility\Admin\Controller\ConfigControllerFactory`
  - `ZF\Apigility\Admin\Controller\DashboardControllerFactory`
  - `ZF\Apigility\Admin\Controller\DbAutodiscoveryControllerFactory`
  - `ZF\Apigility\Admin\Controller\DocumentationControllerFactory`
  - `ZF\Apigility\Admin\Controller\FiltersControllerFactory`
  - `ZF\Apigility\Admin\Controller\HydratorsControllerFactory`
  - `ZF\Apigility\Admin\Controller\InputFilterControllerFactory`
  - `ZF\Apigility\Admin\Controller\ModuleConfigControllerFactory`
  - `ZF\Apigility\Admin\Controller\ModuleCreationControllerFactory`
  - `ZF\Apigility\Admin\Controller\SourceControllerFactory`
  - `ZF\Apigility\Admin\Controller\StrategyControllerFactory`
  - `ZF\Apigility\Admin\Controller\ValidatorsControllerFactory`
  - `ZF\Apigility\Admin\Controller\VersioningControllerFactory`

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) exposes the
  module to zend-component-installer.

### Deprecated

- Nothing.

### Removed

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) removes
  support for PHP 5.5.
- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) removes the
  dependency on rwoverdijk/assetmanager, allowing usage of any tool that
  understands the same configuration (and, specifically, the
  `asset_manager.resolver_configs.paths` configuration directive). However, **this
  means that for those upgrading via simple `composer update`, you will also
  need to execute `composer require rwoverdijk/assetmanager` immediately for
  your application to continue to work.**

### Fixed

- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) updates
  `ZF\Apigility\Admin\Controller\StrategyController` to accept a
  `ContainerInterface` to its constructor, instead of relying on auto-injection
  of a zend-servicemanager instance via an initializer; this change removes
  deprecation notices from its usage of `getServiceLocator()` (it no longer
  calls that method), and documents the dependency explicitly. If you were
  extending this class previously, you may need to update your factory.
- [#348](https://github.com/zfcampus/zf-apigility-admin/pull/348) updates
  `ZF\Apigility\Admin\Model\DoctrineAdapterResource`'s contructor to make the
  second argument, `$loadedModules`, optional. If you were extending the class
  previously, you may need to update your signature.

## 1.4.3 - 2016-08-05

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#350](https://github.com/zfcampus/zf-apigility-admin/pull/350) updates the
  `Module` class to pull entities composed in `ZF\Hal\Entity` instances via the
  `getEntity()` method of that class, if it exists (introduced in zf-hal 1.4).
  This change prevents zf-hal 1.4+ versions from emitting deprecation notices,
  and thus breaking usage of the admin API.

## 1.4.2 - 2016-06-28

### Added

- Nothing.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#344](https://github.com/zfcampus/zf-apigility-admin/pull/344) removes the
  `ServiceLocatorAwareInterface`, and updates factories for autodiscovery
  classes to inject their service locator instead. This change removes
  deprecation notices when using Apigility with the zend-mvc 2.7+ series.

## 1.4.1 - 2016-01-26

### Added

- [#329](https://github.com/zfcampus/zf-apigility-admin/pull/329) improved install instructions

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#320](https://github.com/zfcampus/zf-apigility-admin/pull/320) typo fixes on array_fill() usage

## 1.4.0 - 2015-09-22

### Added

- [#317](https://github.com/zfcampus/zf-apigility-admin/pull/317) updates the component
  to use zend-hydrator for hydrator functionality; this provides forward
  compatibility with zend-hydrator, and backwards compatibility with
  hydrators from older versions of zend-stdlib.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- Nothing.

## 1.3.2 - 2015-09-22

### Added

- [#311](https://github.com/zfcampus/zf-apigility-admin/pull/311) updates the
  API to allow using custom authentication adapters (vs only OAuth2 or HTTP).
- [#314](https://github.com/zfcampus/zf-apigility-admin/pull/314) provides a
  simple fix to the `DbAutodiscoveryModel` which allows using database views for
  DB-connected services.

### Deprecated

- Nothing.

### Removed

- Nothing.

### Fixed

- [#316](https://github.com/zfcampus/zf-apigility-admin/pull/316) updates the
  zend-stdlib dependency to reference `>=2.5.0,<2.7.0` to ensure hydrators
  will work as expected following extraction of hydrators to the zend-hydrator
  repository.
- [#316](https://github.com/zfcampus/zf-apigility-admin/pull/316) fixes the
  OAuth2 input filter to ensure it works correctly with the latest versions of
  zend-inputfilter.
