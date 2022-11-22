# ElogicCo_MagentoImport

## How to install
`composer require elogic-co/magento-import`

## Functionality
- Database data provider/importer
- Csv data provider/importer
- Remember failled data-item and ability to continue importing from this remembered data-item (`ElogicCo\MagentoImport\Api\DataImporter\ImporterRememberInterface`)
- Progress bar (Doesn't work for chsv import yet)
![image](https://user-images.githubusercontent.com/64845469/195119397-08e732cc-3d02-47a2-901e-78ad41ae522b.png)
- Mapping for tablelike data import
![image](https://user-images.githubusercontent.com/64845469/195119862-f83ab116-defa-4e55-a0a4-dd57250a3627.png)
- Call Observer during importing for each data-item, so that you can change data-item's columns or even add additional columns to the data-item for import.
![image](https://user-images.githubusercontent.com/64845469/195115569-8fd53c18-2861-4d3c-8953-e8b4d96aca56.png)

## My notes
I have problems with naming programming entities. To deal with this issue I have to:
1. Read books about programming architecture in English
2. Push my code to a much more relevant programmer to hear advices from them. I think it has even  more influence than reading books
3. Work with different projects, not with Magento only to see many ways to name programming entities
