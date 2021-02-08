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
        ee()->load->library('logger');
    }

    public function index()
    {
        $validation_results = null;
        if (array_key_exists('image_destination', $_POST)) {
            $request = $_POST;

            $validation_results = $this->validate_destination($request['image_destination']);

            if ($validation_results->isValid()) {
                $limit = $request['first_char'] === 'all' ? null : $request['first_char'];
                $this->run_manipulations($request['image_destination'], boolval($request['clean_files']), $limit);
            } else {
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

    public function run_manipulations($destination_id, $clean = false, $limit = null)
    {
        $model = ee('Model')->get('UploadDestination')
            ->filter('site_id', ee()->config->item('site_id'))
            ->filter('module_id', 0) // limit selection to user-defined destinations
            ->filter('id', $destination_id)
            ->first();

        ee()->logger->developer(Constants::NAME . ': began resizing ' . $model->name);

        if ($clean) {
            $limit = null;
            $this->clean_old_manipulations($model);
        }

        $this->resize_images($model, $limit);
        ee()->logger->developer(Constants::NAME . ': finished resizing ' . $model->name);
    }

    private function build_alphabet_dropdown()
    {
        $choices = array();
        $choices['all'] = ['all'];
        $choices['0-3'] = '0-3';
        $choices['4-6'] = '4-6';
        $choices['7-9'] = '7-9';
        $choices['a-b'] = 'a-b';
        $choices['c-d'] = 'c-d';
        $choices['e-f'] = 'e-f';
        $choices['g-h'] = 'g-h';
        $choices['i-j'] = 'i-j';
        $choices['k-l'] = 'k-l';
        $choices['m-n'] = 'm-n';
        $choices['o-p'] = 'o-p';
        $choices['q-r'] = 'q-r';
        $choices['s-t'] = 's-t';
        $choices['u-v'] = 'u-v';
        $choices['w-z'] = 'w-z';

        $field = array(
            'title' => 'Limit Operations',
            'desc' => 'Limit targets by first character of filename.',
            'fields' => array(
                'first_char' => array(
                    'type' => 'select',
                    'choices' => $choices,
                ),
            ),
            'required' => 'false',
        );

        return $field;
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
        $form_fields['EE Image Tools'][] = $this->build_alphabet_dropdown();
        $form_fields['EE Image Tools'][] = $this->build_delete_field();

        return $form_fields;
    }

    private function clean_old_manipulations($model)
    {
        ee()->load->helper('file');

        $manipulations = $model->FileDimensions;

        foreach ($manipulations as $manipulation) {
            $path = $manipulation->getAbsolutePath();
            $del_dir = true;
            $del_success = delete_files($path, $del_dir);
            $msg = $del_success ? 'Deleted' : 'Failed to delete';
            $msg = Constants::NAME . ': ' . $msg . ' ' . $path;
            ee()->logger->developer($msg);
        }
        return;
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
        $errors = $results->getAllErrors();
        if (array_key_exists('allowed_types', $errors)) {
            // set issue
            ee('CP/Alert')->makeInline('image-manipulation-runner')
                ->asIssue()
                ->withTitle('Invalid Selection')
                ->addToBody('Select an image-only file destination.')
                ->defer();
        }

        if (array_key_exists('file_dimensions', $errors)) {
            // set issue
            ee('CP/Alert')->makeInline('image-manipulation-runner')
                ->asInfo()
                ->withTitle('No action necessary')
                ->addToBody('Image destination has no file manipulations defined.')
                ->defer();
        }

        return;
    }

    private function resize_images($destination, $limit)
    {
        ee()->load->library('image_lib');

        $manipulations = $destination->FileDimensions;
        $files = $destination->Files;
        $resize_results = array();

        $resize_results = array();
        foreach ($files as $file) {

            if ($limit && !preg_match("#^[$limit](.*)$#i", $file->file_name)) {
                continue;
            }

            foreach ($manipulations as $manipulation) {
                if (!$file->exists()) {
                    continue;
                }

                $config = array(
                    'width' => $manipulation->width,
                    'maintain_ratio' => true,
                    'library_path' => $destination->server_path,
                    'image_library' => ee()->config->item('image_resize_protocol'),
                    'source_image' => $file->getAbsolutePath(),
                    'new_image' => join(DIRECTORY_SEPARATOR, array(rtrim($manipulation->getAbsolutePath(), DIRECTORY_SEPARATOR), $file->file_name)),
                );

                if (ee()->input->get_post('resize_height') != '') {
                    $config['height'] = ee()->input->get_post('resize_height');
                } else {
                    $config['master_dim'] = 'width';
                }

                // Must initialize seperately in case image_lib was loaded previously
                ee()->load->library('image_lib');
                $return = ee()->image_lib->initialize($config);

                if ($return === false) {
                    $errors = ee()->image_lib->display_errors();
                } else {
                    if (!ee()->image_lib->resize()) {
                        $errors = ee()->image_lib->display_errors();
                    }
                }

                $reponse = array();

                if (isset($errors)) {
                    $response['errors'] = $errors;
                } else {
                    $file_path = $file->getAbsolutePath();
                    ee()->load->helper('file');
                    $response = array(
                        'dimensions' => ee()->image_lib->get_image_properties('', true),
                        'file_info' => get_file_info($file_path),
                    );
                }

                ee()->image_lib->clear();

                $resize_results[] = $response;
            }
        }

        return $resize_results;
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
            'file_dimensions' => $model->FileDimensions->count(),
        );
        $result = ee('Validation')->make($rules)->validate($model->toArray());

        return $result;
    }
}
