<?php

namespace CyberDuck\BlockPage\Model;

use Page;
use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\ORM\DataObject;
use SilverStripe\Versioned\Versioned;

class PageContentBlock extends DataObject
{
    private static $has_one = [
        'Page'  => Page::class,
        'ContentBlock' => ContentBlock::class,
    ];

    private static $owns = [
        'Page',
        'ContentBlock'
    ];
    
    private static $extensions = [
        Versioned::class
    ];
    
    private static $default_sort = 'Created';

    private static $table_name = 'PageContentBlock';

    private static $versioned_gridfield_extensions = true;
}