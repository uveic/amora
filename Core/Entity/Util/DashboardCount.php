<?php

namespace Amora\Core\Entity\Util;

class DashboardCount
{
    public function __construct(
        public readonly int $images,
        public readonly int $files,
        public readonly int $pages,
        public readonly int $blogPosts,
        public readonly int $users,
    ) {}
}
