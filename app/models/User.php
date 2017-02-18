<?php

class User extends Base
{

	protected $visible = ['username', 'name', 'bio', 'website', 'location', 'color', 'avatar_url', 'posts_count', 'followers_count', 'following_count', 'is_following', 'is_followed'];

    protected $rules = array(
        'create' => [
            'username' => 'required|alpha_numeric|max_len,32|min_len,1',
            'name' => 'required|max_len,100|min_len,1',
            'email' => 'required|valid_email',
            'password' => 'required'
        ],
        'update' => [
            'name' => 'max_len,100|min_len,1',
            'bio' => 'max_len,200|min_len,1',
            'website' => 'valid_url|max_len,256|min_len,1',
            'location' => 'alpha_space|max_len,32|min_len,1',
            'color' => 'alpha_numeric|exact_len,6'
        ]
    );

    protected $appends = [
        'avatar_url'
    ];

    /**
     * Get all of the posts for the user.
     */
    public function posts()
    {
        return $this->hasMany('Post');
    }

    /**
     * Get all of the comments for the user.
     */
    public function comments()
    {
        return $this->hasMany('Comment');
    }

    /**
     * Get all of the API tokens for the user.
     */
    public function tokens()
    {
        return $this->hasMany('Token');
    }

	/**
	 * User following relationship
	 */
	public function following()
	{
	  return $this->belongsToMany('User', 'follows', 'user_id', 'follow_id');
	}

	/**
	 * User followers relationship
	 */
	public function followers()
	{
	  return $this->belongsToMany('User', 'follows', 'follow_id', 'user_id');
	}

    /**
     * Is Following?
     */
    public function is_following()
    {
            $this->is_following = (bool) count($this->following);
    }

    /**
     * Is Followed?
     */
    public function is_followed()
    {
            $this->is_followed = (bool) count($this->followers);
    }

    /**
     * User likes
     */
    public function likes()
    {
        return $this->morphedByMany('Likes', 'likeable');
    }

    /**
    * Get all of the notifiations for the user
    */
    public function notifications()
    {
        return $this->hasMany('Notification');
    }   

    /**
     * Always hash the password when it's set
     */
    public function setPasswordAttribute($value) {
        $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
    }

    /**
     * Check if username already exists
     */
    public function usernameExists($username) {
    	
    	if(User::where('username', $username)->first()) {
    		return true;
    	}

    	return false;

    }

    /**
     * Get the profile photo URL attribute.
     */
    public function getAvatarUrlAttribute()
    {
        return 'https://www.gravatar.com/avatar/'.md5(strtolower($this->email)).'.jpg?s=200&d=mm';
    }


}
