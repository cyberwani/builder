<?php

namespace VisualComposer\Modules\Editors\DataAjax;

if (!defined('ABSPATH')) {
    header('Status: 403 Forbidden');
    header('HTTP/1.1 403 Forbidden');
    exit;
}

use VisualComposer\Framework\Container;
use VisualComposer\Framework\Illuminate\Support\Module;
use VisualComposer\Helpers\Traits\EventsFilters;

/**
 * Class PostDataController
 * @package VisualComposer\Modules\Editors\DataAjax
 */
class PostDataController extends Container implements Module
{
    use EventsFilters;

    /**
     * Use for dynamic fields components
     * PostDataController constructor.
     */
    public function __construct()
    {
        $this->addFilter(
            'vcv:dataAjax:getData',
            'getPostData'
        );
    }

    /**
     * @param $response
     * @param $payload
     *
     * @return mixed
     */
    protected function getPostData($response, $payload)
    {
        $sourceId = intval($payload['sourceId']);

        $response['postData'] = [
            'featuredImageId' => get_post_thumbnail_id($sourceId),
            'featuredImageSrc' => get_the_post_thumbnail_url($sourceId),
        ];

        return $response;
    }
}
