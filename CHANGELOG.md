# Changelog

## [3.0.0](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.2.0...v3.0.0) (2026-02-09)


### ⚠ BREAKING CHANGES

* Validation and hydration flow has changed; `RequestDtoTrait` was removed

### Features

* Add strict flag to RequestDto to allow controlling type coercion when properties are assigned. ([83ca5ca](https://github.com/crtl/request-dto-resolver-bundle/commit/83ca5ca1652ab023755fdda009efc17aa0022636))
* Only assign properties when provided with data. ([83ca5ca](https://github.com/crtl/request-dto-resolver-bundle/commit/83ca5ca1652ab023755fdda009efc17aa0022636))


### Miscellaneous Chores

* fix coverage badge in readme ([a6bde76](https://github.com/crtl/request-dto-resolver-bundle/commit/a6bde76d3851e3a9b71c0d4bf851876dc44f6357))


### Code Refactoring

* unify hydration and validation flow with circular reference detection ([83ca5ca](https://github.com/crtl/request-dto-resolver-bundle/commit/83ca5ca1652ab023755fdda009efc17aa0022636))

## [2.2.0](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.1.0...v2.2.0) (2026-02-04)


### Bug Fixes

* violation property path prefixing for array sequences. ([#15](https://github.com/crtl/request-dto-resolver-bundle/issues/15)) ([2f54bef](https://github.com/crtl/request-dto-resolver-bundle/commit/2f54bef9632854ac31f4b07785635d113871a609))


### Miscellaneous Chores

* **2.x:** release 2.1.0 ([#18](https://github.com/crtl/request-dto-resolver-bundle/issues/18)) ([70e8b01](https://github.com/crtl/request-dto-resolver-bundle/commit/70e8b0116acfb92d0e9c1282f4d958b7f02d55b8))
* **2.x:** release 2.1.1 ([#16](https://github.com/crtl/request-dto-resolver-bundle/issues/16)) ([8406f9c](https://github.com/crtl/request-dto-resolver-bundle/commit/8406f9cdc2fd2f22831e6e9806dde1c68a94cf77))
* release 2.1.0 ([a87512b](https://github.com/crtl/request-dto-resolver-bundle/commit/a87512bb43b9074c3571c0ee96575a7b8613a2e2))
* release 2.2.0 ([9b9398c](https://github.com/crtl/request-dto-resolver-bundle/commit/9b9398c909b2c42d5a7f8528bedd28d8a8d2566a))

## [2.1.0](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.1.1...v2.1.0) (2026-02-04)


### Miscellaneous Chores

* release 2.1.0 ([a87512b](https://github.com/crtl/request-dto-resolver-bundle/commit/a87512bb43b9074c3571c0ee96575a7b8613a2e2))

## [2.1.1](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.1.0...v2.1.1) (2026-02-02)


### Bug Fixes

* violation property path prefixing for array sequences. ([#15](https://github.com/crtl/request-dto-resolver-bundle/issues/15)) ([2f54bef](https://github.com/crtl/request-dto-resolver-bundle/commit/2f54bef9632854ac31f4b07785635d113871a609))

## [2.1.0](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.0.5...v2.1.0) (2026-02-02)


### Features

* fallback to reading property from instance when initialized in RequestDtoTrait::getValue ([#13](https://github.com/crtl/request-dto-resolver-bundle/issues/13)) ([29ab207](https://github.com/crtl/request-dto-resolver-bundle/commit/29ab2072912f6b171966923dbfff11f7d8d44085))

## [2.0.5](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.0.4...v2.0.5) (2026-02-02)


### Bug Fixes

* make request in RequestDtoTrait constructor nullable to ensure b… ([#11](https://github.com/crtl/request-dto-resolver-bundle/issues/11)) ([c2daa34](https://github.com/crtl/request-dto-resolver-bundle/commit/c2daa34b4928a4eedc6ed5f9c2322514ea27ec77))

## [2.0.4](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.0.3...v2.0.4) (2026-02-02)


### Bug Fixes

* exception thrown by type-info when reading mixed properties with union types by changing packages in composer.lock ([#9](https://github.com/crtl/request-dto-resolver-bundle/issues/9)) ([520e887](https://github.com/crtl/request-dto-resolver-bundle/commit/520e88745809d0d07d52105e0f5456a3dcc1f97b))

## [2.0.3](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.0.2...v2.0.3) (2026-02-02)


### ⚠ BREAKING CHANGES

* Namespace renamed to `Crtl\RequestDtoResolverBundle` and property info extractor configuration updated.

### Features

* extend DTO resolver/validation with group sequence providers and nested DTO arrays ([#7](https://github.com/crtl/request-dto-resolver-bundle/issues/7)) ([3d9741b](https://github.com/crtl/request-dto-resolver-bundle/commit/3d9741b72ddce24714c6c1513fd09d7e16fcb3ab))


### Miscellaneous Chores

* release 2.0.3 ([a8ec38d](https://github.com/crtl/request-dto-resolver-bundle/commit/a8ec38dea4de6c38e207106d091d4351e528b6c4))

## [2.0.2](https://github.com/crtl/request-dto-resolver-bundle/compare/v2.0.1...v2.0.2) (2026-02-01)


### Miscellaneous Chores

* **dependencies:** update symfony package constraints and ci workflow matrix ([#5](https://github.com/crtl/request-dto-resolver-bundle/issues/5)) ([6cc55e6](https://github.com/crtl/request-dto-resolver-bundle/commit/6cc55e676dfc139c4ad8db78927096296876d650))

## [2.0.1](https://github.com/crtl/request-dto-resolver-bundle/compare/0.0.3...v2.0.1) (2026-02-01)


### ⚠ BREAKING CHANGES

* The DTO lifecycle is now split between resolution and validation phases. DTO properties are no longer hydrated during the resolution phase but after the `kernel.controller_arguments` event.

### Features

* add phpstan and fix issues ([d447869](https://github.com/crtl/request-dto-resolver-bundle/commit/d4478695a6413be81122609429f36c4032bfb860))
* complete rework to support type-safe request DTOs ([#3](https://github.com/crtl/request-dto-resolver-bundle/issues/3)) ([32069d0](https://github.com/crtl/request-dto-resolver-bundle/commit/32069d096144a200164377fcbec0740203a5638e))
* Moves validation logic from value resolver into event subscriber to prevent validation of values before all arguments and security has been resolved completely. ([2a5b29b](https://github.com/crtl/request-dto-resolver-bundle/commit/2a5b29b0a805967d2663772881d0a5be3f0105d7))


### Miscellaneous Chores

* release 2.0.1 ([66be6fe](https://github.com/crtl/request-dto-resolver-bundle/commit/66be6fe693aa8b73d304ca712bb49ee1202a448f))
