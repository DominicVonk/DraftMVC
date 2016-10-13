<?php
require('DraftRouter.php');
require('DraftModel.php');
require('DraftController.php');
require('DraftView.php');
require('DraftConfig.php');

if (!defined('DRAFT_CONTROLLERS')) {
    define('DRAFT_CONTROLLERS', __DIR__ . '/controllers');
}
if (!defined('DRAFT_VIEWS')) {
    define('DRAFT_VIEWS', __DIR__ . '/views');
}
if (!defined('DRAFT_CONFIGS')) {
    define('DRAFT_CONFIGS', __DIR__ . '/config');
}