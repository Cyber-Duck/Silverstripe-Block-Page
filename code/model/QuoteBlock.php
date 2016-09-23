<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class QuoteBlock extends ContentBlock
{
	private static $title = 'Quote block';

	private static $description = 'Text quote based block';

	private static $styles = 'quote-block';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertBefore(HeaderField::create('Quote Block'), 'Name');

        return $fields;
    }
}