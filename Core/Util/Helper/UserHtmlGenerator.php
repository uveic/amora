<?php

namespace Amora\Core\Util\Helper;

use Amora\App\Value\AppUserRole;
use Amora\App\Value\Language;
use Amora\Core\Entity\Response\HtmlResponseDataAbstract;
use Amora\Core\Module\User\Model\Session;
use Amora\Core\Module\User\Model\User;
use Amora\Core\Module\User\Value\UserRole;
use Amora\Core\Module\User\Value\UserStatus;
use Amora\Core\Util\DateUtil;
use Amora\Core\Util\UrlBuilderUtil;
use Amora\Core\Value\CoreIcons;

final class UserHtmlGenerator
{
    public static function generateUserStatusHtml(UserStatus $status, Language $language): string
    {
        return '<span class="article-status m-t-0 '
            . $status->getClassName() . '">'
            . $status->getIcon()
            . $status->getTitle($language)
            . '</span>';
    }

    public static function generateDynamicUserStatusHtml(
        User $user,
        Language $language,
        string $identifier = 'user-status',
        string $indentation = '',
    ): string {
        $uniqueDropdownIdentifier = $identifier . '-' . $user->id;

        $output = [
            $indentation . '<input type="checkbox" id="' . $uniqueDropdownIdentifier . '-dd-checkbox" class="dropdown-menu">',
            $indentation . '<div class="dropdown-container ' . $uniqueDropdownIdentifier . '-container" data-user-id="' . $user->id . '">',
            $indentation . '  <ul>',
        ];

        foreach (UserStatus::getAll() as $item) {
            $statusClassname = $item->getClassName();
            $icon = $item->getIcon();
            $output[] = $indentation . '    <li><a data-checked="' . ($user->status === $item ? '1' : '0') . '" data-value="' . $item->value . '" class="dropdown-menu-option ' . $identifier . '-dd-option no-loader ' . $statusClassname . '"  data-dropdown-identifier="' . $uniqueDropdownIdentifier . '" href="#">' . $icon . $item->getTitle($language) . '</a></li>';
        }

        $icon = $user->status->getIcon();
        $selectedStatusClassname = $user->status->getClassName();
        $output[] = $indentation . '  </ul>';
        $output[] = $indentation . '  <label id="' . $uniqueDropdownIdentifier . '-dd-label" for="' . $uniqueDropdownIdentifier . '-dd-checkbox" data-value="' . $user->status->value . '" class="dropdown-menu-label ' . $selectedStatusClassname . '">';
        $output[] = $indentation . '    <span>' . $icon . $user->status->getTitle($language) . '</span>';
        $output[] = $indentation . '    ' . CoreIcons::CARET_DOWN;
        $output[] = $indentation . '  </label>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateDynamicUserRoleHtml(
        User $user,
        Language $language,
        string $identifier = 'user-role',
        string $indentation = '',
    ): string {
        $uniqueDropdownIdentifier = $identifier . '-' . $user->id;

        $output = [
            $indentation . '<input type="checkbox" id="' . $uniqueDropdownIdentifier . '-dd-checkbox" class="dropdown-menu">',
            $indentation . '<div class="dropdown-container ' . $uniqueDropdownIdentifier . '-container" data-user-id="' . $user->id . '">',
            $indentation . '  <ul>',
        ];

        foreach (AppUserRole::getAll() as $item) {
            $className = $item->getClass();
            $icon = $item->getIcon();
            $output[] = $indentation . '    <li><a data-checked="' . ($user->role === $item ? '1' : '0') . '" data-value="' . $item->value . '" class="dropdown-menu-option ' . $identifier . '-dd-option no-loader ' . $className . '"  data-dropdown-identifier="' . $uniqueDropdownIdentifier . '" href="#">' . $icon . $item->getTitle($language) . '</a></li>';
        }

        $icon = $user->role->getIcon();
        $selectedRoleClassname = $user->role->getClass();
        $output[] = $indentation . '  </ul>';
        $output[] = $indentation . '  <label id="' . $uniqueDropdownIdentifier . '-dd-label" for="' . $uniqueDropdownIdentifier . '-dd-checkbox" data-value="' . $user->role->value . '" class="dropdown-menu-label ' . $selectedRoleClassname . '">';
        $output[] = $indentation . '    <span>' . $icon . $user->role->getTitle($language) . '</span>';
        $output[] = $indentation . '    ' . CoreIcons::CARET_DOWN;
        $output[] = $indentation . '  </label>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateUserRowHtml(
        Language $language,
        User $user,
        ?Session $session = null,
        string $indentation = '        ',
    ): string {
        $userTitleHtml = '<a href="' . UrlBuilderUtil::buildBackofficeUserViewUrl(language: $language, userId: $user->id) . '">' . $user->getNameOrEmail() . '</a>';

        $output = [];
        $output[] = $indentation . '<div class="table-row">';
        $output[] = $indentation . '  <div class="table-item">';
        $output[] = $indentation . '    ' . $userTitleHtml;
        $output[] = $indentation . '    <span class="icon-one-line">' . CoreIcons::ENVELOPE_SIMPLE . $user->email . '</span>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="table-item flex-no-grow">';

        if ($session) {
            $sessionLastVisitedString = DateUtil::getElapsedTimeString(
                from: $session->lastVisitedAt,
                includePrefixAndOrSuffix: true,
                language: $language,
            );

            $output[] = $indentation . '    <div class="icon-one-line" title="' . DateUtil::formatDateShort($session->lastVisitedAt) . '">' . CoreIcons::SIGN_IN .  $sessionLastVisitedString . '</div>';
        }

        if (!$user->status->isEnabled()) {
            $output[] = $indentation . '    ' . $user->status->asHtml($language);
        }

        if (!$user->isVerified()) {
            $output[] = $indentation . '    ' . $user->journeyStatus->asHtml($language);
        }

        $output[] = $indentation . '    ' . $user->role->asHtml($language);

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }

    public static function generateUserFilterFilterInfoHtml(HtmlResponseDataAbstract $responseData): string
    {
        $statusIdParam = $responseData->request->getGetParam('sId');
        $roleIdParam = $responseData->request->getGetParam('rId');

        if (empty($statusIdParam) && empty($roleIdParam)) {
            return '';
        }

        $output = [];
        $output[] = '      <div class="filter-by">';
        $output[] = '        <div class="items">';
        $output[] = '          <span><b>' . $responseData->getLocalValue('formFilterTitle') . 's:</b></span>';

        if (!empty($statusIdParam) && UserStatus::tryFrom($statusIdParam)) {
            $status = UserStatus::from($statusIdParam);
            $output[] = '          ' . $status->asHtml($responseData->siteLanguage);
        }

        if (!empty($roleIdParam) && (UserRole::tryFrom($roleIdParam) || AppUserRole::tryFrom($roleIdParam) )) {
            $role = UserRole::tryFrom($roleIdParam) ? UserRole::from($roleIdParam) : AppUserRole::from($roleIdParam);
            $output[] = '          ' . $role->asHtml($responseData->siteLanguage);
        }

        $output[] = '        </div>';
        $output[] = '        <div class="filter-links">';
        $output[] = '          <span class="filter-open">' . CoreIcons::PENCIL_SIMPLE . '</span>';
        $output[] = '        </div>';
        $output[] = '      </div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
