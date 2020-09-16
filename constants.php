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

    const DOCS_URL = 'https://gitlab.engr.illinois.edu/willpublicmedia/ee-image-manipulation-runner';
    
    const VERSION = '0.0.0';
}