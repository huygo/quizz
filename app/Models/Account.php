<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable implements JWTSubject 
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $table = 'account';
    protected $fillable = ['name','email','password','phone','address','province','district','avatar'];
    protected $hidden = [
        'password',
        'action_code',
        'action_date',
        'dcv_token',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }
    public function getJWTCustomClaims()
    {
        return [
            'iss' => 'login',
            'id'              => $this->id,
            'name'      => $this->name,
            'email'           => $this->email,
        ];
    }
    // protected $timestamps = false;
    // public static function all(){
    //     return DB::table('users')->get();
    // }
}
