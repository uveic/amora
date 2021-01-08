<?php

namespace uve\core\model\util;

class UserAgentInfo
{
    private ?string $platform;
    private ?string $browser;
    private ?string $version;

    public function __construct(
        ?string $platform,
        ?string $browser,
        ?string $version
    ) {
        $this->platform = $platform;
        $this->browser = $browser;
        $this->version = $version;
    }

    public static function fromArray(array $data): self
    {
        return new self(
            $data['platform'] ?? null,
            $data['browser'] ?? null,
            $data['version'] ?? null
        );
    }

    public function asArray(): array
    {
        return [
            'platform' => $this->getPlatform(),
            'browser' => $this->getBrowser(),
            'version' => $this->getVersion()
        ];
    }

    public function getPlatform(): ?string
    {
        return $this->platform;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function getBrowserAndPlatform(): string
    {
        if (!$this->getBrowser()) {
            return 'Unknown';
        }

        $output = $this->getBrowser();
        if ($this->getPlatform()) {
            $output .= ', ' . $this->getPlatform();
        }

        return $output;
    }
}
