<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/

class ContentBlock extends DataObject
{
	private static $db = array(
		'Name'        => 'Varchar(512)',
        'CssSelector' => 'Varchar(512)',
	);

	private static $summary_fields = array(
		'ID'          => 'ID',
		'Name'        => 'Name',
		'ClassName'   => 'Type',
		'CssSelector' => 'CSS Style'
	);

	public function getCMSActions()
	{
    	$fields = parent::getCMSActions();

	    $fields->fieldByName('MajorActions')->push(
	        $cleanupAction = FormAction::create('cleanup', 'Cleaned')
	            ->setAttribute('data-icon', 'accept')
	            ->setAttribute('data-icon-alternate', 'addpage')
	            ->setAttribute('data-text-alternate', 'Clean-up now')
	    );
	    return $fields;
	}

    public function getCMSValidator()
    {
        $request = Controller::curr()->getRequest();

        $required = array('BlockStage', 'BlockType');

    	if($request->postVar('BlockStage') == 'form') {
    		$required[] = 'Name';
    		$required[] = 'CssSelector';
    	}
		return new RequiredFields($required);
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $request = Controller::curr()->getRequest();

        $fields->addFieldsToTab('Root.Main', new FieldList(
			TextField::create('Name'),
			DropdownField::create('CssSelector', 'CSS Style', array('a' => 'style 1')),
			HiddenField::create('BlockType')->setValue($request->postVar('BlockType')),
			HiddenField::create('BlockStage')->setValue('form')
		));
        return $fields;
    }

    public function getSelectionCMSFields()
    {
		$fields = new FieldList();

        $tabs = new TabSet('Root',
			new Tab('Main', 'Main')
		);
		$fields->push($tabs);

    	$types = Config::inst()->get('BlockPage', 'blocks');

        foreach($types as $type) {
			$html = sprintf('<span class="page-icon class-%s"></span><strong class="title">%s</strong><span class="description">%s</span>',
				$type,
				Config::inst()->get($type, 'title'),
				Config::inst()->get($type, 'description')
			);
			$options[$type] = DBField::create_field('HTMLText', $html);
        }

        $step = '<span class="step-label"><span class="flyout">%d</span><span class="arrow"></span><span class="title">%s</span></span>';
        $label = sprintf($step, 1, 'Add block page');

		$fields->addFieldsToTab('Root.Main', new FieldList(
			LiteralField::create(false, '<div id="PageType">'),
			HeaderField::create('Blocks'),
			OptionsetField::create('BlockType', $label, $options)->setCustomValidationMessage('Please select a block type'),
			LiteralField::create(false, '</div">'),
			HiddenField::create('BlockStage')->setValue('select')
		));
        return $fields;
    }
}