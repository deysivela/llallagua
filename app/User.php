<?php 

namespace App;
use Caffeinated\Shinobi\Traits\ShinobiTrait;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{

    use Notifiable, ShinobiTrait;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'employee_id','statu','user','password'
    ];

    /** 
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    public function employees()
    {
        return $this->belongsTo(Employee::class,'employee_id');
    }

    public function establecimientosSalud()
    {
        return $this->belongsToMany(
            \App\Salud\SaludEstablecimiento::class,
            'salud_usuario_establecimiento',
            'user_id',
            'establecimiento_id'
        );
    }
}
