# Laying
![alt tag](http://arturu.it/download/laying-docs/yaml_to_html.png)

## Installation

```
composer require arturu/laying:dev-master
```

## Usage

```
cd vendor/arturu/laying

php bin/console render /path/source/page.yml > /path/to/template/page.html
```

For example

```
cd vendor/arturu/laying
```
edit configuration file

```
gedit examples/page.yml
```
and exec

```
php bin/console render examples/page.yml > /path/to/template/page.html.twig
```

## License
GPL v3.0