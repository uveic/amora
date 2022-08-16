<?php

namespace Amora\Core\Config;

use Amora\App\Value\Language;

enum Env {
    case Dev;
    case Live;
    case Staging;
}

final class Database {
    public function __construct(
        public readonly string $host = 'localhost',
        public readonly string $user = 'user',
        public readonly string $password = 'pass',
        public readonly string $name = 'amora',
    ) {}
}

final class DatabaseBackup {
    public function __construct(
        public readonly string $mysqlCommandPath = 'mysql',
        public readonly string $mysqlDumpCommandPath = 'mysqldump',
        public readonly string $gzipCommandPath = 'gzip',
        public readonly string $folderPath = '/tmp/',
    ) {}
}

final class MailerSendGrid
{
    public function __construct(
        public readonly string $apiKey = '',
        public readonly string $baseApiUrl = 'https://api.sendgrid.com/v3',
    ) {}
}

final class Email
{
    public function __construct(
        public readonly string $email = '',
        public readonly string $name = '',
    ) {}
}

final class Mailer {
    public function __construct(
        public readonly Email $from,
        public readonly Email $replyTo,
        public readonly MailerSendGrid $sendGrid,
        public readonly bool $sendEmailSynchronously = false,
    ) {}
}

abstract class AbstractConfig {
    public function __construct(
        public readonly string $appName,
        public readonly bool $isLoggingEnabled,
        public readonly bool $isActionLoggingEnabled,
        public readonly bool $isRegistrationEnabled,
        public readonly bool $isInvitationEnabled,
        public readonly Env $env,

        public readonly string $siteAdminEmail,
        public readonly string $siteAdminName,

        public string $timezone,
        public readonly string $phpLocale,
        public readonly Language $defaultSiteLanguage,
        public readonly array $allSiteLanguages,

        public readonly string $baseUrl,
        public readonly string $siteImageUrl,
        public readonly string $logoImageUrl,
        public readonly string $mediaBaseDir,
        public readonly string $mediaBaseUrl,

        public readonly DatabaseBackup $databaseBackup,

        public readonly Database $coreDb,
        public readonly Database $actionDb,
        public readonly Database $mailerDb,

        public readonly Mailer $mailer,
    ) {}

    abstract public static function get(): AbstractConfig;
}
