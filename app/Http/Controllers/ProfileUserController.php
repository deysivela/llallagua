<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Caffeinated\Shinobi\Models\Role;
use Caffeinated\Shinobi\Models\Permission;
use App\User;
use App\Employee;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class ProfileUserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */

    public function index()
    {
        $employees=Employee::all();
        $users=User::all();
        $roles=Role::all();
        $permissions=Permission::all();
        return view('serve.admin.principal', compact('users', 'roles', 'permissions','employees'));
    }


    public function profile()
    {
        /** @var \App\User $user */
        $user = Auth::user();
        $user->load(['employees.charges', 'roles', 'establecimientosSalud']);
        $employees = Employee::optionsFullName();

        return view('serve.admin.profile', compact('user', 'employees'));
    }

    public function updates_employee(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|integer|exists:employees,id',
        ], [
            'employee_id.integer' => 'El funcionario seleccionado no es válido.',
            'employee_id.exists'  => 'El funcionario seleccionado no existe en el registro.',
        ]);

        /** @var \App\User $user */
        $user = Auth::user();
        $employeeId = $request->filled('employee_id') ? (int) $request->input('employee_id') : null;

        $user->employee_id = $employeeId;
        $user->save();

        if ($employeeId === null) {
            return redirect()->route('profile.index')
                ->with('success', 'Se desvinculó el funcionario de su cuenta correctamente.');
        }

        return redirect()->route('profile.index')
            ->with('success', 'Funcionario vinculado a su cuenta correctamente.');
    }


    public function updates_avatar(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ], [
            'photo.required' => 'Seleccione una imagen para actualizar su foto.',
            'photo.image'    => 'El archivo seleccionado debe ser una imagen válida.',
            'photo.mimes'    => 'La imagen debe ser de tipo JPG, PNG o GIF.',
            'photo.max'      => 'La imagen no debe superar los 2 MB.',
        ]);

        /** @var \App\User $user */
        $user = Auth::user();

        if (!$user->employees) {
            return redirect()->route('profile.index')
                ->with('error', 'Esta cuenta no tiene un registro de funcionario asociado para actualizar la fotografía.');
        }

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');
            $filename = time() . '.' . $photo->getClientOriginalExtension();
            $relativePath = 'server/imagenes/funcionarios/' . $filename;
            Image::make($photo)->resize(300, 300)->save(public_path($relativePath));

            $user->employees->photo = $relativePath;
            $user->employees->save();
        }

        return redirect()->route('profile.index')->with('success', 'Fotografía de perfil actualizada correctamente.');
    }

    public function updates_password(Request $request, User $user = null)
    {
        $request->validate([
            'current-password' => 'required',
            'new-password'     => 'required|string|min:6|confirmed',
        ], [
            'current-password.required' => 'Debe ingresar su contraseña actual.',
            'new-password.required'     => 'Debe ingresar una nueva contraseña.',
            'new-password.min'          => 'La nueva contraseña debe tener al menos 6 caracteres.',
            'new-password.confirmed'    => 'La confirmación de la nueva contraseña no coincide.',
        ]);

        /** @var \App\User $authUser */
        $authUser = Auth::user();

        if (!Hash::check($request->get('current-password'), $authUser->password)) {
            return redirect()->route('profile.index')
                ->withErrors(['current-password' => 'Su contraseña actual no coincide con la registrada.'])
                ->with('error', 'Su contraseña actual no coincide con la contraseña que proporcionó. Inténtelo de nuevo.');
        }

        if (strcmp($request->get('current-password'), $request->get('new-password')) === 0) {
            return redirect()->route('profile.index')
                ->withErrors(['new-password' => 'La nueva contraseña no puede ser igual a la contraseña actual.'])
                ->with('error', 'La nueva contraseña no puede ser la misma que su contraseña actual. Por favor, elija una contraseña diferente.');
        }

        $authUser->password = Hash::make($request->get('new-password'));
        $authUser->save();

        return redirect()->route('profile.index')->with('success', 'Contraseña actualizada correctamente.');
    }
} 