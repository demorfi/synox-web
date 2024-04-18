# Changelog

## [1.4.0] - 19-04-2024

### Fixed
- (Vue) separator for "Requires" list (*Visual)
- (Vue) not enough space for empty profile selector (*Visual)
- (Vue) props type seeds and peers in search result item
- (Vue) validation magnet link in search result item

### Added
- JackettApiHook extension package
- Indexer extension package (*Elastic)
- Indexer search extension package (*Elastic)
- Base validation package
- Ability to upload/update package by gui (*Only in dev mode)
- Ability to define the activity (use) of the package (*Extension)
- Elasticsearch service (*docker-compose)
- Additional compose profiles (*docker-compose)

### Changed
- Package load style is now dynamic. all packages look like (Type@Subtype$Name)
- There is no longer any need to enable the package to process a (fetch) request
- Package availability is checked only if the package is enabled
- Timeout connection for redis default set by 1.5 sec
- Digua updated to version 1.7
- Cache extension updated to version 1.1

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