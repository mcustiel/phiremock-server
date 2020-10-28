<?php

namespace Mcustiel\Phiremock\Server\Utils\Config;

use DomainException;
use Exception;
use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Factory\Factory;
use Mcustiel\Phiremock\Server\Utils\HomePathService;

class ConfigBuilder
{
    private const DEFAULT_IP = '0.0.0.0';
    private const DEFAULT_PORT = 8086;

    /** @var array */
    private static $defaultConfig;

    /** @var Directory */
    private $configPath;

    /** @throws Exception */
    public function __construct(Directory $configPath)
    {
        if (self::$defaultConfig === null) {
            self::$defaultConfig = [
                'port'             => self::DEFAULT_PORT,
                'ip'               => self::DEFAULT_IP,
                'expectations-dir' => self::getDefaultExpectationsDir()->asString(),
                'debug'            => false,
                'factory-class'    => Factory::class,
            ];
        }
        $this->configPath = $configPath;
    }

    /** @throws Exception */
    public function build(array $cliConfig): Config
    {
        $config = self::$defaultConfig;

        $fileConfiguration = [];

        $configFiles = [
            __DIR__ . '/../../../../../../.phiremock',
            __DIR__ . '/../../../../../../.phiremock.dist',
            __DIR__ . '/../../../.phiremock',
            __DIR__ . '/../../../.phiremock.dist',
            getcwd() . '/.phiremock',
            getcwd() . '/.phiremock.dist',
            HomePathService::getHomePath()->getFullSubpathAsString(
                '.phiremock' . DIRECTORY_SEPARATOR . 'config'
            ),
            '.phiremock',
            '.phiremock.dist',
        ];

        foreach ($configFiles as $configFileName) {
            $configFilePath = $this->configPath->getFullSubpathAsString($configFileName);
            if (file_exists($configFilePath)) {
                $fileConfiguration = require $configFilePath;
                break;
            }
        }
        $extraKeys = array_diff_key($fileConfiguration, self::$defaultConfig);
        if (!empty($extraKeys)) {
            throw new DomainException('Extra keys in configuration file: ' . implode(',', $extraKeys));
        }

        return new Config(array_replace($config, $fileConfiguration, $cliConfig));
    }

    /** @throws Exception */
    public static function getDefaultExpectationsDir(): ExpectationsDirectory
    {
        return new ExpectationsDirectory(
            HomePathService::getHomePath()->getFullSubpathAsString(
                '.phiremock' . DIRECTORY_SEPARATOR . 'expectations'
            )
        );
    }
}
