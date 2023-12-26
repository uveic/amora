<?php

namespace Amora\Core\Entity\Util;

readonly class UserAgentInfo
{
    public function __construct(
        public ?string $platform = null,
        public ?string $browser = null,
        public ?string $version = null,
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
