#!/usr/bin/env bash

rm -rf vendor

composer clearcache
composer global update

mv composer.json composer.json.bkp
cp composer.phar.json composer.json

composer update --no-dev --optimize-autoloader

box compile -vvv

mv composer.json.bkp composer.json
