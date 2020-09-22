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

        $view = ee('View')->make(strtolower(CONSTANTS::MODULE_NAME) . ':index');
        $output = $view->render(array('message' => $html));
        return $output;
    }

    public function run_manipulations($bucket = null)
    {
        return false;
    }

    private function get_upload_destinations() {
        $destinations = ee('Model')->get('UploadDestination')
            ->filter('site_id', ee()->config->item('site_id'))
            ->filter('module_id', 0) // limit selection to user-defined destinations
            ->all();

        $file_choices = array();
        foreach ($destinations as $dest) { 
            $file_choices[$dest->id] = $dest->name;
        }
            
        
        $upload_field = array(
            'title' => 'Image Upload Destination',
            // should be able to use BASE here, but url swaps session token and uri.
            'desc' => 'Choose an appropriate image gallery from the <a href="/admin.php?cp/files">Files</a> menu.',
            'fields' => array(
                'npr_image_destination' => array(
                    'type' => 'radio',
                    'choices' => $file_choices,
                    'value' => ''
                )
            ),
            'required' => true
        );

        return $upload_field;
    }
}
