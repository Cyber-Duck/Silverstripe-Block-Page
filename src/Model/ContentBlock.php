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
        'ID'          => 'ID',
        'Title'       => 'Title',
        'ClassName'   => 'Type'
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

        $fields->push(LiteralField::create(false, '<div id="PageType" class="cms-add-form">'));
        $fields->push(OptionsetField::create('BlockType', $this->getBlockSelectionLabel(), $this->getBlockSelectionOptions())
                ->setCustomValidationMessage('Please select a block type'));
        $fields->push(LiteralField::create(false, '</div">'));
        $fields->push(HiddenField::create('BlockStage')->setValue('choose'));
        $fields->push(HiddenField::create('ParentID'));
        $fields->push(HiddenField::create('ParentClass'));

        return $fields;
    }

    /**
     * Create the CMS block selector field label
     *
     * @since version 1.0.0
     *
     * @return string
     **/
    private function getBlockSelectionLabel()
    {
        return DBField::create_field('HTMLText','<span class="step-label">
        <span class="flyout">Step 1.</span>
        <span class="arrow"></span>
        <span class="title">Add content block</span>
        </span>');
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
        $html = '<span class="page-icon class-%s"></span>
                 <strong class="title">%s</strong>
                 <span class="description">%s</span>';

        $options = [];

        foreach($this->getUnrestrictedBlocks() as $block) {
            $option = sprintf($html,
                $block,
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
}
