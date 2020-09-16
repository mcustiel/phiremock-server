<?php

/**
 * This file is part of Phiremock.
 *
 * Phiremock is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Phiremock is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Phiremock.  If not, see <http://www.gnu.org/licenses/>.
 */
use Mcustiel\Phiremock\Server\Cli\Commands\PhiremockServerCommand;
use Symfony\Component\Console\Application;

const IS_SINGLE_COMMAND = true;

if (PHP_SAPI !== 'cli') {
    throw new RuntimeException('This is a standalone CLI application');
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    $loader = require __DIR__ . '/../vendor/autoload.php';
} else {
    $loader = require __DIR__ . '/../../../autoload.php';
}

$application = new Application('Phiremock', '');
$phiremockServerCommand = new PhiremockServerCommand();
$application->add($phiremockServerCommand);
$application->setDefaultCommand($phiremockServerCommand->getName(), IS_SINGLE_COMMAND);
$application->run();
