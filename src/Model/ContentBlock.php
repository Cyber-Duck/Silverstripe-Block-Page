<?php

namespace CyberDuck\BlockPage\Model;

use SilverStripe\Control\Controller;
use SilverStripe\Core\Config\Config;
use SilverStripe\ORM\DataObject;
use SilverStripe\ORM\FieldType\DBField;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\HiddenField;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\OptionsetField;
use SilverStripe\Forms\TextField;
use SilverStripe\Security\Permission;

/**
 * ContentBlock
 *
 * Parent class for content blocks to inherit from
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class ContentBlock extends DataObject
{
    /**
     * Object database fields
     *
     * @since version 1.0.0
     *
     * @config array $db
     **/
    private static $db = [
        'Title'       => 'Varchar(512)',
        'BlockType'   => 'Varchar(30)',
        'BlockSort'   => 'Int',
        'ParentClass' => 'Varchar(512)'
    ];
    
    /**
     * Object has one relations
     *
     * @since version 1.0.0
     *
     * @config array $has_one
     **/
    private static $has_one = [
        'Parent' => DataObject::class
    ];

    /**
     * Object CMS GridField summary fields
     *
     * @since version 1.0.0
     *
     * @config array $summary_fields
     **/
    private static $summary_fields = [
        'BlockType'   => 'Type',
        'BlockTitle'  => 'Title',
        'BlockEdited' => 'Last Updated'
    ];

    /**
     * Default sorting
     *
     * @since version 1.0.1
     *
     * @config string $default_sort
     **/
    private static $default_sort = 'BlockSort';

    /**
     * Reference to current block parent
     *
     * @since version 1.1.0
     *
     * @config string $parent
     **/
    private static $parent;

    /**
     * Table name
     *
     * @since version 4.0.0
     *
     * @config string $table_name
     **/
    private static $table_name = 'ContentBlock';

    /**
     * Summary field block type
     *
     * @since version 4.0.0
     *
     * @return string
     **/
    public function getBlockType()
    {
        return $this->config()->title;
    }

    /**
     * Summary field block title
     *
     * @since version 4.0.0
     *
     * @return string
     **/
    public function getBlockTitle()
    {
        return $this->Title;
    }

    /**
     * Summary field last edited
     *
     * @since version 4.0.0
     *
     * @return string
     **/
    public function getBlockEdited()
    {
        return date('j M Y H:i:s ', strtotime($this->LastEdited)).' ';
    }

    /**
     * Update the CMS fields with the block selector or normal fields
     *
     * @since version 1.0.0
     *
     * @return object
     **/
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->addFieldToTab('Root.Main', TextField::create('Title'));

        $fields->push(HiddenField::create('BlockSort'));
        $fields->push(HiddenField::create('BlockType'));
        $fields->push(HiddenField::create('ParentID'));
        $fields->push(HiddenField::create('ParentClass'));

        if($this->getAction() == 'new') {
            return $this->getBlockSelectionFields($fields);
        }
        return $fields;
    }

    /**
     * Get the new or edit action
     *
     * @since version 1.0.0
     *
     * @return string
     **/
    private function getAction()
    {
        $path = explode('/', Controller::curr()->getRequest()->getURL());

        return array_pop($path);
    }

    /**
     * Create the CMS block selector fields
     *
     * @since version 1.0.0
     *
     * @param object $fields
     *
     * @return object
     **/
    public function getBlockSelectionFields(FieldList $fields)
    {
        $fields->removeByName('Title');

        $options = $this->getBlockSelectionOptions();
        $checked = key(array_slice($options, 0, 1, true));

        $fields->push(LiteralField::create(false, '<div id="PageType" class="cms-add-form">'));
        $fields->push(OptionsetField::create('BlockType', false, $options, $checked));
        $fields->push(LiteralField::create(false, '</div">'));
        $fields->push(HiddenField::create('BlockStage')->setValue('choose'));
        $fields->push(HiddenField::create('ParentID'));
        $fields->push(HiddenField::create('ParentClass'));

        return $fields;
    }

    /**
     * Return an array of block type dropdown options HTML
     *
     * @since version 1.0.0
     *
     * @return array
     **/
    private function getBlockSelectionOptions()
    {
        $html = '<div class="form-check-overlay"></div>
                 <div class="form-check-section">
                    <div class="form-check-img">
                        <img src="%s" height="150" width="360">
                    </div>
                    <strong class="form-check-title">%s</strong>
                    <span class="form-check-description">%s</span>
                 </div>';

        $options = [];

        foreach($this->getUnrestrictedBlocks() as $block) {
            $option = sprintf($html,
                Config::inst()->get($block, 'preview'),
                Config::inst()->get($block, 'title'),
                Config::inst()->get($block, 'description')
            );
            $options[$block] = DBField::create_field('HTMLText', $option);
        }
        return $options;
    }

    /**
     * Return an array of blocks to choose from
     *
     * @since version 1.0.7
     *
     * @return array
     **/
    private function getUnrestrictedBlocks()
    {
        $rules = (array) Config::inst()->get(ContentBlock::class, 'restrict');

        if(!empty($rules)) {
            foreach($rules as $restricted => $blocks) {
                if(!self::$parent) {
                    self::$parent = $this->ParentClass;
                }
                if(self::$parent == $restricted) {
                    return $blocks;
                }
            }
        }
        return (array) Config::inst()->get(ContentBlock::class, 'blocks');
    }
    
    /**
     * Render the block holder template
     *
     * @since version 4.0.0
     *
     * @return string
     **/
    public function getTemplateHolder()
    {
        return $this->renderWith(['Block/ContentBlock_holder']);
    }
    
    /**
     * Render the individual block template
     *
     * @since version 4.0.0
     *
     * @return string
     **/
    public function getTemplate()
    {   
        return $this->renderWith('Block/'.$this->ClassName);
    }
}
