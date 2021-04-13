# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [3.1.0] - 2021-04-13

### Changed

- Adjusted to use Defer 3.x

## [3.0.2] - 2021-01-07

## [3.0.1] - 2021-01-06

### Fixed

- Fixed error when copying on PHP 8 if folder already exists

## [3.0.0] - 2021-01-04

### Changed

- Changed minimum PHP version to 7.3
- Changed minimum PHPUnit to 9.2
- Changed defer to use v2

## [2.0.0] - 2020-09-26

### Changed

- Changed Folder::list now returns an array of FileObjects
- Changed Folder::list FileObject uses directory instead of path (BC)
- Changed File::info filename has been renamed to name (BC)
- Changed File::info path has been renamed to directory (BC)

## [1.0.0] - 2019-10-11

This component has been decoupled from the [OriginPHP framework](https://www.originphp.com/).
