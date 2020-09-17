<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Permission;

class RoleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
            'permissions' => $this->permissions->map(function (Permission $permission) {
                return [
                    'id' => $permission->id,
                    'name' => $permission->name,
                    'created_at' => $permission->created_at->toDateTimeString(),
                    'updated_at' => $permission->updated_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
