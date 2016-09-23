<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class TextBlock extends ContentBlock
{
	private static $title = 'Text Block';

	private static $description = 'Chunky text based block';

	private static $styles = 'text-block';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertBefore(HeaderField::create('Text Block'), 'Name');

        return $fields;
    }
}