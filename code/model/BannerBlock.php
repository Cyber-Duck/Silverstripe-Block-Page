<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class BannerBlock extends ContentBlock
{
	private static $title = 'Banner block';

	private static $description = 'Rotating banner block';

	private static $styles = 'banner-block';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertBefore(HeaderField::create('Banner Block'), 'Name');

        return $fields;
    }
}