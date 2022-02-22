<?php

use Amora\Core\Model\Response\HtmlResponseDataAuthorised;
use Amora\Core\Util\UrlBuilderUtil;

/** @var HtmlResponseDataAuthorised $responseData */

?>
        <div class="content-flex-block width-45-percent">
          <h2><?=$responseData->getLocalValue('dashboardGoTo')?></h2>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/image-black.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminImages'))?>"><a href="<?=UrlBuilderUtil::buildBackofficeImagesUrl($responseData->siteLanguageIsoCode)?>"><?=$this->e($responseData->getLocalValue('navAdminImages'))?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/note-pencil.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminArticles'))?>"><a href="<?=UrlBuilderUtil::buildBackofficeArticlesUrl($responseData->siteLanguageIsoCode)?>"><?=$responseData->getLocalValue('navAdminArticles')?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/article-medium.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminBlogPosts'))?>"><a href="<?=UrlBuilderUtil::buildBackofficeBlogPostsUrl($responseData->siteLanguageIsoCode)?>"><?=$responseData->getLocalValue('navAdminBlogPosts')?></a></p>
          <p><img class="img-svg m-r-05" width="20" height="20" src="/img/svg/users.svg" alt="<?=$this->e($responseData->getLocalValue('navAdminUsers'))?>"><a href="<?=UrlBuilderUtil::buildBackofficeUsersUrl($responseData->siteLanguageIsoCode)?>"><?=$this->e($responseData->getLocalValue('navAdminUsers'))?></a></p>
        </div>
