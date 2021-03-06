<?php 

namespace IllinoisPublicMedia\ImageManipulationRunner;

if (!defined('BASEPATH')) { 
    exit ('No direct script access allowed.');
}

class Constants 
{
    const NAME = 'Image Manipulation Runner';

    const AUTHOR = 'Illinois Public Media';

    const AUTHOR_URL = 'https://will.illinois.edu';

    const DESCRIPTION = 'Runs image manipulation rules against previously uploaded images.';

    const DOCS_URL = 'https://github.com/willpublicmedia/expressionengine-image-manipulation-runner';

    const MODULE_NAME = 'Image_manipulation_runner';
    
    const VERSION = '0.1.4';
}