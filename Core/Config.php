<?php

namespace Amora\Core;

use Exception;

final class Config
{
    const CONFIG_PATH_DEFAULT = '/Core/config/default.php';
    const CONFIG_PATH_LOCAL = '/App/config/local.php';

    private string $configFilePath;
    private string $localConfigFilePath;
    private array $config = [];

    /**
     * Config constructor.
     * @param string|null $defaultConfigPath
     * @param string|null $localConfigPath
     * @throws Exception
     */
    public function __construct(?string $defaultConfigPath = null, ?string $localConfigPath = null)
    {
        $this->configFilePath = $defaultConfigPath
            ? realpath($defaultConfigPath)
            : realpath(Core::getPathRoot() . self::CONFIG_PATH_DEFAULT);
        $this->localConfigFilePath = $localConfigPath
            ? realpath($localConfigPath)
            : realpath(Core::getPathRoot() . self::CONFIG_PATH_LOCAL);

        if (!file_exists($this->configFilePath)) {
            $this->configFilePath = realpath(Core::getPathRoot() . self::CONFIG_PATH_LOCAL);
        }

        if (!file_exists($this->configFilePath)) {
            throw new Exception(
                'Default config file not found: ' . $this->getDefaultConfigFilePath()
            );
        }

        if (!file_exists($this->localConfigFilePath)) {
            $this->localConfigFilePath = realpath(Core::getPathRoot() . self::CONFIG_PATH_LOCAL);
        }
    }

    public function getDefaultConfigFilePath(): string
    {
        return $this->configFilePath;
    }

    public function getLocalConfigFilePath(): string
    {
        return $this->localConfigFilePath;
    }

    public function getConfig(): array
    {
        if (!empty($this->config)) {
            return $this->config;
        }

        $this->loadConfig();
        return $this->config;
    }

    public function get(string $key)
    {
        if (empty($this->config)) {
            $this->loadConfig();
        }

        return $this->config[$key] ?? null;
    }

    private function loadConfig()
    {
        $defaultConfig = require $this->getDefaultConfigFilePath();
        if (empty($defaultConfig)) {
            echo 'Failed to load default config file';
            die;
        }

        $this->config = $defaultConfig;
        $localConfig = require_once $this->getLocalConfigFilePath();
        if (!empty($localConfig)) {
            // Add values present in localConfig but not present in defaultConfig
            // ToDo: make it work for nested values/arrays
            foreach ($localConfig as $key => $value) {
                $this->add($key, $value, $this->config);
            }

            foreach ($this->config as $key => $value) {
                if (isset($localConfig[$key])) {
                    $this->replace($this->config[$key], $localConfig[$key]);
                }
            }
        }
    }

    private function replace(&$a, $b)
    {
        if (is_array($a) && is_array($b)) {
            foreach ($a as $key => $value) {
                if (isset($b[$key])) {
                    $this->replace($a[$key], $b[$key]);
                }
            }
        } else {
            $a = $b;
        }
    }

    private function add($key, $value, &$parent): void
    {
        if (!isset($parent[$key])) {
            $parent[$key] = $value;
            return;
        }

        if (is_array($parent[$key])) {
            foreach ($parent[$key] as $a => $b) {
                $this->add($a, $b, $parent[$key]);
            }
        }
    }
}
