<?php

class CreateBlock_ItemRequest extends GridFieldDetailForm_ItemRequest
{
    private static $allowed_actions = array('ItemEditForm', 'doBlock');

    public function ItemEditForm()
    {
        $form = parent::ItemEditForm();
        $request = Controller::curr()->getRequest();

        $class = $request->postVar('BlockType');
        $stage = $request->postVar('BlockStage');

        if($request->param('ID') == 'new') {
            if($class == null) {
                $form->addExtraClass('cms-add-form stacked cms-content center cms-edit-form');
                $form->setFields(ContentBlock::create()->getSelectionCMSFields());
            } else {
                $form->setFields($class::create()->getCMSFields());
            }
            $actions = $form->Actions();

            $actions->removeByName('action_doSave');

            $button = FormAction::create('doBlock');
            $button->setTitle('Create')
                ->setAttribute('data-icon', 'accept')
                ->setAttribute('data-icon-alternate', 'addpage')
                ->setAttribute('data-text-alternate', 'Clean-up now');
            $actions->unshift($button);

            $form->setActions($actions);
        }
        return $form;
    }
    
    public function doBlock($data, $form)
    {
        $class = $data['BlockType'];
        $controller = Controller::curr();

        $class = $class::create();

        $form->saveInto($class);
        $class->write();

        Session::set("FormInfo.Form_EditForm.formError.message", 'Successfully created block');
        Session::set("FormInfo.Form_EditForm.formError.type", 'good');

        $class->flushCache();

        $controller->response->removeHeader('Location'); 
        $noActionURL = $controller->removeAction($data['url']);
        $controller->getRequest()->addHeader('X-Pjax', 'CurrentForm,Breadcrumbs');

        $controller->redirect($noActionURL, 302);
    }
}