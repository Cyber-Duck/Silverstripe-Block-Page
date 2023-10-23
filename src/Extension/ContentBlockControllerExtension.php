<?php

namespace CyberDuck\BlockPage\Extension;

use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\Control\Controller;
use SilverStripe\Control\HTTPRequest;
use SilverStripe\Core\Extension;
use SilverStripe\Core\Injector\Injector;

class ContentBlockControllerExtension extends Extension
{
    private static $allowed_actions = [
        'handleContentBlock',
    ];

    /**
     * Handles Content Block Controller request
     *
     * @param  HTTPRequest $request
     *
     * @return Controller Current Content Block Controller
     */
    public function handleContentBlock($request)
    {
        $id = $request->param('ID');
        $action = $request->param('OtherID');
        $block = ContentBlock::get()->byId($id);
        $blockController = $block->ClassName.'Controller';

        if (class_exists($blockController)) {
            $blockController = Injector::inst()->get($blockController, true, [$block]);
            $blockController->doInit();

            if (! $blockController->checkAccessAction($action)) {
                // TODO: raise user Exception
            }

            return $blockController;
        }

        return Controller::curr();
    }
}
