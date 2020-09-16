<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/constants.php';
use IllinoisPublicMedia\ImageManipulationRunner\Constants;

class Image_manipulation_runner_mcp
{
    private $base_uri = 'addons/settings/'+CONSTANTS::MODULE_NAME;
    public function __construct()
    {
    }

    public function index()
    {
        $html = '<p>Hello control panel.</p>';

        return array(
            'body' => $html
        );
    }
}
