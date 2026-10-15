<?php

namespace App\Http\Controllers;

use App\Employee;
use App\Level;
use App\Charge;
use App\Salarie;
use App\Http\Requests\EmployeeStoreRequest;
use App\Http\Requests\EmployeeUpdateRequest;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $employees = Employee::all();
        return view('serve.employee.index', compact('employees'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('serve.employee.create', $this->employeeFormLists());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\EmployeeStoreRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(EmployeeStoreRequest $request)
    {
        $employee = Employee::create($request->except('photo'));

        if ($request->hasFile('photo')) {
            $path = Storage::disk('public')->put('server/imagenes/funcionarios', $request->file('photo'));
            $employee->fill(['photo' => $path])->save();
        } else {
            $employee->fill(['photo' => $this->defaultPhotoForSex($request->sex)])->save();
        }

        return redirect()->route('employees.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function show(Employee $employee)
    {
        return view('serve.employee.show', compact('employee'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function edit(Employee $employee)
    {
        return view('serve.employee.edit', array_merge(
            compact('employee'),
            $this->employeeFormLists()
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\EmployeeUpdateRequest  $request
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function update(EmployeeUpdateRequest $request, Employee $employee)
    {
        $employee->update($request->except('photo'));

        if ($request->hasFile('photo')) {
            $path = Storage::disk('public')->put('server/imagenes/funcionarios', $request->file('photo'));
            $employee->fill(['photo' => $path])->save();
        } elseif (!$employee->photo || $employee->rutaFoto() === $this->defaultPhotoForSex($request->sex)) {
            $employee->fill(['photo' => $this->defaultPhotoForSex($request->sex)])->save();
        }

        return redirect()->route('employees.index');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Employee  $employee
     * @return \Illuminate\Http\Response
     */
    public function destroy(Employee $employee)
    {
        $employee->delete();

        return redirect()->route('employees.index');
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
     * @return array
     */
    private function employeeFormLists()
    {
        $levels = Level::orderBy('level_employee')->pluck('level_employee', 'id');
        $salaries = Salarie::pluck('salary_monthly', 'id');
        $chargesByLevel = Charge::orderBy('charge_employee')
            ->get(['id', 'level_id', 'charge_employee'])
            ->groupBy('level_id')
            ->map(function ($group) {
                return $group->map(function ($charge) {
                    return [
                        'id' => $charge->id,
                        'name' => $charge->charge_employee,
                    ];
                })->values();
            });

        return compact('levels', 'salaries', 'chargesByLevel');
    }


}
