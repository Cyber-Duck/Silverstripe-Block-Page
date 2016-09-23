<?php

/**
 * 
 *
 * @package silverstripe-block-page
 * @license MIT License https://github.com/cyber-duck/silverstripe-block-page/blob/master/LICENSE
 * @author  <andrewm@cyber-duck.co.uk>
 **/
class SliderBlock extends ContentBlock
{
	private static $title = 'Slider block';

	private static $description = 'Card style slider block';

	private static $styles = 'slider-block';

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        $fields->insertBefore(HeaderField::create('Slider Block'), 'Name');

        return $fields;
    }
}