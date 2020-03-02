# yii2-jsloader-systemjs

[![Latest Stable Version](https://poser.pugx.org/ischenko/yii2-jsloader-systemjs/v/stable)](https://packagist.org/packages/ischenko/yii2-jsloader-systemjs)
[![Total Downloads](https://poser.pugx.org/ischenko/yii2-jsloader-systemjs/downloads)](https://packagist.org/packages/ischenko/yii2-jsloader-systemjs)
[![Build Status](https://travis-ci.com/ischenko/yii2-jsloader-systemjs.svg?branch=master)](https://travis-ci.com/ischenko/yii2-jsloader-systemjs)
[![Code Climate](https://codeclimate.com/github/ischenko/yii2-jsloader-systemjs/badges/gpa.svg)](https://codeclimate.com/github/ischenko/yii2-jsloader-systemjs)
[![Test Coverage](https://codeclimate.com/github/ischenko/yii2-jsloader-systemjs/badges/coverage.svg)](https://codeclimate.com/github/ischenko/yii2-jsloader-systemjs/coverage)
[![License](https://poser.pugx.org/ischenko/yii2-jsloader-systemjs/license)](https://packagist.org/packages/ischenko/yii2-jsloader-systemjs)

An Yii2 extension that allows to register asset bundles as [systemjs](https://github.com/systemjs/systemjs) modules.

## Installation
*Requires PHP >= 7.1

*Requires [ischenko/yii2-jsloader](https://github.com/ischenko/yii2-jsloader) >= 1.2

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run
```
composer require ischenko/yii2-jsloader-systemjs
```

or add

```json
"ischenko/yii2-jsloader-systemjs": "*"
```

to the `require` section of your composer.json.

## Usage

Add the [behavior](https://github.com/ischenko/yii2-jsloader#usage) and systemjs loader to the view configuration

```php
    ...
    'components' => [
        ...
        'view' => [
            'as jsLoader' => [
                'class' => 'ischenko\yii2\jsloader\Behavior',
                'loader' => [
                    'class' => 'ischenko\yii2\jsloader\SystemJs',
                ]
            ]
        ]
        ...
    ]
    ...
```

Loader accepts the following options:
 - **extras**: *array* a list of systemJs extras to load. Possible values are:
    - amd
    - transform
    - named-exports
    - named-register
    - global
    - module-types 
 - **minimal**: *boolean* indicates whether to load core version (s.js) or full version (system.js) of the library
 - **position**: *integer* a position where the library and import map should be added to
 - **renderer**: *string*|*array* configuration for the JS expressions renderer object

 ```php
     ...
     'components' => [
         ...
         'view' => [
             'as jsLoader' => [
                 'class' => 'ischenko\yii2\jsloader\Behavior',
                 'loader' => [
                     'minimal' => false,
                     'extras' => ['amd'],
                     'position' => \yii\web\View::POS_HEAD,
                     'class' => 'ischenko\yii2\jsloader\SystemJs',
                 ]
             ]
         ]
         ...
     ]
     ...
 ```

**Please note** that aliases works only within client-side code. On server-side you still need to operate with actual module names.
