<?php

namespace CyberDuck\BlockPage\Extension;

use Page;
use CyberDuck\BlockPage\Action\GridFieldVersionedContentBlockItemRequest;
use CyberDuck\BlockPage\Action\GridFieldVersionedDeleteAction;
use CyberDuck\BlockPage\Model\ContentBlock;
use CyberDuck\BlockPage\Model\PageContentBlock;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldConfig_RelationEditor;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Forms\GridField\GridFieldDetailForm;
use SilverStripe\Forms\GridField\GridFieldVersionedState;
use SilverStripe\ORM\DataExtension;
use SilverStripe\ORM\DataObject;
use Symbiote\GridFieldExtensions\GridFieldOrderableRows;

class BlockPageExtension extends DataExtension
{    
    private static $db = [];

    private static $many_many = [
        'ContentBlocks' => [
            'through' => PageContentBlock::class,
            'from' => Page::class,
            'to' => 'ContentBlock'
        ]
    ];
    
    private static $owns = [
        'ContentBlocks'
    ];
    
    public function updateCMSFields(FieldList $fields) 
    {   
        $editor = GridFieldConfig_RelationEditor::create();
        $grid = new GridField('ContentBlocks', 'Content Blocks', $this->owner->ContentBlocks(), $editor);
        $grid->getConfig()
            ->removeComponentsByType(GridFieldDeleteAction::class)
            ->addComponent(new GridFieldVersionedState(['Title']))
            ->addComponent(new GridFieldOrderableRows('Sort'))
            ->addComponent(new GridFieldVersionedDeleteAction(true));

        $detail = $grid->getConfig()
            ->getComponentByType(GridFieldDetailForm::class);
        $detail->setItemRequestClass(GridFieldVersionedContentBlockItemRequest::class);

        $content = ContentBlock::create();
        $content->PageID = $this->owner->ID;
        $content->PageClass = $this->owner->ClassName;
        $detail->setFields($content->getCMSFields());

        $fields->addFieldToTab('Root.ContentBlocks', $grid);
    }
}