# Changelog

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
