<?php

use Amora\Core\Entity\Response\HtmlResponseDataAdmin;
use Amora\Core\Module\Article\Value\ArticleType;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAdmin $responseData */

?>
        <div>
          <h2><?=$responseData->getLocalValue('dashboardGoTo')?></h2>
          <div class="dashboard-cards-wrapper">
            <a href="<?=UrlBuilderUtil::buildBackofficeImagesUrl($responseData->siteLanguage)?>">
              <span class="value"><?=$responseData->dashboardCount->images?></span>
              <span>
                <img class="img-svg img-svg-20 m-r-05" width="20" height="20" src="/img/svg/image.svg" alt="<?=$responseData->getLocalValue('navAdminImages')?>">
                <?=$this->e($responseData->getLocalValue('navAdminImages'))?>
              </span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeMediaUrl($responseData->siteLanguage)?>">
              <span class="value"><?=$responseData->dashboardCount->files?></span>
              <span>
                <img class="img-svg img-svg-20 m-r-05" width="20" height="20" src="/img/svg/files.svg" alt="<?=$responseData->getLocalValue('navAdminMedia')?>">
                <?=$this->e($responseData->getLocalValue('navAdminMedia'))?>
              </span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeAlbumsUrl($responseData->siteLanguage)?>">
              <span class="value"><?=$responseData->dashboardCount->albums?></span>
              <span>
                <img class="img-svg img-svg-20 m-r-05" width="20" height="20" src="/img/svg/files.svg" alt="<?=$responseData->getLocalValue('navAdminAlbums')?>">
                <?=$this->e($responseData->getLocalValue('navAdminAlbums'))?>
              </span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Page)?>">
              <span class="value"><?=$responseData->dashboardCount->pages?></span>
              <span>
                <img class="img-svg img-svg-20 m-r-05" width="20" height="20" src="/img/svg/note-pencil.svg" alt="<?=$responseData->getLocalValue('navAdminArticles')?>">
                <?=$responseData->getLocalValue('navAdminArticles')?>
              </span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguage, ArticleType::Blog)?>">
              <span class="value"><?=$responseData->dashboardCount->blogPosts?></span>
              <span>
                <img class="img-svg img-svg-20 m-r-05" width="20" height="20" src="/img/svg/article-medium.svg" alt="<?=$responseData->getLocalValue('navAdminBlogPosts')?>">
                <?=$responseData->getLocalValue('navAdminBlogPosts')?>
              </span>
            </a>
            <a href="<?=UrlBuilderUtil::buildBackofficeUsersUrl($responseData->siteLanguage)?>">
              <span class="value"><?=$responseData->dashboardCount->users?></span>
              <span>
                <img class="img-svg img-svg-20 m-r-05" width="20" height="20" src="/img/svg/users.svg" alt="<?=$responseData->getLocalValue('navAdminUsers')?>">
                <?=$this->e($responseData->getLocalValue('navAdminUsers'))?>
              </span>
            </a>
        </div>
