# Introduction
Laying converts an array in YML format into nested XML / HTML tags. Laying facilitates the development and management of templates for the most popular CMS. In addition, laying allows you to manage the structure of XML files. Laying receives in input a YML file that represents the structure of the file to generate and outputs the result.

## Installation
```
composer require arturu/laying
```

## Use
To get the output you have to use the laying console
```
cd path/to/vendor/arturu/laying
bin/console render /path/to/source/page.yml > /path/to/output/page.html.twig
```

## Structure of the YML file
The source file consists of two main elements: the configuration array and one or more arrays that relate the output. The configuration array is not mandatory.

### Structure array
The output is represented by the following structure:
```
elementID: # Unique name. Mandatory
  type: div|header|nav|footer|ecc|null # Optional.
  implicit: " />"|"?>"|">"|ecc # Optional.
  injectTag: "Raw string" # Optional
  attributes: # Optional.
    id: custom|null # Optional.
    class: custom|null # Optional.
    foo: bar # Optional.
    ...
  regionsContent: # Optional.
    - 'Raw text' # Optional.
    - { file: 'path/relative/file.ext', parseMode: 'yml|raw' } # Optional. Nested laying output.
    ... # list regionsContent
  items: # Optional. One or more nested elementIDs
    elementID2:
        ...
        elementID21
            ...
    elementID3
        ...
```

#### ElementID
| Name             | Description                                                                                                                                                                                                                                                                                 | Mandatory |                   Default                   | Value                                               |
|------------------|---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|:---------:|:-------------------------------------------:|-----------------------------------------------------|
| elementID        | Unique name of tag                                                                                                                                                                                                                                                                          |    yes    |                                             | A string key in the camelCase format                    |
| type             | If set the output will be "\<type ...\>...\<\/type\>". DefaultType (see conf chapter) will be used if not set, example "\<div ... \>...\<\/div\>". If you set null, the tag name will not be rendered, for example "\< ... \>...\<\/\>". "type: null" is only used if you need to render a doctype or xml header |     no    | "div" - See defaultType in the conf chapter | String: "div" "header" "nav" "footer" "custom" null |
| implicit         | Set this parameter if it is an implicit tag, example "\<type ... \/\>". If type:"img" and implicit is " />" the output will be "\<img ... \/\>". If type:null and implicit is ">" the output will be "\< ... \>". If type:"?xml" and implicit is "?>" the output will be "\<?xml ... ?\>".  |     no    |                                             | String closure: " />", "?>", ">"                    |
| injectTag        | Enter raw text inside the opening tag. If injectTag:"{{Raw text}}" the output will be "\<type ... {{Raw text}}>...\<\/type\>". Note: if injectTag is !== false, compressOutput will be set to false                                                                                         |     no    |                                             | String                                              |
| attributes       | Multidimensional array that contains key pairs and values that represent tag attributes. See attributes chapter.                                                                                                                                                                            |     no    |                                             | array                                               |
| regionsContent   | Multidimensional array that contains region content. See regionsContent chapter.                                                                                                                                                                                                            |     no    |                                             | array                                               |
| items            | One or more nested elementIDs                                                                                                                                                                                                                                                               |     no    |                                             | array                                               |

#### Attributes
...

#### regionsContent
...

#### Items
...

### Structure conf
Default settings are defined in "src/conf/settings.yml".

