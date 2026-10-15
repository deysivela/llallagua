<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Employee extends Model
{
    protected $fillable = ['level_id','charge_id','type_employee','name','last_name','sex','address','phone','email','photo'];

    public function users()
    {
    	return $this->hasMany(User::class);
    }

    public function levels()
    {
    	return $this->belongsTo(Level::class,'level_id');
    }

    public function charges()
    {
    	return $this->belongsTo(Charge::class,'charge_id');
    }

    public function salaries()
    {
    	return $this->belongsTo(Salarie::class,'salarie_id');
    }

    public function getFullNameAttribute()
    {
        return trim($this->name . ' ' . $this->last_name);
    }

    /* ------------------------------------------------------------------
       Ajuste de encuadre de la foto (solo para la credencial)
       ------------------------------------------------------------------
       La columna photo es un VARCHAR(255) que guarda la ruta del archivo en
       disco, por ejemplo server/imagenes/funcionarios/ab12.jpg. Ese mismo
       valor se pinta con asset() en el sitio publico, en las listas del
       panel y en la credencial, y se usa como nombre de archivo en disco
       cuando se borra o se reemplaza la foto.

       Para no agregar una migracion, el encuadre se guarda pegado a la ruta
       como si fuera una cadena de consulta:

           server/imagenes/funcionarios/ab12.jpg?z=1.35&x=12&y=-8

       asset() concatena igual, asi que la imagen sigue sirviendo (el servidor
       web ignora la cadena de consulta al buscar el archivo) y el recorte
       viaja solo a todas las vistas. Lo que SI hay que hacer es usar
       rutaFoto() en el codigo que toca el disco o compara contra la foto
       por defecto, porque con la cadena pegada no encuentra el archivo.
       ------------------------------------------------------------------ */

    const FOTO_ZOOM_MIN = 1.0;
    const FOTO_ZOOM_MAX = 4.0;

    /**
     * Area real por la que se mueve la foto dentro del marco, en unidades de
     * diseno. El marco mide 490em x 440em pero lleva borde de 5em y el CSS usa
     * box-sizing: border-box, asi que lo que ve la foto son 480 x 430.
     */
    const FOTO_MARCO_ANCHO = 480;
    const FOTO_MARCO_ALTO = 430;

    /**
     * Ruta del archivo sin el ajuste pegado. Es la que se usa para borrar en
     * disco y para comparar contra la foto por defecto.
     *
     * @return string
     */
    public function rutaFoto()
    {
        $foto = (string) $this->photo;
        $pos = strpos($foto, '?');

        return $pos === false ? $foto : substr($foto, 0, $pos);
    }

    /**
     * Encuadre guardado: zoom y desplazamiento, en unidades de diseno.
     * Siempre sale acotado, asi que un valor raro guardado a mano no
     * empuja la foto fuera del marco.
     *
     * @return array{z: float, x: float, y: float}
     */
    public function ajusteFoto()
    {
        $ajuste = ['z' => self::FOTO_ZOOM_MIN, 'x' => 0.0, 'y' => 0.0];

        $foto = (string) $this->photo;
        $pos = strpos($foto, '?');
        if ($pos !== false) {
            $query = substr($foto, $pos + 1);
            $datos = [];
            parse_str($query, $datos);
            foreach (['z', 'x', 'y'] as $clave) {
                if (isset($datos[$clave]) && is_numeric($datos[$clave])) {
                    $ajuste[$clave] = (float) $datos[$clave];
                }
            }
        }

        return static::acotarAjuste($ajuste);
    }

    /**
     * Pega un encuadre a una ruta y devuelve el valor listo para la columna.
     * Si el encuadre es el de siempre se devuelve la ruta pelada, para no
     * ensuciar la base de datos con querys que no dicen nada.
     *
     * @param  string|null  $ruta
     * @param  array  $ajuste
     * @return string
     */
    public static function componerFoto($ruta, array $ajuste = [])
    {
        $ruta = (string) $ruta;
        if ($ruta === '') {
            return $ruta;
        }

        $ajuste = static::acotarAjuste(array_merge(
            ['z' => self::FOTO_ZOOM_MIN, 'x' => 0.0, 'y' => 0.0],
            array_intersect_key($ajuste, array_flip(['z', 'x', 'y']))
        ));

        $z = round($ajuste['z'], 2);
        $x = (int) round($ajuste['x']);
        $y = (int) round($ajuste['y']);

        if ($z <= self::FOTO_ZOOM_MIN && $x === 0 && $y === 0) {
            return $ruta;
        }

        return $ruta . '?z=' . $z . '&x=' . $x . '&y=' . $y;
    }

    /**
     * Acota el encuadre para que la foto siempre cubra el marco: el zoom no
     * baja de 1 (dejaria huecos) ni sube de 4, y el desplazamiento no puede
     * llevar un borde de la foto adentro de la ventana.
     *
     * @param  array  $ajuste
     * @return array
     */
    public static function acotarAjuste(array $ajuste)
    {
        $z = isset($ajuste['z']) ? (float) $ajuste['z'] : self::FOTO_ZOOM_MIN;
        $z = max(self::FOTO_ZOOM_MIN, min(self::FOTO_ZOOM_MAX, $z));

        $margenX = (self::FOTO_MARCO_ANCHO * ($z - 1)) / 2;
        $margenY = (self::FOTO_MARCO_ALTO * ($z - 1)) / 2;

        $x = isset($ajuste['x']) ? (float) $ajuste['x'] : 0.0;
        $y = isset($ajuste['y']) ? (float) $ajuste['y'] : 0.0;

        return [
            'z' => $z,
            'x' => max(-$margenX, min($margenX, $x)),
            'y' => max(-$margenY, min($margenY, $y)),
        ];
    }

    /**
     * Guarda el encuadre en la misma columna photo.
     *
     * @param  array  $ajuste
     * @return $this
     */
    public function guardarAjusteFoto(array $ajuste)
    {
        $this->photo = static::componerFoto($this->rutaFoto(), $ajuste);
        $this->save();

        return $this;
    }

    public function getChargeEmployeeAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        return optional($this->charges)->charge_employee;
    }

    public function getPublicEmailAttribute()
    {
        $email = preg_replace('/\s+/', '', (string) $this->email);

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return null;
        }

        return $email;
    }

    public function getPublicPhoneAttribute()
    {
        $phone = preg_replace('/\D+/', '', (string) $this->phone);

        if ($phone === '' || $phone === '0') {
            return null;
        }

        return $phone;
    }

    public function getTelHrefAttribute()
    {
        if (!$this->public_phone) {
            return null;
        }

        return 'tel:+591' . $this->public_phone;
    }

    /**
     * Nómina pública: autoridades, directores, responsables/encargados, unidades y el resto.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function forNominaPersonal()
    {
        return static::with(['charges', 'levels'])
            ->get()
            ->sortBy(function ($employee) {
                return sprintf(
                    '%02d-%02d-%s-%s-%s',
                    $employee->nominaGroupRank(),
                    $employee->nominaSecondaryRank(),
                    mb_strtoupper(trim(optional($employee->levels)->level_employee ?: 'zzz')),
                    mb_strtoupper(trim($employee->last_name)),
                    mb_strtoupper(trim($employee->name))
                );
            })
            ->values();
    }

    /**
     * @return int
     */
    public function nominaGroupRank()
    {
        $cargo = $this->nominaCargoNorm();
        $nivel = $this->nominaNivelNorm();

        if ($cargo === 'ALCALDE MUNICIPAL' || (strpos($cargo, 'ALCALDE MUNICIPAL') !== false && strpos($cargo, 'SUB') === false)) {
            return 1;
        }

        if (strpos($cargo, 'PRESIDENTE DEL CONCEJO') !== false || strpos($cargo, 'CONCEJAL') !== false) {
            return 2;
        }

        if ($this->esCargoSubAlcalde($cargo)) {
            return 3;
        }

        if ($this->esTitularSecretariaMunicipal($cargo)) {
            return 4;
        }

        if ($this->esCargoDirector($cargo)) {
            return 5;
        }

        if ($this->esCargoResponsableOEncargado($cargo)) {
            return 6;
        }

        if (strpos($nivel, 'UNIDAD') !== false) {
            return 7;
        }

        return 8;
    }

    /**
     * @return string
     */
    public function nominaGroupLabel()
    {
        $labels = [
            1 => 'Alcalde municipal',
            2 => 'Concejales',
            3 => 'Subalcaldes',
            4 => 'Secretarías municipales',
            5 => 'Directores',
            6 => 'Responsables y encargados',
            7 => 'Unidades',
            8 => 'Otras dependencias',
        ];

        return $labels[$this->nominaGroupRank()];
    }

    /**
     * @return int
     */
    public function nominaSecondaryRank()
    {
        $cargo = $this->nominaCargoNorm();
        $nivel = $this->nominaNivelNorm();
        $rank = $this->nominaGroupRank();

        if ($rank === 2) {
            return strpos($cargo, 'PRESIDENTE DEL CONCEJO') !== false ? 0 : 1;
        }

        if ($rank === 3) {
            $ordenDistrito = [
                'central' => 1,
                'siglo' => 2,
                '22' => 3,
                'catavi' => 4,
                'chullpa' => 5,
                'sicoya' => 6,
                'sakamarca' => 7,
            ];
            $clave = $this->distritoSubalcaldiaKey();

            return isset($ordenDistrito[$clave]) ? $ordenDistrito[$clave] : 9;
        }

        if ($rank === 4) {
            if (strpos($cargo, 'ADMINISTRACION') !== false || strpos($cargo, 'ADMINISTRACIÓN') !== false || strpos($cargo, 'FINANZAS') !== false) {
                return 0;
            }
            if (strpos($cargo, 'TECNICA') !== false || strpos($cargo, 'TÉCNICA') !== false) {
                return 1;
            }
            if (strpos($cargo, 'DESARROLLO HUMANO') !== false) {
                return 2;
            }

            return 3;
        }

        if ($rank === 6) {
            $esUnidad = $this->mencionaUnidad($cargo) || $this->mencionaUnidad($nivel);
            $esResponsable = strpos($cargo, 'RESPONSABLE') !== false;

            if ($esResponsable && $esUnidad) {
                return 0;
            }
            if (strpos($cargo, 'JEFE') !== false && $esUnidad) {
                return 1;
            }
            if ($esResponsable) {
                return 2;
            }
            if (preg_match('/ENCARGAD|ENCANGAD|\bENC\./', $cargo)) {
                return 3;
            }

            return 4;
        }

        if ($rank === 8 && (strpos($nivel, 'SECRETARIA MUNICIPAL') !== false || strpos($nivel, 'SECRETARÍA MUNICIPAL') !== false)) {
            return 0;
        }

        return 1;
    }

    /**
     * @return string
     */
    private function nominaCargoNorm()
    {
        return mb_strtoupper(trim(optional($this->charges)->charge_employee ?: ''));
    }

    /**
     * @return string
     */
    private function nominaNivelNorm()
    {
        return mb_strtoupper(trim(optional($this->levels)->level_employee ?: ''));
    }

    /**
     * @param  string  $cargo
     * @return bool
     */
    private function esCargoDirector($cargo)
    {
        if (strpos($cargo, 'TECNICO') !== false || strpos($cargo, 'TÉCNICO') !== false) {
            return false;
        }

        return (bool) preg_match('/\bDIRECTORA?\b/u', $cargo);
    }

    /**
     * Titulares de las 3 secretarías municipales (no asistentes ni auxiliares).
     *
     * @param  string  $cargo
     * @return bool
     */
    private function esTitularSecretariaMunicipal($cargo)
    {
        if (strpos($cargo, 'ASISTENTE') !== false || strpos($cargo, 'AUXILIAR') !== false) {
            return false;
        }

        return (bool) preg_match('/SECRETARI[OA] MUNICIPAL/', $cargo);
    }

    /**
     * @param  string  $cargo
     * @return bool
     */
    private function esCargoResponsableOEncargado($cargo)
    {
        if (strpos($cargo, 'RESPONSABLE') !== false) {
            return true;
        }

        if (preg_match('/ENCARGAD|ENCANGAD|\bENC\.|\bENC\s/', $cargo)) {
            return true;
        }

        if (strpos($cargo, 'JEFE') !== false) {
            return true;
        }

        if (preg_match('/\bADMINISTRADOR/', $cargo)) {
            return true;
        }

        return false;
    }

    /**
     * @param  string  $texto
     * @return bool
     */
    private function mencionaUnidad($texto)
    {
        return (bool) preg_match('/\bUNIDAD(?:ES)?\b/u', $texto);
    }

    /**
     * @param  string  $cargo
     * @return bool
     */
    private function esCargoSubAlcalde($cargo)
    {
        if (!preg_match('/SUB[-\s]*ALCALDE/', $cargo)) {
            return false;
        }

        return !preg_match('/AYUDANTE|SERENO|SUPERVISOR|SECRETARIA|ELABORACI[OÓ]N/', $cargo);
    }

    /**
     * Empleados que ocupan uno o varios cargos (por nombre exacto, sin importar mayúsculas).
     *
     * @param  string|array  $charges
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function withChargeNames($charges)
    {
        $names = array_values(array_unique(array_filter(array_map(function ($charge) {
            return mb_strtoupper(trim($charge));
        }, (array) $charges))));

        if (empty($names)) {
            return static::query()->whereRaw('1 = 0')->get();
        }

        return static::with(['charges', 'levels'])
            ->whereHas('charges', function ($query) use ($names) {
                $query->where(function ($inner) use ($names) {
                    foreach ($names as $index => $name) {
                        $method = $index === 0 ? 'whereRaw' : 'orWhereRaw';
                        $inner->{$method}('UPPER(TRIM(charge_employee)) = ?', [$name]);
                    }
                });
            })
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Subalcaldes y subalcaldesas, excluyendo personal de apoyo de las subalcaldías.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function subAlcaldes()
    {
        return static::with(['charges', 'levels', 'charges.levels'])
            ->whereHas('charges', function ($query) {
                $query->where(function ($inner) {
                    $inner->whereRaw('UPPER(charge_employee) LIKE ?', ['%SUB ALCALDE%'])
                        ->orWhereRaw('UPPER(charge_employee) LIKE ?', ['%SUB-ALCALDE%'])
                        ->orWhereRaw('UPPER(charge_employee) LIKE ?', ['%SUB- ALCALDE%'])
                        ->orWhereRaw('UPPER(charge_employee) LIKE ?', ['%SUB ALCALDESA%'])
                        ->orWhereRaw('UPPER(charge_employee) LIKE ?', ['%SUB-ALCALDESA%']);
                })
                ->whereRaw('UPPER(charge_employee) NOT LIKE ?', ['%AYUDANTE%'])
                ->whereRaw('UPPER(charge_employee) NOT LIKE ?', ['%SERENO%'])
                ->whereRaw('UPPER(charge_employee) NOT LIKE ?', ['%SUPERVISOR%'])
                ->whereRaw('UPPER(charge_employee) NOT LIKE ?', ['%SECRETARIA%'])
                ->whereRaw('UPPER(charge_employee) NOT LIKE ?', ['%ELABORACION%'])
                ->whereRaw('UPPER(charge_employee) NOT LIKE ?', ['%ELABORACIÓN%']);
            })
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();
    }

    /**
     * Clave de distrito de la página de subalcaldías (central, siglo, 22, catavi, chullpa, sicoya, sakamarca).
     *
     * @return string|null
     */
    public function distritoSubalcaldiaKey()
    {
        $nivel = mb_strtoupper(trim(
            optional($this->levels)->level_employee
            ?: optional(optional($this->charges)->levels)->level_employee
            ?: ''
        ));

        $porNivel = [
            'SUB ALCALDIA CATAVI' => 'catavi',
            'SUB ALCALDIA SIGLO XX' => 'siglo',
            'SUB ALCALDIA AYLLU CHULLPA' => 'chullpa',
            'SUB ALCALDIA AYLLU SIKUYA' => 'sicoya',
            'SUB ALCALDIA 22 DE DICIEMBRE' => '22',
            'SUB ALCALDIA DISTRITO SAKAMARCA' => 'sakamarca',
            'SUB ALCALDIA DISTRITO CENTRAL LLALLAGUA' => 'central',
        ];

        if (isset($porNivel[$nivel])) {
            return $porNivel[$nivel];
        }

        $texto = $nivel . ' ' . mb_strtoupper(trim(optional($this->charges)->charge_employee ?: ''));

        if (strpos($texto, 'CENTRAL') !== false) {
            return 'central';
        }
        if (strpos($texto, 'SIGLO') !== false) {
            return 'siglo';
        }
        if (strpos($texto, '22') !== false) {
            return '22';
        }
        if (strpos($texto, 'CHULLPA') !== false) {
            return 'chullpa';
        }
        if (strpos($texto, 'SIKUYA') !== false || strpos($texto, 'SIKOYA') !== false) {
            return 'sicoya';
        }
        if (strpos($texto, 'SAKAMARCA') !== false) {
            return 'sakamarca';
        }
        if (strpos($texto, 'CATAVI') !== false) {
            return 'catavi';
        }

        return null;
    }

    /**
     * @param  array  $distritoIds
     * @return array
     */
    public static function subAlcaldesPorDistrito(array $distritoIds = [])
    {
        $ids = $distritoIds ?: ['central', 'siglo', '22', 'catavi', 'chullpa', 'sicoya', 'sakamarca'];
        $porDistrito = [];

        foreach ($ids as $id) {
            $porDistrito[$id] = new Collection();
        }

        foreach (static::subAlcaldes() as $persona) {
            $clave = $persona->distritoSubalcaldiaKey();
            if ($clave && isset($porDistrito[$clave])) {
                $porDistrito[$clave]->push($persona);
            }
        }

        return $porDistrito;
    }
}
