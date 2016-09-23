<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class NewsletterBlock extends ContentBlock
{
	private static $title = 'Newsletter block';

	private static $description = 'Block with AJAX newsletter';

	private static $styles = 'newsletter-block';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertBefore(HeaderField::create('Newsletter Block'), 'Name');

        return $fields;
    }
}