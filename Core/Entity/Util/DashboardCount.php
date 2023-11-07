<?php

namespace Amora\Core\Entity\Util;

readonly class DashboardCount
{
    public function __construct(
        public int $images,
        public int $files,
        public int $pages,
        public int $blogPosts,
        public int $users,
        public int $albums,
    ) {}
}
