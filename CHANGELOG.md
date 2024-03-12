# Changelog

## [1.3.1] - 13-03-2024

### Fixed
- Fetch text content
- Reset selected the package in the profile form

## [1.3.0] - 11-03-2024

### Added
- Search Profiles

### Changed
- Minor changed the visual style the card of the packages
- Digua Framework updated to version 1.6.0

### Fixed
- Checking enabled "search package" for search
- Navigation progress animation (*Visual)
- Modal windows overlapped the notifications (*Visual)
- The package preloading (*Vue)

## [1.2.0] - 30-01-2024

### Added
- Search package "Jackett"
- Ability to specify the torrent hash and magnet uri
- Ability to pre-specify content for an item
- Ability to check package availability
- Ability to add custom settings for packages
- Additional parameters "params" in the search and fetch query
- Preliminary restore of content from cache

### Changed
- API variable "content" renamed to "description"
- Now package method fetch accept object Query instead of string id just like the method "search"
- Cache storage key. The key now depends on the version of the Cache class. No need to reset cache to prevent errors
- Search will be performed only for packages that returned the available status
- Visual state of a package when it changes (enabled, available, etc...)

### Fixed
- Undefined constant DOCUMENT_ROOT for worker
- Special chars decode query for fetch and search actions
- Name of the content created file cannot be empty

## [1.1.1] - 24-01-2024

### Added
- Route dispatcher event containing request and route builder

### Changed
- Extension wakeup moved before route dispatcher

### Fixed
- Fetch torrent file

## [1.1.0] - 03-12-2023

### Added

- Support worker wss (ssl) transport protocol


## [1.0.0] - 03-12-2023

Initial Release