<?php

use Amora\Core\Entity\Response\HtmlResponseData;
use Amora\Core\Module\Album\Model\Album;
use Amora\Core\Module\Album\Model\AlbumSection;
use Amora\Core\Util\Helper\AlbumHtmlGenerator;

/** @var HtmlResponseData $responseData */

if (!$responseData->album) {
  return;
}

/** @var Album $album */
$album = $responseData->album;

?>
<!DOCTYPE html>
<html lang="<?=strtolower($responseData->siteLanguage->value)?>">
<head>
<?=$this->insert('../../../../app/frontend/public/partials/head', ['responseData' => $responseData])?>
  <link href="/css/album/new-york-000.css" rel="stylesheet" type="text/css">
  <link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%2210 0 100 100%22><text y=%22.90em%22 font-size=%2290%22>🚆</text></svg>">
</head>
<body>
  <main class="content-main">
<?php
    /** @var AlbumSection $section */
    foreach ($album->sections as $section) {
        echo AlbumHtmlGenerator::generateAlbumTemplateNewYorkSectionHtml(
            section: $section,
            indentation: '    ',
        ) . PHP_EOL;
    }
?>
    <section class="content-child js-content-slider-fade">
      <div class="media-wrapper">
        <img src="/uploads/001.jpg" class="media-item media-opacity-active" alt="Imaxe">
        <img src="/uploads/002.jpg" class="media-item media-opacity-hidden" alt="Imaxe">
        <img src="/uploads/003.jpg" class="media-item media-opacity-hidden" alt="Imaxe">
        <img src="/uploads/004.jpg" class="media-item media-opacity-hidden" alt="Imaxe">
      </div>
      <div class="media-content-wrapper">
        <h1 class="media-title main-slide-title"><?=$album->titleHtml?></h1>
        <div class="media-text"><?=$album->contentHtml?></div>
      </div>
    </section>

    <section class="content-child js-content-slider" data-media-id="001">
      <div class="media-wrapper">
        <img src="/uploads/004.jpg" class="media-item media-active" alt="Imaxe" loading="lazy">
        <img src="/uploads/006.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/007.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/008.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/009.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/010.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/011.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/012.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
      </div>
      <div class="media-content-wrapper">
        <div class="content-header">
          <h1 class="media-title">Marsella</h1>
          <p class="media-subtitle">Francia</p>
        </div>
        <div class="content-text">
          <p class="media-text">Travel’s rebound has revealed the depth of our drive to explore the world. Why do we travel? For food, culture, adventure, natural beauty? This year’s list has all those elements, and more.</p>
          <div class="media-links">
            <a href="#" class="js-media-read-more" data-media-id="001">Ler máis<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>
            <a href="#" class="js-media-view" data-media-id="001">Ver as fotos<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>
          </div>
        </div>

        <div class="media-content-navigation">
          <div class="js-navigation-left"></div>
          <div class="js-navigation-right"></div>
        </div>

        <div class="media-text-panel null" id="media-text-panel-001">
          <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>
          <div class="media-panel-content">
            <img loading="lazy" src="https://static01.nytimes.com/newsgraphics/2022-11-30-52places/3567f18533e05d6ff63d906e28741c9aa2a99c1c/_assets/final-maps/f2-23-places-kilmartin-glen.svg" alt="Map of Kilmartin Glen, Scotland">
            <p>The sun rises over Kilmartin Glen as it has for thousands of years, illuminating an ancient landscape of more than 800 archaeological monuments sprouting in the mist. This verdant valley on Scotland’s wild west coast is one of the most significant prehistoric sites in Britain, yet it’s largely off the visitor circuit; imagine Stonehenge without the crowds.</p>
            <p>Wander among majestic stone circles, standing slabs that jut from the earth, burial cairns and rock carvings of concentric rings, expanding like ripples from a drop of water. And now the past is getting a refresh: The <a href="https://www.kilmartin.org/">Kilmartin Museum</a> is reopening with expanded exhibits and new experiences that delve into the region’s relics and flourishing natural life, including <a href="https://www.nature.scot/enjoying-outdoors/scotlands-national-nature-reserves/moine-mhor-nnr/moine-mhor-nnr-about-reserve">Moine Mhor</a> (Great Moss), one of the few remaining raised bogs in Europe, above which looms the Iron Age hill fort of Dunadd.</p>
            <p>For full immersion into the Scotland of yore, stay at the moody 16th-century <a href="https://www.kilmartincastle.com/">Kilmartin Castle</a>, which was recently transformed into a boutique hotel, with vaulted ceilings, copper tubs and a wild swimming pond.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="content-child js-content-slider" data-media-id="006">
      <div class="media-wrapper">
        <img src="/uploads/017.jpg" class="media-item media-active" alt="Imaxe" loading="lazy">
        <img src="/uploads/018.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/019.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
      </div>
      <div class="media-content-wrapper">
        <div class="content-header">
          <h1 class="media-title">Xénova</h1>
          <p class="media-subtitle">Italia</p>
        </div>

        <div class="content-text">
          <p class="media-text">Travel’s rebound has revealed the depth of our drive to explore the world. Why do we travel? For food, culture, adventure, natural beauty? This year’s list has all those elements, and more.</p>
          <div class="media-links">
            <a href="#" class="js-media-read-more" data-media-id="006">Ler máis<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>
            <a href="#" class="js-media-view" data-media-id="001">Ver as fotos<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>
          </div>
        </div>

        <div class="media-content-navigation">
          <div class="js-navigation-left"></div>
          <div class="js-navigation-right"></div>
        </div>

        <div class="media-text-panel null" id="media-text-panel-006">
          <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>
          <div class="media-panel-content">
            <img loading="lazy" src="https://static01.nytimes.com/newsgraphics/2022-11-30-52places/3567f18533e05d6ff63d906e28741c9aa2a99c1c/_assets/final-maps/f2-23-places-kilmartin-glen.svg" alt="Map of Kilmartin Glen, Scotland">
            <p>The sun rises over Kilmartin Glen as it has for thousands of years, illuminating an ancient landscape of more than 800 archaeological monuments sprouting in the mist. This verdant valley on Scotland’s wild west coast is one of the most significant prehistoric sites in Britain, yet it’s largely off the visitor circuit; imagine Stonehenge without the crowds.</p>
            <p>Wander among majestic stone circles, standing slabs that jut from the earth, burial cairns and rock carvings of concentric rings, expanding like ripples from a drop of water. And now the past is getting a refresh: The <a href="https://www.kilmartin.org/">Kilmartin Museum</a> is reopening with expanded exhibits and new experiences that delve into the region’s relics and flourishing natural life, including <a href="https://www.nature.scot/enjoying-outdoors/scotlands-national-nature-reserves/moine-mhor-nnr/moine-mhor-nnr-about-reserve">Moine Mhor</a> (Great Moss), one of the few remaining raised bogs in Europe, above which looms the Iron Age hill fort of Dunadd.</p>
            <p>For full immersion into the Scotland of yore, stay at the moody 16th-century <a href="https://www.kilmartincastle.com/">Kilmartin Castle</a>, which was recently transformed into a boutique hotel, with vaulted ceilings, copper tubs and a wild swimming pond.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="content-child js-content-slider" data-media-id="002">
      <div class="media-wrapper">
        <img src="/uploads/013.jpg" class="media-item media-active" alt="Imaxe" loading="lazy">
        <img src="/uploads/014.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/015.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/016.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
      </div>
      <div class="media-content-wrapper">
        <div class="content-header">
          <h1 class="media-title">Camogli</h1>
          <p class="media-subtitle">Italia</p>
        </div>

        <div class="content-text">
          <p class="media-text">Travel’s rebound has revealed the depth of our drive to explore the world. Why do we travel? For food, culture, adventure, natural beauty? This year’s list has all those elements, and more.</p>
          <div class="media-links">
            <a href="#" class="js-media-read-more" data-media-id="002">Ler máis<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>
            <a href="#" class="js-media-view" data-media-id="002">Ver as fotos<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>
          </div>
        </div>

        <div class="media-content-navigation">
          <div class="js-navigation-left"></div>
          <div class="js-navigation-right"></div>
        </div>

        <div class="media-text-panel null" id="media-text-panel-002">
          <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>
          <div class="media-panel-content">
            <img loading="lazy" src="https://static01.nytimes.com/newsgraphics/2022-11-30-52places/3567f18533e05d6ff63d906e28741c9aa2a99c1c/_assets/final-maps/f2-23-places-kilmartin-glen.svg" alt="Map of Kilmartin Glen, Scotland">
            <p>The sun rises over Kilmartin Glen as it has for thousands of years, illuminating an ancient landscape of more than 800 archaeological monuments sprouting in the mist. This verdant valley on Scotland’s wild west coast is one of the most significant prehistoric sites in Britain, yet it’s largely off the visitor circuit; imagine Stonehenge without the crowds.</p>
            <p>Wander among majestic stone circles, standing slabs that jut from the earth, burial cairns and rock carvings of concentric rings, expanding like ripples from a drop of water. And now the past is getting a refresh: The <a href="https://www.kilmartin.org/">Kilmartin Museum</a> is reopening with expanded exhibits and new experiences that delve into the region’s relics and flourishing natural life, including <a href="https://www.nature.scot/enjoying-outdoors/scotlands-national-nature-reserves/moine-mhor-nnr/moine-mhor-nnr-about-reserve">Moine Mhor</a> (Great Moss), one of the few remaining raised bogs in Europe, above which looms the Iron Age hill fort of Dunadd.</p>
            <p>For full immersion into the Scotland of yore, stay at the moody 16th-century <a href="https://www.kilmartincastle.com/">Kilmartin Castle</a>, which was recently transformed into a boutique hotel, with vaulted ceilings, copper tubs and a wild swimming pond.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="content-child js-content-slider" data-media-id="003">
      <div class="media-wrapper">
        <img src="/uploads/020.jpg" class="media-item media-active" alt="Imaxe" loading="lazy">
        <img src="/uploads/021.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/022.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/023.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/024.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/025.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/026.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
      </div>
      <div class="media-content-wrapper">
        <div class="content-header">
          <h1 class="media-title">Pisa</h1>
          <p class="media-subtitle">Italia</p>
        </div>

        <div class="content-text">
          <p class="media-text">Travel’s rebound has revealed the depth of our drive to explore the world. Why do we travel? For food, culture, adventure, natural beauty? This year’s list has all those elements, and more.</p>
          <div class="media-links">
            <a href="#" class="js-media-read-more" data-media-id="003">Ler máis<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>
            <a href="#" class="js-media-view" data-media-id="003">Ver as fotos<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>
          </div>
        </div>

        <div class="media-content-navigation">
          <div class="js-navigation-left"></div>
          <div class="js-navigation-right"></div>
        </div>

        <div class="media-text-panel null" id="media-text-panel-003">
          <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>
          <div class="media-panel-content">
            <img loading="lazy" src="https://static01.nytimes.com/newsgraphics/2022-11-30-52places/3567f18533e05d6ff63d906e28741c9aa2a99c1c/_assets/final-maps/f2-23-places-kilmartin-glen.svg" alt="Map of Kilmartin Glen, Scotland">
            <p>The sun rises over Kilmartin Glen as it has for thousands of years, illuminating an ancient landscape of more than 800 archaeological monuments sprouting in the mist. This verdant valley on Scotland’s wild west coast is one of the most significant prehistoric sites in Britain, yet it’s largely off the visitor circuit; imagine Stonehenge without the crowds.</p>
            <p>Wander among majestic stone circles, standing slabs that jut from the earth, burial cairns and rock carvings of concentric rings, expanding like ripples from a drop of water. And now the past is getting a refresh: The <a href="https://www.kilmartin.org/">Kilmartin Museum</a> is reopening with expanded exhibits and new experiences that delve into the region’s relics and flourishing natural life, including <a href="https://www.nature.scot/enjoying-outdoors/scotlands-national-nature-reserves/moine-mhor-nnr/moine-mhor-nnr-about-reserve">Moine Mhor</a> (Great Moss), one of the few remaining raised bogs in Europe, above which looms the Iron Age hill fort of Dunadd.</p>
            <p>For full immersion into the Scotland of yore, stay at the moody 16th-century <a href="https://www.kilmartincastle.com/">Kilmartin Castle</a>, which was recently transformed into a boutique hotel, with vaulted ceilings, copper tubs and a wild swimming pond.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="content-child js-content-slider" data-media-id="004">
      <div class="media-wrapper">
        <img src="/uploads/027.jpg" class="media-item media-active" alt="Imaxe" loading="lazy">
        <img src="/uploads/028.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/029.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/030.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
      </div>
      <div class="media-content-wrapper">
        <div class="content-header">
          <h1 class="media-title">Florencia</h1>
          <p class="media-subtitle">Italia</p>
        </div>

        <div class="content-text">
          <p class="media-text">Travel’s rebound has revealed the depth of our drive to explore the world. Why do we travel? For food, culture, adventure, natural beauty? This year’s list has all those elements, and more.</p>
          <div class="media-links">
            <a href="#" class="js-media-read-more" data-media-id="004">Ler máis<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>
            <a href="#" class="js-media-view" data-media-id="004">Ver as fotos<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>
          </div>
        </div>

        <div class="media-content-navigation">
          <div class="js-navigation-left"></div>
          <div class="js-navigation-right"></div>
        </div>

        <div class="media-text-panel null" id="media-text-panel-004">
          <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>
          <div class="media-panel-content">
            <img loading="lazy" src="https://static01.nytimes.com/newsgraphics/2022-11-30-52places/3567f18533e05d6ff63d906e28741c9aa2a99c1c/_assets/final-maps/f2-23-places-kilmartin-glen.svg" alt="Map of Kilmartin Glen, Scotland">
            <p>The sun rises over Kilmartin Glen as it has for thousands of years, illuminating an ancient landscape of more than 800 archaeological monuments sprouting in the mist. This verdant valley on Scotland’s wild west coast is one of the most significant prehistoric sites in Britain, yet it’s largely off the visitor circuit; imagine Stonehenge without the crowds.</p>
            <p>Wander among majestic stone circles, standing slabs that jut from the earth, burial cairns and rock carvings of concentric rings, expanding like ripples from a drop of water. And now the past is getting a refresh: The <a href="https://www.kilmartin.org/">Kilmartin Museum</a> is reopening with expanded exhibits and new experiences that delve into the region’s relics and flourishing natural life, including <a href="https://www.nature.scot/enjoying-outdoors/scotlands-national-nature-reserves/moine-mhor-nnr/moine-mhor-nnr-about-reserve">Moine Mhor</a> (Great Moss), one of the few remaining raised bogs in Europe, above which looms the Iron Age hill fort of Dunadd.</p>
            <p>For full immersion into the Scotland of yore, stay at the moody 16th-century <a href="https://www.kilmartincastle.com/">Kilmartin Castle</a>, which was recently transformed into a boutique hotel, with vaulted ceilings, copper tubs and a wild swimming pond.</p>
          </div>
        </div>
      </div>
    </section>

    <section class="content-child js-content-slider" data-media-id="005">
      <div class="media-wrapper">
        <img src="/uploads/031.jpg" class="media-item media-active" alt="Imaxe" loading="lazy">
        <img src="/uploads/032.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/033.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/034.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/035.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/036.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/037.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/038.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/039.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
        <img src="/uploads/040.jpg" class="media-item media-hidden" alt="Imaxe" loading="lazy">
      </div>
      <div class="media-content-wrapper">
        <div class="content-header">
          <h1 class="media-title">Roma</h1>
          <p class="media-subtitle">Italia</p>
        </div>

        <div class="content-text">
          <p class="media-text">Travel’s rebound has revealed the depth of our drive to explore the world. Why do we travel? For food, culture, adventure, natural beauty? This year’s list has all those elements, and more.</p>
          <div class="media-links">
            <a href="#" class="js-media-read-more" data-media-id="005">Ler máis<img src="/img/svg/article-white.svg" alt="Ler máis" width="20" height="20"></a>
            <a href="#" class="js-media-view" data-media-id="005">Ver as fotos<img src="/img/svg/arrow-right-white.svg" alt="Ver as fotos" width="20" height="20"></a>
          </div>
        </div>

        <div class="media-content-navigation">
          <div class="js-navigation-left"></div>
          <div class="js-navigation-right"></div>
        </div>

        <div class="media-text-panel null" id="media-text-panel-005">
          <a class="media-panel-close" href="#"><img src="/img/svg/x-white.svg" alt="Close" width="20" height="20"></a>
          <div class="media-panel-content">
            <img loading="lazy" src="https://static01.nytimes.com/newsgraphics/2022-11-30-52places/3567f18533e05d6ff63d906e28741c9aa2a99c1c/_assets/final-maps/f2-23-places-kilmartin-glen.svg" alt="Map of Kilmartin Glen, Scotland">
            <p>The sun rises over Kilmartin Glen as it has for thousands of years, illuminating an ancient landscape of more than 800 archaeological monuments sprouting in the mist. This verdant valley on Scotland’s wild west coast is one of the most significant prehistoric sites in Britain, yet it’s largely off the visitor circuit; imagine Stonehenge without the crowds.</p>
            <p>Wander among majestic stone circles, standing slabs that jut from the earth, burial cairns and rock carvings of concentric rings, expanding like ripples from a drop of water. And now the past is getting a refresh: The <a href="https://www.kilmartin.org/">Kilmartin Museum</a> is reopening with expanded exhibits and new experiences that delve into the region’s relics and flourishing natural life, including <a href="https://www.nature.scot/enjoying-outdoors/scotlands-national-nature-reserves/moine-mhor-nnr/moine-mhor-nnr-about-reserve">Moine Mhor</a> (Great Moss), one of the few remaining raised bogs in Europe, above which looms the Iron Age hill fort of Dunadd.</p>
            <p>For full immersion into the Scotland of yore, stay at the moody 16th-century <a href="https://www.kilmartincastle.com/">Kilmartin Castle</a>, which was recently transformed into a boutique hotel, with vaulted ceilings, copper tubs and a wild swimming pond.</p>
          </div>
        </div>
      </div>
    </section>

  </main>
  <script type="module" src="/js/album/new-york-000.js"></script>
</body>

</html>
