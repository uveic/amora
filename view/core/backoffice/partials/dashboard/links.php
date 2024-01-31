<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\StringUtil;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

?>
        <div class="dashboard-count">
          <h3 class="no-margin"><?=$responseData->getLocalValue('dashboardGoTo')?></h3>
          <div class="dashboard-cards-wrapper">
            <a href="<?=UrlBuilderUtil::buildBackofficeImageListUrl($responseData->siteLanguage)?>">
              <span class="value"><?=StringUtil::formatNumber($responseData->siteLanguage, $responseData->dashboardCount->images)?></span>
              <span><?=$responseData->getLocalValue('navAdminImages')?></span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeMediaListUrl($responseData->siteLanguage)?>">
              <span class="value"><?=StringUtil::formatNumber($responseData->siteLanguage, $responseData->dashboardCount->files)?></span>
              <span><?=$responseData->getLocalValue('navAdminMedia')?></span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeArticleListUrl($responseData->siteLanguage, ArticleType::Page)?>">
              <span class="value"><?=StringUtil::formatNumber($responseData->siteLanguage, $responseData->dashboardCount->pages)?></span>
              <span><?=$responseData->getLocalValue('navAdminArticles')?></span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeArticleListUrl($responseData->siteLanguage, ArticleType::Blog)?>">
              <span class="value"><?=StringUtil::formatNumber($responseData->siteLanguage, $responseData->dashboardCount->blogPosts)?></span>
              <span><?=$responseData->getLocalValue('navAdminBlogPosts')?></span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeUserListUrl($responseData->siteLanguage)?>">
              <span class="value"><?=StringUtil::formatNumber($responseData->siteLanguage, $responseData->dashboardCount->users)?></span>
              <span><?=$responseData->getLocalValue('navAdminUsers')?></span>
            </a>
          </div>
        </div>
