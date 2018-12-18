<?php

namespace CyberDuck\BlockPage\Controller;

use CyberDuck\BlockPage\Model\ContentBlock;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;

class ContentBlockController extends Controller
{
    /**
     * @var ContentBlock $content_block
     */
    protected $content_block;

    /**
     * Constructor
     *
     * @param ContentBlock $content_block
     */
    public function __construct(ContentBlock $content_block)
    {
        $this->content_block = $content_block;

        parent::__construct();

        $this->setRequest(Controller::curr()->getRequest());
    }

    /**
     * Get current content block
     *
     * @return ContentBlock
     */
    public function getContentBlock()
    {
        return $this->content_block;
    }

    /**
     * Get link string prepared for form action
     *
     * @param string $form Form name
     *
     * @return string Form action link
     */
    public function getFormActionLink($form = 'Form')
    {
        $controller = Controller::curr();

        return Controller::join_links(
            $controller->Link(),
            'block',
            $this->getContentBlock()->ID,
            $form
        );
    }

    /**
     * Get Link with Content Block Anchor
     * Usable for forms redirections to Content Block on page
     *
     * @param string $action Action name
     *
     * @return string
     */
    public function Link($action = null)
    {
        $page = Director::get_current_page();
        $controller = Controller::curr();

        if ($controller && ! ($controller instanceof self)) {
            return Controller::join_links(
                $controller->Link($action),
                '#block-'.$this->content_block->ID
            );
        }
        if ($page && ! ($page instanceof self)) {
            return Controller::join_links(
                $page->Link($action),
                '#block-'.$this->content_block->ID
            );
        }
    }
}
