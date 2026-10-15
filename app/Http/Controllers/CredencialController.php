<?php

namespace App\Http\Controllers;

use App\Credencial;
use App\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CredencialController extends Controller
{
    /**
     * Escritorio del generador de credenciales.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $employees = Employee::with('charges')
            ->orderBy('last_name')
            ->orderBy('name')
            ->get();

        return view('serve.credencial.index', compact('employees'));
    }

    /**
     * Crea o actualiza la credencial de un empleado y devuelve su hash de verificación.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function generate(Request $request)
    {
        $this->validate($request, [
            'employee_id' => 'required|integer|exists:employees,id',
        ]);

        $employee = Employee::with('charges')->findOrFail($request->input('employee_id'));

        $credencial = Credencial::firstOrNew(['employee_id' => $employee->id]);

        if (!$credencial->hash) {
            $credencial->hash = bin2hex(random_bytes(20));
        }

        $credencial->fecha = now();

        $credencial->save();

        return response()->json(['ok' => true, 'data' => $this->recordPayload($credencial)]);
    }

    /**
     * Actualiza la foto del funcionario en la tabla employees.
     * Si no llega archivo (o restore=1), restaura la foto por defecto según sexo.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function uploadFoto(Request $request)
    {
        $this->validate($request, [
            'employee_id' => 'required|integer|exists:employees,id',
            'photo' => 'nullable|image|max:8192',
            'restore' => 'boolean',
        ], [
            'photo.max' => 'La foto no puede pesar más de 8 MB.',
            'photo.image' => 'La foto debe ser una imagen PNG o JPG.',
            'photo.mimes' => 'La foto debe ser una imagen PNG o JPG.',
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));

        $restore = (bool) $request->input('restore', false);
        $nueva = $request->file('photo');

        if ($restore || !$nueva) {
            $photo = $this->defaultPhotoForSex($employee->sex);
        } else {
            $photo = Storage::disk('public')->put('server/imagenes/funcionarios', $nueva);
        }

        // rutaFoto() y no photo: la columna puede traer el encuadre pegado
        // ("?z=..&x=..&y=..") y en disco ese archivo no existe con ese nombre.
        $anterior = $employee->rutaFoto();
        if ($anterior && $anterior !== $photo && !$this->esFotoPorDefecto($anterior)) {
            Storage::disk('public')->delete($anterior);
        }

        $employee->photo = $photo;
        $employee->save();

        return response()->json(['ok' => true, 'foto' => asset($employee->photo)]);
    }

    /**
     * Guarda el encuadre de la foto (zoom y desplazamiento) del funcionario.
     *
     * No hay columna nueva: el encuadre se pega a la ruta dentro de la misma
     * columna photo, asi que se llama al modelo, que se encarga de acotar los
     * valores y de dejar la columna pelada cuando el encuadre es el de siempre.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ajusteFoto(Request $request)
    {
        // Aqui se comprueba solo el tipo. El rango (zoom entre 1 y 4 y el tope
        // de desplazamiento) lo acota Employee::acotarAjuste, que es el unico
        // sitio donde vive: si el editor de la pantalla mandara un valor fuera
        // de rango se guarda acotado en vez de rechazar la peticion, y el
        // encuadre que se ve sigue siendo el que quedo en la base.
        $this->validate($request, [
            'employee_id' => 'required|integer|exists:employees,id',
            'z' => 'nullable|numeric',
            'x' => 'nullable|numeric',
            'y' => 'nullable|numeric',
        ]);

        $employee = Employee::findOrFail($request->input('employee_id'));

        $ajuste = [
            'z' => (float) $request->input('z', 1),
            'x' => (float) $request->input('x', 0),
            'y' => (float) $request->input('y', 0),
        ];

        $employee->guardarAjusteFoto($ajuste);

        return response()->json([
            'ok' => true,
            'foto' => asset($employee->photo),
            'ajuste' => $employee->ajusteFoto(),
        ]);
    }

    /**
     * @param  string|null  $sex
     * @return string
     */
    private function defaultPhotoForSex($sex)
    {
        return $sex === 'Femenino'
            ? 'server/imagenes/funcionarios/default-female.png'
            : 'server/imagenes/funcionarios/default-male.png';
    }

    /**
     * @param  string|null  $photo
     * @return bool
     */
    private function esFotoPorDefecto($photo)
    {
        return in_array($photo, [
            'server/imagenes/funcionarios/default-female.png',
            'server/imagenes/funcionarios/default-male.png',
        ], true);
    }

    /**
     * Lista las credenciales guardadas (con búsqueda opcional).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function data(Request $request)
    {
        $id = trim((string) $request->query('id', ''));

        if ($id !== '') {
            $credencial = Credencial::with('employee.charges')->find((int) $id);

            return response()->json([
                'ok' => true,
                'data' => $credencial ? $this->recordPayload($credencial) : null,
            ]);
        }

        $q = trim((string) $request->query('q', ''));

        $query = Credencial::with('employee.charges')->orderBy('created_at', 'desc');

        if ($q !== '') {
            $term = '%' . $q . '%';
            $query->whereHas('employee', function ($builder) use ($term, $q) {
                $builder->where('name', 'like', $term)
                    ->orWhere('last_name', 'like', $term)
                    ->orWhereHas('charges', function ($charges) use ($q) {
                        $charges->where('charge_employee', 'like', '%' . $q . '%');
                    });
            });
        }

        return response()->json([
            'ok' => true,
            'data' => $query->get()->map(function (Credencial $credencial) {
                return $this->recordPayload($credencial);
            })->values(),
        ]);
    }

    /**
     * Elimina la credencial (no elimina al empleado).
     *
     * @param  \App\Credencial  $credencial
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Credencial $credencial)
    {
        $credencial->delete();

        return response()->json(['ok' => true]);
    }

    /**
     * Página pública de verificación alcanzada desde el QR.
     *
     * @param  string  $hash
     * @return \Illuminate\Contracts\View\View
     */
    public function verify($hash)
    {
        $credencial = Credencial::with('employee.charges')
            ->where('hash', $hash)
            ->first();

        return view('credencial.verificar', compact('credencial'));
    }

    /**
     * Payload (id, hash, fecha, nombre, cargo, foto y URL de verificación)
     * que el cliente usa para previsualizar, imprimir o descargar.
     *
     * @param  \App\Credencial  $credencial
     * @return array
     */
    private function recordPayload(Credencial $credencial)
    {
        return [
            'id' => $credencial->id,
            'employee_id' => $credencial->employee_id,
            'hash' => $credencial->hash,
            'fecha' => $credencial->fecha ? $credencial->fecha->format('Y-m-d') : null,
            'nombre' => $credencial->nombre,
            'cargo' => $credencial->cargo,
            'ci' => $credencial->ci,
            'foto' => $credencial->foto,
            'url' => route('verificar', $credencial->hash),
        ];
    }
}