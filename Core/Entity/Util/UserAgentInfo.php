<?php

namespace Amora\Core\Entity\Util;

class UserAgentInfo
{
    public function __construct(
        public readonly ?string $platform = null,
        public readonly ?string $browser = null,
        public readonly ?string $version = null,
    ) {}

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
