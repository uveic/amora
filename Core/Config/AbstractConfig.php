<?php

namespace Amora\Core\Config;

use Amora\App\Value\Language;

enum Env {
    case Dev;
    case Live;
    case Staging;
}

final readonly class Database {
    public function __construct(
        public string $host = 'localhost',
        public string $user = 'user',
        public string $password = 'pass',
        public string $name = 'amora',
    ) {}
}

final readonly class DatabaseBackup {
    public function __construct(
        public string $mysqlCommandPath = 'mysql',
        public string $mysqlDumpCommandPath = 'mysqldump',
        public string $gzipCommandPath = 'gzip',
        public string $folderPath = '/tmp/',
    ) {}
}

final readonly class MailerSendGrid
{
    public function __construct(
        public string $apiKey = '',
        public string $baseApiUrl = 'https://api.sendgrid.com/v3',
    ) {}
}

final readonly class Email
{
    public function __construct(
        public string $email = '',
        public string $name = '',
    ) {}
}

final readonly class Mailer {
    public function __construct(
        public Email $from,
        public Email $replyTo,
        public MailerSendGrid $sendGrid,
        public bool $sendEmailSynchronously = false,
    ) {}
}

abstract class AbstractConfig {
    public function __construct(
        public readonly string $appName,
        public readonly Env $env,
        public readonly string $salt,
        public readonly ?string $hiddenSiteToken,
        public readonly array $allowedUrlsForSrcScript,

        public readonly bool $isLoggingEnabled,
        public readonly bool $isAnalyticsEnabled,
        public readonly bool $isRegistrationEnabled,
        public readonly bool $isInvitationEnabled,
        public readonly bool $isSearchEnabled,

        public readonly string $siteAdminEmail,
        public readonly string $siteAdminName,

        public string $timezone,
        public readonly string $phpLocale,
        public readonly Language $defaultSiteLanguage,
        public readonly array $allSiteLanguages,

        public readonly string $baseUrl,
        public readonly string $siteImageUrl,
        public readonly string $logoImageUrl,
        public readonly string $siteIcon512pixels,
        public readonly string $siteIcon64pixels,
        public readonly string $themeColourHex,

        public readonly string $mediaBaseDir,
        public readonly string $mediaBaseUrl,

        public readonly DatabaseBackup $databaseBackup,

        public readonly Database $coreDb,
        public readonly Database $analyticsDb,
        public readonly Database $mailerDb,

        public readonly Mailer $mailer,
    ) {}

    abstract public static function get(): AbstractConfig;
}
