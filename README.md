# Laying
[![Build Status](https://travis-ci.org/arturu/laying.svg?branch=master)](https://travis-ci.org/arturu/laying)

Laying converts an array in YML format into nested XML / HTML tags. Laying facilitates the development and management of templates for the most popular CMS. In addition, laying allows you to manage the structure of XML files. Laying receives in input a YML file that represents the structure of the file to generate and outputs the result.

![alt tag](http://arturu.it/download/laying-docs/yaml_to_html.png)

## Installation

```
composer require arturu/laying
```

## Usage

```
cd vendor/arturu/laying

bin/console render /path/source/page.yml > /path/to/template/page.html
```

For example

```
cd vendor/arturu/laying
```
edit configuration file

```
nano examples/basic-examples/page-standard.yml
```
and exec

```
bin/console render examples/basic-examples/page-standard.yml > /path/to/template/page.html.twig
```

## Documentation
See docs folder for documentation

## License
GPL v3.0 - Read LICENSE file
