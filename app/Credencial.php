<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credencial extends Model
{
    protected $table = 'credenciales';

    protected $fillable = ['employee_id', 'fecha', 'hash'];

    protected $dates = ['fecha'];

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function getNombreAttribute()
    {
        return trim((string) optional($this->employee)->full_name);
    }

    public function getCargoAttribute()
    {
        return optional(optional($this->employee)->charges)->charge_employee;
    }

    public function getCiAttribute()
    {
        return optional($this->employee)->ci;
    }

    public function getFotoAttribute()
    {
        $photo = optional($this->employee)->photo;

        return $photo ? asset($photo) : null;
    }
}