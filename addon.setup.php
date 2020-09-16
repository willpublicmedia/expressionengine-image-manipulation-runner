<?php if (!defined('BASEPATH')) { exit ('No direct script access allowed.'); };

require_once (__DIR__ . '/constants.php');
use IllinoisPublicMedia\ImageManipulationRunner\Constants;

return array(
    'author' => Constants::AUTHOR,
    'author_url' => Constants::AUTHOR_URL,
    'name' => Constants::NAME,
    'description' => Constants::DESCRIPTION,
    'version' => Constants::VERSION,
    'namespace' => 'IllinoisPublicMedia\ImageManipulationRunner',
    'settings_exist' => true
);