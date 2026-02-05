<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

	protected $guard = 'admins';
    protected $table = 'users';
	//default guard
	protected function getDefaultGuardName(): string
    {
        return 'admin';
    }


    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
		'first_name',
		'last_name',
        'email',
		'phone',
		'phone_country',
		'is_active',
		'role_id',
		'reset_token',
		'avatar',
		'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

	//filter user function which is used for list
	 public static function filterUser(string $search = "", $limit = 0, $offset = 0, string $user = "", string $role = "")
    {
        $query = self::select("users.*", 'roles.name as role_name')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('role_id', "!=", 1)
            ->whereNull('deleted_at');
        if ($user != "") {
            $query->where('users.id', $user);
        }
        if ($role != "") {
            $query->where('users.role_id', $role);
        }
        $query->where(function ($query) use ($search) {
            $query->where('users.first_name', 'like', "%{$search}%")
			     ->orWhere('users.username', 'like', "%{$search}%")
			     ->orWhere('users.last_name', 'like', "%{$search}%")
                ->orWhere('users.email', 'like', "%{$search}%")
                ->orWhere('users.phone', 'like', "%{$search}%")
                ->orWhere('roles.name', 'like', "%{$search}%");
        })
            ->orderBy('users.id', 'DESC');

        $total = $query->count();
        if ($limit && $limit > 0) {
            $query->limit($limit)->offset($offset);
        }
        $result = $query->get();
        return ["totalRecords" => $total, "result" => $result];
    }
	//role relationship
	public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }
}
