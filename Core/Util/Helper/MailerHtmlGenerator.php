<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Util\DateUtil;

final class MailerHtmlGenerator
{
    public static function generateMailerItemRowHtml(
        HtmlResponseDataAdmin $responseData,
        MailerItem $mailerItem,
        string $indentation = '        ',
    ): string {
        $statusContent = isset($mailerItem->hasError)
            ? ($mailerItem->hasError
                ? '<span class="article-status status-deleted"><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/warning-circle-white.svg" alt="OK">' . $responseData->getLocalValue('mailerListError') . '</span>'
                : '<span class="article-status status-published"><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/check-white.svg" alt="Rejected">' . $responseData->getLocalValue('mailerListNoError') . '</span>'
            )
            : '<span>-</span>';

        $templateContent = '<span class="article-status status-draft">'
            . '<img class="img-svg m-r-05" src="/img/svg/file-dashed-white.svg" alt="Receiver" width="20" height="20">'
            . $responseData->getLocalValue('mailerTemplate' . $mailerItem->template->name)
            . '</span>';

        $sentAtString = $mailerItem->processedAt
            ? DateUtil::formatDateShort(
                date: $mailerItem->processedAt,
            )
            : '-';

        $receiverHtml = '<div class="mail-receiver-container">'
            . '<img class="img-svg img-svg-30" src="/img/svg/at.svg" alt="Receiver" width="30" height="30">'
            . '<div class="mail-receiver-content">'
            . '<span class="mail-receiver-name">' . ($mailerItem->receiverName ?: '-') . '</span>'
            . '<span class="mail-receiver-email">' . $mailerItem->receiverEmailAddress . '</span>'
            . '</div>'
            . '</div>';

        $secondsToSend = $mailerItem->processedAt
            ? DateUtil::getElapsedTimeString(
                from: $mailerItem->createdAt,
                to: $mailerItem->processedAt,
                language: $responseData->siteLanguage,
            )
            : '-';

        $output = [];
        $output[] = $indentation . '<div class="table-row">';
        $output[] = $indentation . '  <div class="table-item">';
        $output[] = $indentation . '    <span class="light-text-color font-0-9">#' . $mailerItem->id . '</span>';
        $output[] = $indentation . '    ' . $receiverHtml;
        $output[] = $indentation . '    <span><img class="img-svg m-r-05" src="/img/svg/envelope-simple.svg" alt="Subject" width="20" height="20">' . $mailerItem->subject .'</span>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="table-item flex-end">';
        $output[] = $indentation . '    ' . $templateContent;
        $output[] = $indentation . '    ' . $statusContent;
        $output[] = $indentation . '    <span>' . $secondsToSend . '</span>';
        $output[] = $indentation . '    <div><img class="img-svg m-r-05" src="/img/svg/calendar-check.svg" alt="Recibido" width="20" height="20">' .  $sentAtString . '</div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
