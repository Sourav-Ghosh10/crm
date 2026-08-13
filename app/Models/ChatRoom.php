<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoom extends Model
{
    protected $fillable = ['name', 'is_group', 'created_by'];

    public function members()
    {
        return $this->belongsToMany(User::class, 'chat_room_members', 'chat_room_id', 'user_id');
    }

    public function messages()
    {
        return $this->hasMany(ChatMessage::class, 'chat_room_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
