<?php

/**
 * Extension to turn a Page object into a Block Page
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class BlockPageExtension extends DataExtension
{
    /**
     * Has many object relations
     *
     * @since version 1.0.0
     *
     * @var array
     **/
    private static $has_many = [
        'ContentBlocks' => 'ContentBlock'
    ];

    /**
     * Method to include content blocks in the page template
     *
     * @since version 1.0.0
     *
     * @return string
     **/
    public function IncludeBlock($class, $id)
    {   
        return DataObject::get_by_id($class, $id)->renderWith($class);
    }

    /**
     * Update the page CMS fields with the content block grid field
     *
     * @since version 1.0.0
     *
     * @return object
     **/
    public function updateCMSFields(FieldList $fields) 
    {   
        $blocks = $this->owner->ContentBlocks();
        $editor = GridFieldConfig_RelationEditor::create()->addComponent(new GridFieldSortableRows('BlockSort'));
        $grid = new GridField('ContentBlocks', 'Content Blocks', $blocks, $editor);

        $grid->getConfig()
            ->removeComponentsByType('GridFieldAddExistingAutocompleter')
            ->getComponentByType('GridFieldDetailForm')
            ->setItemRequestClass('CreateBlock_ItemRequest');

        $detail = $grid->getConfig()->getComponentByType('GridFieldDetailForm');

        $content = new ContentBlock();
        $content->PageID = $this->owner->ID;
        $detail->setFields($content->getCMSFields());

        $fields->addFieldToTab('Root.ContentBlocks', $grid);

        return $fields;
    }
}