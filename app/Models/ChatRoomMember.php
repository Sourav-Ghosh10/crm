<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatRoomMember extends Model
{
    protected $table = 'chat_room_members';
    protected $fillable = ['chat_room_id', 'user_id'];

    public function room()
    {
        return $this->belongsTo(ChatRoom::class, 'chat_room_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
