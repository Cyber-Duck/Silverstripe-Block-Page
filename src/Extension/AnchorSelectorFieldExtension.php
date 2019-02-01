<?php

namespace CyberDuck\BlockPage\Extension;

use Page;
use SilverStripe\Core\Extension;
use SilverStripe\ORM\DataObjectSchema;
use SilverStripe\Core\Injector\Injector;

class AnchorSelectorFieldExtension extends Extension
{
    public function afterCallActionHandler($request, $action, $actionRes)
    {
        if ($action == 'anchors') {
            // Check page is accessible, otherwise return previous results
            $id = (int) $request->param('PageID');
            $page = Page::get()->byID($id);
            if (!$page || !$page->canView()) {
                return $actionRes;
            }

            // Transform previous results to php array
            $anchors = json_decode($actionRes);

            // Get the DB schema
            $dbSchema = Injector::inst()->get(DataObjectSchema::class);

            // Get page block
            $blocks = $page->ContentBlocks();

            foreach ($blocks as $block) {
                // Get fields for each block
                $fields = $dbSchema->databaseFields($block->ClassName);
                foreach ($fields as $field => $type) {
                    if ($type === 'HTMLText') {
                        // If the block uses a HTML editor
                        $content = $block->getField($field);
                        if ($content) {
                            // Get anchors using the same regex as AnchorSelectorField
                            $parseSuccess = preg_match_all(
                                "/\\s+(name|id)\\s*=\\s*([\"'])([^\\2\\s>]*?)\\2|\\s+(name|id)\\s*=\\s*([^\"']+)[\\s +>]/im",
                                $content,
                                $matches
                            );

                            if ($parseSuccess) {
                                // Cleanup results and merge them to the results,
                                $anchors = array_merge($anchors, array_values(array_unique(array_filter(array_merge($matches[3], $matches[5]))));
                            }
                        }
                    }
                }
            }

            sort($anchors);

            return json_encode($anchors);
        }
    }
}
