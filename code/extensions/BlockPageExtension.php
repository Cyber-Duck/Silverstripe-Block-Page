<?php

class BlockPageExtension extends DataExtension
{
    private static $has_many = [
        'ContentBlocks' => 'ContentBlock'
    ];

    private static $summary_fields = [];

    public function IncludeBlock($class, $id)
    {   
        return DataObject::get_by_id($class, $id)->renderWith($class);
    }

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