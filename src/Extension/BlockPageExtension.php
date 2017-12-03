<?php

namespace CyberDuck\BlockPage\Extension;

use Page;
use CyberDuck\BlockPage\Action\GridFieldVersionedContentBlockItemRequest;
use CyberDuck\BlockPage\Action\GridFieldVersionedDeleteAction;
use CyberDuck\BlockPage\Action\GridFieldVersionedOrderableRows;
use CyberDuck\BlockPage\Model\ContentBlock;
use CyberDuck\BlockPage\Model\PageContentBlock;
use SilverStripe\Control\Controller;
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
            ->addComponent(new GridFieldVersionedOrderableRows('Sort'))
            ->addComponent(new GridFieldVersionedDeleteAction(true))
            ->getComponentByType(GridFieldDetailForm::class)
            ->setItemRequestClass(GridFieldVersionedContentBlockItemRequest::class);

        $detail = $grid->getConfig()->getComponentByType(GridFieldDetailForm::class);

        $session = Controller::curr()->getRequest()->getSession();
        $session->set('BlockRelationID', $this->owner->ID);
        $session->set('BlockRelationClass', $this->owner->ClassName);

        $fields->addFieldToTab('Root.ContentBlocks', $grid);
    }
}