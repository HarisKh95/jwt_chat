<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\CommentResource;
class PostResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'file' => $this->file,
            'file' => $this->file,
            'user_id' => $this->user_id,
            'visible' => $this->visibile,
            'created_at' => (string) $this->created_at,
            'updated_at' => (string) $this->updated_at,
            'Comments'=> CommentResource::collection($this->comments()->get())

          ];
    }
}
