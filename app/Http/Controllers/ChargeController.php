<?php

namespace App\Http\Controllers;

use App\Charge;
use App\Level;
use Illuminate\Http\Request;
use App\Http\Requests\ChargeStoreRequest;

class ChargeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $levels = Level::orderBy('level_employee')->pluck('level_employee', 'id');
        $levelId = $request->input('level_id');

        $charges = Charge::with('levels')
            ->when($levelId, function ($query) use ($levelId) {
                $query->where('level_id', $levelId);
            })
            ->orderBy('charge_employee')
            ->get();

        return view('serve.charge.create', compact('charges', 'levels', 'levelId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ChargeStoreRequest $request)
    {
        $charge = Charge::create($request->all());

        return redirect()->route('charges.create', array_filter([
            'level_id' => $request->input('level_id'),
        ]));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function show(Charge $charge)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function edit(Charge $charge)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Charge $charge)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Charge  $charge
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Charge $charge)
    {
        $charge->delete();

        return redirect()->route('charges.create', array_filter([
            'level_id' => $request->input('filter_level_id'),
        ]));
    }
}
