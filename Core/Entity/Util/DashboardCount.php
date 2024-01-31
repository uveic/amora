<?php

namespace Amora\Core\Entity\Util;

readonly class DashboardCount
{
    public function __construct(
        public int $images = 0,
        public int $files = 0,
        public int $pages = 0,
        public int $blogPosts = 0,
        public int $users = 0,
        public int $albums = 0,
        public int $visitorsToday = 0,
        public int $pageViewsToday = 0,
    ) {}
}
