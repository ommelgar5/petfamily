<?php

namespace App\Http\Controllers\Peluqueria;

use App\Detalle_peluqueria;
use App\Http\Controllers\Controller;
use App\Peluqueria;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PeluqueriaController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pagActual  = 'servicio';
        $peluquerias = Peluqueria::where('estado','=',0)->where('cod_usuario','=',Auth::user()->cod_usuario)->with('mascota.raza','mascota.sexo','mascota.propietario') ->orderBy('fecha', 'asc')->get();
        return view('peluqueria.atender', compact('pagActual','peluquerias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param $cod_expediente
     * @param $cod_peluqueria
     * @return \Illuminate\Http\Response
     */
    public function create($cod_expediente, $cod_peluqueria)
    {
        $pagActual = 'servicio';
        $servicios = Detalle_peluqueria::where('estado',0)->where('cod_peluqueria',$cod_peluqueria)->with('peluqueria','tipo_servicio')->get();
        return view('peluqueria.atenderMascota',compact('servicios','cod_peluqueria','pagActual'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $cod_peluqueria)
    {
        DB::beginTransaction();
        try{
                $servicios = Arr::except($request, ['_token','_method'])->toArray();

                foreach ($servicios as $servicio){
                    $detalle = Detalle_peluqueria::findOrFail($servicio);
                    $detalle->fill([
                        'estado' => 1,
                    ]);
                    $detalle->save();
                }

                $pendientes = Detalle_peluqueria::where('cod_peluqueria','=',$cod_peluqueria)->where('estado','=',0)->get();
                if($pendientes->isEmpty()) {
                    $peluqueria = Peluqueria::findOrFail($cod_peluqueria);
                    $peluqueria->fill([
                        'estado' => 1,
                    ]);
                    $peluqueria->save();
                }
                DB::commit();
                return redirect()->route('peluqueria.observacion', compact('cod_peluqueria'))->with('success', 'Felicidades has terminado los servicios de la mascota');

        }catch (\Exception $e){
            DB::rollBack();
            return redirect()->route('peluqueria.atender')->with('error', 'Error al actualizar el registro');
        }

        return redirect()->route('peluqueria.atender')->with('success', 'Registro actualizado correctamente');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function obervacion($cod_peluqueria){
        $pagActual = 'servicio';
        return view('peluqueria.observacion', compact('cod_peluqueria','pagActual'));
    }

    public function gobservacion(Request $request, $cod_peluqueria){

        $validator = Validator::make($request->all(), [
            'observaciones' => ['nullable','max:300','string'],
        ]);

        if ($validator->fails()) {
            return redirect()->route('peluqueria.observacion', compact($cod_peluqueria))
                ->withErrors($validator)
                ->withInput();
        }

        $peluqueria = Peluqueria::findOrFail($cod_peluqueria);
        $peluqueria->fill([
            'observaciones' => $request->observaciones,
        ]);

        $success = $peluqueria->save();
        if(!$success){
            return redirect()->back()->with('error', 'Error al agregar la observación');
        }
        return redirect()->route('peluqueria.atender')->with('success', 'Registro actualizado correctamente');
    }
}
