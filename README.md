# Laying
[![Build Status](https://travis-ci.org/arturu/laying.svg?branch=master)](https://travis-ci.org/arturu/laying)

YAML to XML/HTML file

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
gedit examples/basic-examples/page-standard.yml
```
and exec

```
bin/console render examples/basic-examples/page-standard.yml > /path/to/template/page.html.twig
```

## License
GPL v3.0 - Read LICENSE file