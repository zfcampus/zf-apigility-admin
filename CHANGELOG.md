# Changelog

All notable changes to this project will be documented in this file, in reverse chronological order by release.

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
