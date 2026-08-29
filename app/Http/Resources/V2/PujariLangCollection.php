<?php

namespace App\Http\Resources\V2;

use Illuminate\Http\Resources\Json\ResourceCollection;

class PujariLangCollection extends ResourceCollection
{
    public function toArray($request)
    {
        return $this->collection->map(function($data) { return $data->name; });
    }

    public function with($request)
    {
        return [
            'success' => true,
            'status' => 200
        ];
    }
}
