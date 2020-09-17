<?php

namespace App\Http\Resources;

use App\User;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

class PermissionResource extends JsonResource
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
            'roles' => $this->roles->map(function (Role $role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                    'created_at' => $role->created_at->toDateTimeString(),
                    'updated_at' => $role->updated_at->toDateTimeString(),
                ];
            }),
        ];
    }
}
