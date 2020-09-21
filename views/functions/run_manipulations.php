<?php
    if (!defined('BASEPATH')) 
    {
        exit('No direct script access allowed');
    }

    ee()->load->helper('form');
?>

<h2>Run Manipulations</h2>

<p>Runs defined image manipulations against previously uploaded images.</p>
<?= form_open('run_manipulations'); ?>
