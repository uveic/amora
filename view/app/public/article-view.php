<?php

use Amora\Core\Entity\Response\HtmlResponseData;

/** @var HtmlResponseData $responseData */

$this->layout('base', ['responseData' => $responseData]);
$this->insert('partials/article', ['responseData' => $responseData]);