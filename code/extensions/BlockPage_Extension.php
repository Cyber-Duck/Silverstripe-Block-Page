<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class BlockPage_Extension extends DataExtension
{
    /**
     * @since version 1.0.0
     *
     * @config array $db 
     **/

    private static $many_many = array(
        'ContentBlocks' => 'ContentBlock'
    );

    private static $many_many_extraFields = array(
        'ContentBlocks' => array(
            'BlockSort' => 'Int'
        )
    );

    /**
     * @since version 1.0.0
     *
     * @config array $summary_fields
     **/
    private static $summary_fields = array();

    /**
     * 
     *
     * @since version 1.0.0
     *
     * @param string $fields The current FieldList object
     *
     * @return FieldList
     **/
    public function updateCMSFields(FieldList $fields) 
    {   
        $blocks = $this->owner->ContentBlocks();
        $editor = GridFieldConfig_RelationEditor::create()->addComponent(new GridFieldSortableRows('BlockSort'));
        $grid = new GridField('ContentBlocks', 'Content Blocks', $blocks, $editor);
        $fields->addFieldToTab('Root.ContentBlocks', $grid);

        $fields = new FieldList();

        return $fields;
    }
}