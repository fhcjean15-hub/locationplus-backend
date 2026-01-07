<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return [
            'data' => UserResource::collection($this->collection),
        ];
    }

    public function with($request)
    {
        return [
            'meta' => [
                'count' => $this->collection->count(),
                'total' => method_exists($this->resource, 'total') ? $this->resource->total() : $this->collection->count(),
                'page'  => method_exists($this->resource, 'currentPage') ? $this->resource->currentPage() : null,
            ]
        ];
    }
}



// namespace App\Http\Resources;

// use Illuminate\Http\Resources\Json\ResourceCollection;

// class UserCollection extends ResourceCollection
// {
//     public function toArray($request)
//     {
//         return [
//             'users' => $this->collection,
//             'count' => $this->collection->count(),
//         ];
//     }
// }