| Name              	| Description                                                                                                                                 	| Mandatory 	|       Default      	| Value                                  	|
|-------------------	|---------------------------------------------------------------------------------------------------------------------------------------------	|:---------:	|:------------------:	|----------------------------------------	|
| layoutName        	| Machine-name of layout                                                                                                                      	|     no    	| "default-settings" 	| String                                 	|
| description       	| Description                                                                                                                                 	|     no    	| "Default settings" 	| String                                 	|
| defaultType       	| If type is not defined then this will be used. If null is set, nothing will be rendered.                                                    	|     no    	|        "div"       	| div, header, nav, footer, custom, null 	|
| idAuto            	| Automatically generates the id attribute.                                                                                                   	|     no    	|        true        	| Boolean                                	|
| classAuto         	| Automatically generates the class attribute.                                                                                                	|     no    	|        true        	| Boolean                                	|
| wrapper           	| Inserts a container that contains the element to be rendered.                                                                               	|     no    	|        false       	| Boolean                                	|
| wrapperClass      	| The class that will be added to each wrapper element if "classAuto: true"                                                                   	|     no    	|      "wrapper"     	| String                                 	|
| wrapperPrefix     	| The class and id prefix if "classAuto: true" or "idAuto: true"                                                                              	|     no    	|         ""         	| String                                 	|
| wrapperSuffix     	| The suffix of classes and ids if "classAuto: true" or "idAuto: true"                                                                        	|     no    	|     "-wrapper"     	| Custom string                          	|
| element           	| Container of the item to render                                                                                                             	|     no    	|        true        	| Bool                                   	|
| elementClass      	| The class that will be added to each element if "classAuto: true"                                                                           	|     no    	|         ""         	| String                                 	|
| elementPrefix     	| The class and id prefix if "classAuto: true" or "idAuto: true"                                                                              	|     no    	|         ""         	| String                                 	|
| elementSuffix     	| The suffix of classes and ids if "classAuto: true" or "idAuto: true"                                                                        	|     no    	|         ""         	| String                                 	|
| inner             	| Inserts a container inside the element to be generated                                                                                      	|     no    	|        false       	| Bool                                   	|
| innerClass        	| The class that will be added to each inner element if "classAuto: true"                                                                     	|     no    	|       "inner"      	| String                                 	|
| innerPrefix       	| The class and id prefix if "classAuto: true" or "idAuto: true"                                                                              	|     no    	|         ""         	| String                                 	|
| innerSuffix       	| The suffix of classes and ids if "classAuto: true" or "idAuto: true"                                                                        	|     no    	|      "-inner"      	| String                                 	|
| region            	| Container of the single region, it is advisable to always leave on true except very special cases                                           	|     no    	|        true        	| Bool                                   	|
| renderRegionFirst 	| If items and regions are defined on the same level, the region region will be rendered before the items if this value is "true"             	|     no    	|        true        	| Bool                                   	|
| regionClass       	| The class that will be added to each region element if "classAuto: true"                                                                    	|     no    	|      "region"      	| String                                 	|
| regionPrefix      	| The class and id prefix if "classAuto: true" or "idAuto: true"                                                                              	|     no    	|         ""         	| String                                 	|
| regionSuffix      	| The suffix of classes and ids if "classAuto: true" or "idAuto: true"                                                                        	|     no    	|         ""         	| String                                 	|
| debugBlock        	| Content,block rendered after the region, it is useful in development as it,displays the page structure with no content generated by the CMS 	|     no    	|        false       	| Bool                                   	|
| debugBlockOnly    	| Show only the debug block                                                                                                                   	|     no    	|        false       	| Bool                                   	|
| debugBlockClass   	| The class that will be added to each debugBlock element if "classAuto: true"                                                                	|     no    	|  "debugBlock well" 	| String                                 	|
| debugBlockPrefix  	| The class and id prefix if "classAuto: true" or "idAuto: true"                                                                              	|     no    	|         ""         	| String                                 	|
| debugBlockSuffix  	| The suffix of classes and ids if "classAuto: true" or "idAuto: true"                                                                        	|     no    	|    "-debugBlock"   	| String                                 	|
| compressOutput    	| For compress output set "true". For indent output set "false".                                                                              	|     no    	|        false       	| Boolean                                	|
| compressOutputAutoDeactivate | Turn true compressOutput if injectTag is setting, there are issue with tidy.                                                           |     no    	|        true       	| Boolean                                	|
| compressOption    	| See tidy configuration http://api.html-tidy.org/                                                                              	            |     no    	|                   	| tidy array conf                          	|
