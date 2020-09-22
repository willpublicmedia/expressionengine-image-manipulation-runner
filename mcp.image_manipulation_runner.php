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
        $validation_results = null;
        if (!empty($_POST)) {
            ee('CP/Alert')->makeInline('image-manipulator-form')
                ->asSuccess()
                ->withTitle('Image Manipulator')
                ->addToBody('Request posted.')
                ->defer();

            // $validation_results = $this->process_form_data($_POST);

            // if ($validation_results->isValid()) {
            //     $this->save_settings($_POST, 'npr_story_api_settings');
            // }
        }

        $form_fields = $this->build_fields();

        $data = array(
            'base_url' => $this->base_uri,
            'cp_page_title' => 'Run Manipulations',
            'errors' => $validation_results,
            'save_btn_text' => 'Run',
            'save_btn_text_working' => 'Running...',
            'sections' => $form_fields,
        );

        return ee('View')->make('ee:_shared/form')->render($data);
    }

    public function run_manipulations($bucket = null)
    {
        // $validation_results = null;
        // if (!empty($_POST)) {
        //     $validation_results = $this->process_form_data($_POST);

        //     if ($validation_results->isValid()) {
        //         $this->save_settings($_POST, 'npr_story_api_settings');
        //     }
        // }
    }

    private function build_delete_field()
    {
        $field = array(
            'title' => 'Delete existing manipulations',
            'desc' => 'Delete existing manipulations before generating new ones.',
            'fields' => array(
                'clean_files' => array(
                    'type' => 'toggle',
                    'value' => '',
                ),
            ),
            'required' => false,
        );

        return $field;
    }

    private function build_fields()
    {
        $form_fields = array(
            'EE Image Tools' => array(),
        );
        $form_fields['EE Image Tools'][] = $this->get_upload_destinations();
        $form_fields['EE Image Tools'][] = $this->build_delete_field();

        return $form_fields;
    }

    private function get_upload_destinations()
    {
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
            'desc' => 'Choose an target image gallery from the <a href="/admin.php?cp/files">Files</a> menu.',
            'fields' => array(
                'image_destination' => array(
                    'type' => 'radio',
                    'choices' => $file_choices,
                    'value' => '',
                ),
            ),
            'required' => true,
        );

        return $upload_field;
    }
}
