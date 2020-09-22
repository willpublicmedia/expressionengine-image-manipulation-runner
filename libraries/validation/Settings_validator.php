<?php

namespace IllinoisPublicMedia\ImageManipulationRunner\Libraries\Validation;

if (!defined('BASEPATH')) {
    exit('No direct script access allowed.');
}

/**
 * Tools for validating NPR Story API form data.
 */
class Settings_validator {
    /**
     * Default validation rules for NPR Story API settings.
     */
    public const SETTINGS_RULES = array(
        'allowed_types' => 'required|enum[img]'
    );

    /**
     * Validate form values.
     *
     * @param  mixed $data Form data.
     * @param  mixed $rules Validation rules.
     *
     * @return mixed Validation object.
     */
    public function validate($data, $rules) {
        $results = ee('Validation')->make($rules)->validate($data);
        return $results;
    }
}