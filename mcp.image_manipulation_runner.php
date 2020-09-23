<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/constants.php';
use IllinoisPublicMedia\ImageManipulationRunner\Constants;
require_once __DIR__ . '/libraries/validation/settings_validator.php';
use IllinoisPublicMedia\ImageManipulationRunner\Libraries\Validation\Settings_validator;

class Image_manipulation_runner_mcp
{
    private $base_uri;
    public function __construct()
    {
        $this->base_uri = ee('CP/URL')->make('addons/settings/' . strtolower(CONSTANTS::MODULE_NAME));
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

            $validation_results = $this->validate_destination($_POST['image_destination']);

            if ($validation_results->isValid())
            {
                $this->run_manipulations($_POST);
            }
            else
            {
                $this->handle_validation_errors($validation_results);
            }
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

    public function run_manipulations($bucket)
    {
        return;
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
                    'value' => '0',
                ),
            ),
            'required' => false,
        );

        return $upload_field;
    }

    private function handle_validation_errors($results)
    {
        return;
    }

    private function validate_destination($destination)
    {
        $rules = Settings_validator::SETTINGS_RULES;
        $model = ee('Model')->get('UploadDestination')
            ->filter('site_id', ee()->config->item('site_id'))
            ->filter('module_id', 0) // limit selection to user-defined destinations
            ->filter('id', $destination)
            ->with('FileDimensions')
            ->first();
    
        $data = array(
            'allowed_types' => $model->allowed_types,
            'file_dimensions' => count($model->FileDimensions->count())
        );
        $result = ee('Validation')->make($rules)->validate($model->toArray());

        return $result;
    }
}
