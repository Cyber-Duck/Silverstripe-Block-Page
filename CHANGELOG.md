#Change Log

All notable changes to this project will be documented in this file.

## Version 4.6.2
14th October 2020

### Removed
  - Removed page_length setting to allow default page-length in ModelAdmin (current default: 30 records per page)

## Version 4.6.1
24th September 2020

### Added
  - Added new function getPreviewImagePath which enables you to update the preview 
  image path depending on your project's folder setup or simply specify the `preview` class config value with 
  the relative path to the preview image for the respective content block.
  
### Changed
  - Updated extra requirements path for css and js in cms.yml
    
## Version 1.1.0

31st January 2017

Contains breaking changes to version 1.0.*

### Added

  - Allow blocks on any DataObject
  - Unstable badge in README

### Changed

  - Fix for restrict block types
  - Change Name field to Title in ContentBlock

## Version 1.0.7

7th January 2017

### Added

  - Option to restrict block types per Page

### Changed

  - Update conposer require
  - Better PHP comments
  - Updated LICENSE text

## Version 1.0.6

24th November 2016

### Added
  
  - CHANGELOG file
  - .scrutinizer file
  - .gitattributes file
  - .gitignore file
  - .editorconfig
  - CONTRIBUTING file
  - travis file

### Changed
  
  - Update README
  - Changed installer name in composer
  - Removed blockpage.yml file
  - Update LICENSE year

## Version 1.0.5

5th December 2016

### Added
  
  - Add permissions to ContentBlock

## Version 1.0.4

5th December 2016

### Added
  
  - Add Sortable Gridfield module to composer require

## Version 1.0.3

2nd December 2016

### Changed
  
  - Fix issue with name field not appearing on ContentBlock

## Version 1.0.2

29th November 2016

### Changed
  
  - Remove CssSelector field from ContentBlock

## Version 1.0.1

29th November 2016

### Added
  
  - Add BlockSort as $default_sorting for ContentBlock

## Version 1.0.0

24th November 2016

### Added
  
  - Core functionality