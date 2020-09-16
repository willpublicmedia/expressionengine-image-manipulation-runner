<?php if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

require_once __DIR__ . '/constants.php';
use IllinoisPublicMedia\ImageManipulationRunner\Constants;

/**
 * Manage module installation and updates.
 */
class Image_manipulation_runner_upd
{
    private $module_name = CONSTANTS::MODULE_NAME;

    private $version = CONSTANTS::VERSION;

    /**
     * Install module.
     */
    public function install()
    {
        $data = array(
            'module_name' => $this->module_name,
            'module_version' => $this->version,
            'has_cp_backend' => 'y',
            'has_publish_fields' => 'n',
        );

        ee()->db->insert('modules', $data);

        return true;
    }

    /**
     * Uninstall module.
     */
    public function uninstall()
    {
        ee()->db->select('module_id');
        ee()->db->from('modules');
        ee()->db->where('module_name', $this->module_name);
        $query = ee()->db->get();

        ee()->db->delete('module_member_groups', array('module_id' => $query->row('module_id')));
        ee()->db->delete('modules', array('module_name' => $this->module_name));
        ee()->db->delete('actions', array('class' => $this->module_name));

        return true;
    }

    /**
     * Update module.
     */
    public function update($current = '')
    {
        if (version_compare($current, $this->version, '=')) {
            return false;
        }

        return true;
    }
}
