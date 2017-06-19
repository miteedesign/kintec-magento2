# Change Log

## [9.0.1] 2017-03-01
### Fixed
- Pass through reset param for filename helper

## [9.0.0] 2017-02-27
### Added
- Emit event in PdfRenderer

## [8.1.0] 2017-02-23
### Added
- Checkbox Column

## [8.0.2] 2017-02-13
### Added
- Allow filename to be reset
### Fixed
- Product attribute values on non order pdfs

## [8.0.1] 2017-02-06
### Fixed
- Alternative approach for cron translation fix

## [8.0.0] 2017-02-04
### Added
- Add ability to set a row background color
### Fixed
- Explicitly revert emulated design
- Use Image call directly to benefit from automatic scaling
- Workaround for Magento 2 bug to ensure translation of email pdfs via cron
  (Changed DocumentRenderer constructor)

## [7.0.1] 2016-12-23
### Fixed
- Footer block template can be themed
### Changed
- Allow plugin column listing

## [7.0.0] 2016-12-20
### Added
- New getForcedPageOrientation() method for DocumentRendererInterface
- Tax Percentage Column

## [6.1.2] 2016-11-12
### Fixed
- Default fallback for Custom column titles

## [6.1.1] 2016-11-11
### Fixed
- Tax column uses new currency renderer

## [6.1.0] 2016-11-06
### Added
- Custom titles for columns
### Fixed
- Price column now follows Magento setting
- Magento changed currency renderer

## [6.0.0] 2016-10-19
### Changed
- Document Renderer now uses Template directly instead of via a Factory
### Added
- Subtotal excluding tax column

## [5.0.1] 2016-09-22
### Fixed
- Check to exclude some product attributes

## [5.0.0] 2016-09-15
### Added
- Background images
### Fixed
- Some settings were not multistore capable 
- getStoreId() added to DocumentRendererInterface

## [4.1.0] 2016-08-26
### Added
- Support Product Attribute Column

## [4.0.1] 2016-08-19
### Fixed
-  show column extras in templates/pdf/table.html, adjusted spacing

## [4.0.0] 2016-07-25
### Added
- Support for integrated labels
- Ability to restore default values
### Changed
- Compatibility with Magento 2.1, for Magento 2.0 use earlier versions
- Columns setting field HtmlId is not random anymore

## [3.0.6] 2016-06-14
### Fixed
- Multi store capability of footer blocks

## [3.0.5] 2016-06-09
### Fixed
-  Version Number

## [3.0.4] 2016-06-09
### Fixed
- Allow setting of currency for table columns
- Compatibility with Magento 2.1.0-rc1
- Page break on table rows
- Constant line height on images
### Changed
- Run footer through template engine

## [3.0.3] 2016-03-25
### Fixed
- Composer.json for registration and dependency declaration of core modules
- M2 code style

## [3.0.2] 2016-03-24
### Fixed
- Extra second composer.json to support Magento Setup UI

## [3.0.1] 2016-03-13
### Fixed
- Code Style improvements

## [3.0.0] 2016-02-22
### Changed
- Initial Release for Magento 2
