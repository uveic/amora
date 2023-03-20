<?php

namespace Amora\Core\Module\Analytics\Value;

enum CountDbColumn: string
{
    case Page = 'er.url';
    case Country = 'ep.country_iso_code';
    case Device = 'ep.user_agent_platform';
    case Browser = 'ep.user_agent_browser';
    case Language = 'ep.language_iso_code';
    case Referrer = 'ep.referrer';
    case Visitor = 'ep.user_hash';
}
