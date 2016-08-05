# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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

- Updates the `AbstractAutodiscoveryModel` to no longer directly implement
  `ServiceLocatorAwareInterface`, and updates factories for autodiscovery
  classes to inject their service locator instead. This change removes
  deprection notices when using Apigility with the zend-mvc 2.7+ series.

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
