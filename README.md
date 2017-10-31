# Laying
YAML to HTML template

![alt tag](http://arturu.it/download/laying-docs/yaml_to_html.png)

## Installation

```
composer require arturu/laying --dev
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
gedit examples/basic-examples/page.yml
```
and exec

```
bin/console render examples/basic-examples/page.yml > /path/to/template/page.html.twig
```

## License
GPL v3.0 - Read LICENSE file