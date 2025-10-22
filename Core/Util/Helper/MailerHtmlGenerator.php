<?php

namespace Amora\Core\Util\Helper;

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Mailer\Model\MailerItem;
use Amora\Core\Util\DateUtil;
use Amora\Core\Value\CoreIcons;

final class MailerHtmlGenerator
{
    public static function generateMailerItemRowHtml(
        HtmlResponseDataAdmin $responseData,
        MailerItem $mailerItem,
        string $indentation = '        ',
    ): string {
        $statusContent = $mailerItem->processedAt
            ? ($mailerItem->hasError
                ? '<span class="article-status status-deleted">' . CoreIcons::WARNING_CIRCLE . $responseData->getLocalValue('mailerListError') . '</span>'
                : '<span class="article-status status-published">' . CoreIcons::CHECK . $responseData->getLocalValue('mailerListNoError') . '</span>'
            )
            : '<span class="article-status status-warning">' . CoreIcons::WARNING_CIRCLE . $responseData->getLocalValue('mailerListNotSent') . '</span>';

        $templateContent = '<span class="article-status status-draft">'
            . CoreIcons::FILE_DASHED
            . $responseData->getLocalValue('mailerTemplate' . $mailerItem->template->name)
            . '</span>';

        $sentAtString = $mailerItem->processedAt ? DateUtil::formatDateShort($mailerItem->processedAt)
            : DateUtil::formatDateShort($mailerItem->createdAt);

        $receiverHtml = '<div class="mail-receiver-container">'
            . CoreIcons::AT
            . '<div class="mail-receiver-content">'
            . '<span class="mail-receiver-name">' . $mailerItem->receiverName . '</span>'
            . '<span class="mail-receiver-email">' . $mailerItem->receiverEmailAddress . '</span>'
            . '</div>'
            . '</div>';

        $secondsToSendHtml = '';
        if (
            $mailerItem->processedAt &&
            $mailerItem->processedAt->getTimestamp() - $mailerItem->createdAt->getTimestamp() > 10
        ) {
            $secondsToSendHtml = '<span>';
            $secondsToSendHtml .= DateUtil::getElapsedTimeString(
                from: $mailerItem->createdAt,
                to: $mailerItem->processedAt,
                language: $responseData->siteLanguage,
            );
            $secondsToSendHtml .= '</span>';
        }

        $output = [];
        $output[] = $indentation . '<div class="table-row">';
        $output[] = $indentation . '  <div class="table-item">';
        $output[] = $indentation . '    ' . $receiverHtml;
        $output[] = $indentation . '    <span class="icon-one-line">' . CoreIcons::ENVELOPE_SIMPLE . $mailerItem->subject . '</span>';
        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '  <div class="flex-end">';
        $output[] = $indentation . '    <a href="#" class="email-content-js" data-mailer-id="' . $mailerItem->id . '">' . CoreIcons::FILE_HTML . '</a>';
        $output[] = $indentation . '    ' . $templateContent;
        $output[] = $indentation . '    ' . $statusContent;
        $output[] = $indentation . '    ' . $secondsToSendHtml;
        $output[] = $indentation . '    <div>' . CoreIcons::CALENDAR_CHECK .  $sentAtString . '</div>';

        $output[] = $indentation . '  </div>';
        $output[] = $indentation . '</div>';

        return implode(PHP_EOL, $output) . PHP_EOL;
    }
}
