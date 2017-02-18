<?php

class Notification extends Base
{

    protected $visible = ['id', 'notification_type', 'object_type', 'object_id', 'user', 'created_at', 'owner'];

    protected $fillable = [
        'user_id',
        'notification_type',
        'notifier_id',
        'object_id',
        'object_type'
    ];   

    /**
     * The senders of the notifications
     */
	public function user()
	{
	  return $this->belongsToMany('User', 'notifications', 'id', 'notifier_id');
	}

}