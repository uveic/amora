<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Value\UserJourneyStatus;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

final class UserHtmlGenerator
{
    public static function generateUserRowHtml(
        HtmlResponseDataAbstract $responseData,
        User $user,
        string $indentation = '        ',
    ): string {
        $userEditUrl = UrlBuilderUtil::buildBackofficeUserUrl(
            language: $responseData->siteLanguage,
            userId: $user->id,
        );

        $userTitleHtml = '<a href="' . $userEditUrl . '">' . $user->getNameOrEmail() . '</a>';

        $userDate = DateUtil::formatDate(
            date: $user->createdAt,
            lang: $responseData->siteLanguage,
            includeTime: true,
        );

        $output = [];
        $output[] = $indentation . '<div class="table-row">';
        $output[] = $indentation . '  <div class="table-item">';
        $output[] = $indentation . '    <span class="light-text-color font-0-9">#' . $user->id . '</span>';
        $output[] = $indentation . '    ' . $userTitleHtml;
        $output[] = $indentation . '    <span>' . CoreIcons::ENVELOPE_SIMPLE . $user->email . '</span>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="table-item flex-no-grow">';
        $output[] = $indentation . '    <span class="article-status ' . $user->status->getClassname() . '">' . $responseData->getLocalValue('userStatus' . $user->status->name) . '</span>';
        if ($user->journeyStatus !== UserJourneyStatus::Registration) {
            $journeyClassname = match ($user->journeyStatus) {
                UserJourneyStatus::Registration => 'status-published',
                UserJourneyStatus::PendingPasswordCreation => 'status-draft',
            };

            $output[] = $indentation . '    <span class="article-status ' . $journeyClassname . '">' .  $responseData->getLocalValue('userJourney' . $user->journeyStatus->name) . '</span>';
        }
        $output[] = $indentation . '    <span class="' . ($user->role === UserRole::Admin ? 'is-highlighted' : '') .'">' .  $responseData->getLocalValue('userRole' . $user->role->name) . '</span>';
        $output[] = $indentation . '    <div>' . CoreIcons::CALENDAR_CHECK .  $userDate . '</div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
