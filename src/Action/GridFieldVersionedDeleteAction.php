<?php

/*
 * GridFieldVersionedDeleteAction
 *
 * Overrides GridFieldDeleteAction to unlink a versioned relation (ContentBlock) 
 * from a versioned DataObject (Page)
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
namespace CyberDuck\BlockPage\Action;

use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridFieldDeleteAction;
use SilverStripe\Versioned\Versioned;

class GridFieldVersionedDeleteAction extends GridFieldDeleteAction
{
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        // remove from live
        if ($actionName == 'deleterecord' || $actionName == 'unlinkrelation') {
            $item = $gridField->getList()->byID($arguments['RecordID']);
            if (!$item) {
                return;
            }
            if ($actionName == 'unlinkrelation') {
                if (!$item->canEdit()) {
                    throw new ValidationException(
                        _t(__CLASS__.'.EditPermissionsFailure', "No permission to unlink record")
                    );
                }
                $item->PageContentBlock->deleteFromStage(Versioned::LIVE);
            }
        }
        // remove from stage
        parent::handleAction($gridField, $actionName, $arguments, $data);
    }
}