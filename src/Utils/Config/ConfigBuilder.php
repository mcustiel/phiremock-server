<?php

namespace Mcustiel\Phiremock\Server\Utils\Config;

use Mcustiel\Phiremock\Server\Cli\Options\ExpectationsDirectory;
use Mcustiel\Phiremock\Server\Utils\HomePathService;

class ConfigBuilder
{
    private const DEFAULT_IP = '0.0.0.0';
    private const DEFAULT_PORT = 8086;
    private const DEFAULT_EXPECTATIONS_DIR = '[USER_HOME_PATH]/.phiremock/expectations';

    private static $defaultConfig;

    /** @var Directory */
    private $configPath;

    public function __construct(Directory $configPath)
    {
        if (self::$defaultConfig === null) {
            self::$defaultConfig = [
                'port'             => self::DEFAULT_PORT,
                'ip'               => self::DEFAULT_IP,
                'expectations-dir' => self::getDefaultExpectationsDir()->asString(),
                'debug'            => false,
            ];
        }
        $this->configPath = $configPath;
    }

    public function build(array $cliConfig): Config
    {
        $config = self::$defaultConfig;

        $fileConfiguration = [];

        $configFiles = ['.phiremock', '.phiremock.dist'];

        foreach ($configFiles as $configFileName) {
            $configFilePath = $this->configPath->getFullSubpathAsString($configFileName);
            if (file_exists($configFilePath)) {
                $fileConfiguration = require $configFilePath;
                break;
            }
        }
        $extraKeys = array_diff_key($fileConfiguration, self::$defaultConfig);
        if (!empty($extraKeys)) {
            throw new \DomainException('Extra keys in configuration file: ' . implode(',', $extraKeys));
        }

        var_dump($config, $fileConfiguration, $cliConfig);

        return new Config(array_replace($config, $fileConfiguration, $cliConfig));
    }

    public static function getDefaultExpectationsDir(): ExpectationsDirectory
    {
        return new ExpectationsDirectory(
            HomePathService::getHomePath()->getFullSubpathAsString(
                '.phiremock' . \DIRECTORY_SEPARATOR . 'expectations'
            )
        );
    }
}
