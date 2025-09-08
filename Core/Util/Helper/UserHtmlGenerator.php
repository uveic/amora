<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

final class UserHtmlGenerator
{
    public static function generateUserStatusHtml(UserStatus $status): string
    {
        return '<span class="article-status m-t-0 '
            . $status->getClassName() . '">'
            . $status->getIcon()
            . $status->getName()
            . '</span>';
    }

    public static function generateDynamicUserStatusHtml(
        User $user,
        string $indentation = '',
    ): string {
        $dropdownIdentifier = 'user-status';
        $uniqueDropdownIdentifier = $dropdownIdentifier . '-' . $user->id;

        $output = [
            $indentation . '<input type="checkbox" id="' . $uniqueDropdownIdentifier . '-dd-checkbox" class="dropdown-menu">',
            $indentation . '<div class="dropdown-container ' . $uniqueDropdownIdentifier . '-container">',
            $indentation . '  <ul>',
        ];

        foreach (UserStatus::getAll() as $item) {
            $statusClassname = $item->getClassName();
            $icon = $item->getIcon();
            $output[] = $indentation . '    <li><a data-checked="' . ($user->status === $item ? '1' : '0') . '" data-value="' . $item->value . '" class="dropdown-menu-option ' . $dropdownIdentifier . '-dd-option no-loader ' . $statusClassname . '"  data-dropdown-identifier="' . $uniqueDropdownIdentifier . '" data-manager-id="' . $user->id . '" href="#">' . $icon . $item->getName() . '</a></li>';
        }

        $icon = $user->status->getIcon();
        $selectedStatusClassname = $user->status->getClassName();
        $output[] = $indentation . '  </ul>';
        $output[] = $indentation . '  <label id="' . $uniqueDropdownIdentifier . '-dd-label" for="' . $uniqueDropdownIdentifier . '-dd-checkbox" data-status-id="' . $user->status->value . '" class="dropdown-menu-label ' . $selectedStatusClassname . '">';
        $output[] = $indentation . '    <span>' . $icon . $user->status->getName() . '</span>';
        $output[] = $indentation . '    ' . CoreIcons::CARET_DOWN;
        $output[] = $indentation . '  </label>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

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
        if (!$user->isVerified()) {
            $output[] = $indentation . '    <span class="article-status ' . $user->journeyStatus->getClassname() . '">' .  $responseData->getLocalValue('userJourney' . $user->journeyStatus->name) . '</span>';
        }
        $output[] = $indentation . '    <span class="' . ($user->role === UserRole::Admin ? 'is-highlighted' : '') .'">' .  $responseData->getLocalValue('userRole' . $user->role->name) . '</span>';
        $output[] = $indentation . '    <div>' . CoreIcons::CALENDAR_CHECK .  $userDate . '</div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
