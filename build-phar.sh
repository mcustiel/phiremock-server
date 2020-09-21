#!/usr/bin/env bash

COMPOSER=composer.phar.json composer update --no-dev --optimize-autoloader --prefer-stable

box build

