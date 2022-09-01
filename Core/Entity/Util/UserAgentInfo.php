<?php

namespace Amora\Core\Entity\Util;

class UserAgentInfo
{
    public function __construct(
        public readonly ?string $platform,
        public readonly ?string $browser,
        public readonly ?string $version
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            platform: $data['platform'] ?? null,
            browser: $data['browser'] ?? null,
            version: $data['version'] ?? null,
        );
    }

    public function getBrowserAndPlatform(): string
    {
        if (!$this->browser) {
            return 'Unknown';
        }

        $output = $this->browser;
        if ($this->platform) {
            $output .= ', ' . $this->platform;
        }

        return $output;
    }
}
