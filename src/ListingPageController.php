<?php

namespace Symbiote\ListingPage;

use PageController;
use SilverStripe\Core\Config\Config;
use SilverStripe\Assets\File;
use SilverStripe\Assets\Folder;
use SilverStripe\Control\HTTPRequest;

class ListingPageController extends PageController
{
    private static $allowed_actions = [
        'index',
        'view'
    ];

    private static $url_handlers = [
        'view/$ID/$OtherID' => 'view',
        '$Action' => 'index'
    ];

    public function index(HTTPRequest $request)
    {
        // This is required so the listing page doesn't eat AJAX requests against the page controller.
        $action = $request->latestParam('Action');
        if ($action &&
            $this->hasMethod($action) &&
            in_array($action, $this->config()->allowed_actions)) {
            return $this->$action($request);
        }
        if ($this->data()->ContentType ||
            $this->data()->CustomContentType) {
            // k, not doing it in the theme...
            $contentType = $this->data()->ContentType ? $this->data()->ContentType : $this->data()->CustomContentType;
            $this->response->addHeader('Content-type', $contentType);

            return $this->data()->Content();
        }
        return array();
    }

    /**
     * View the ListingPage with a new source.
     *
     * This is only valid for File and Folder source types
     */
    public function view(HTTPRequest $request)
    {
        if (Config::inst()->get('ListingPage', 'allow_source_replacement') == false) {
            return $this->httpError(404);
        }

        if ($id = $request->param('ID')) {
            if (!ctype_digit($id)){
                return $this->httpError(404);
            }

            $page = $this->data();
            if ($page->ListType == Folder::class || (!$page->StrictType && $page->ListType == File::class)) {
                $replaced = $page->replaceSourceID((int)$id);
                if (!$replaced) {
                    return $this->httpError(404);
                }
            }
        }

        return array();
    }
}
