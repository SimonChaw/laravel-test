<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class MailListResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $command = $this->resource->payload->data->command;
        $job = unserialize($command);
        $uuid = $job->uuid;
        return [
            'to' => $job->to,
            'subject' => $job->subject,
            'body' => $job->body,
            'attachments' => array_map(function($attachment) use ($uuid) {
                return [
                    'filename' => $attachment['filename'],
                    'url' => env('APP_URL').Storage::url("jobs/{$uuid}-{$attachment['filename']}")
                ];
            }, $job->attachments)
        ];
    }
}
