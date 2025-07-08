<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PaginationCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    private $pagination;
    private $params;

    public function __construct($resource, $params = [])
    {
        $this->pagination = [
            'total' => $resource->total(),
            'count' => $resource->count(),
            'per_page' => $resource->perPage(),
            'current_page' => $resource->currentPage(),
            'total_pages' => $resource->lastPage(),
        ];

        $this->params = $params;

        $resource = $resource->getCollection();
        parent::__construct($resource);
    }

    public function toArray($request)
    {
        $data = [
            'list' => $this->collection,
            'pagination' => $this->pagination,
            'params' => $this->params
        ];

        return [
            'data' => $data,
            'error_code' => 0,
            'msg' => 'success'
        ];
    }
}
