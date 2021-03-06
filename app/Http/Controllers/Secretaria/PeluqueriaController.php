<?php

namespace App\Http\Controllers\Secretaria;


use App\Consulta;
use App\Control_vacunas;
use App\Detalle_peluqueria;
use App\Empleados;
use App\Http\Controllers\Controller;
use App\Mascota;
use App\Peluqueria;
use App\Tipo_servicio;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
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
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param null $cod_expediente
     * @return \Illuminate\Http\Response
     */
    public function create($cod_expediente = null)
    {
        // Verificar si hay consulta pendiente
        $val = Peluqueria::where('cod_expediente',$cod_expediente)->where('estado',0)->get();
        $info = null;
        if(!$val->isEmpty()){
            session()->flash('info','La mascota tiene servicios pendientes');
        }

        if($cod_expediente){
            $pagActual = 'consulta';
            $mascota = Mascota::findOrFail($cod_expediente);
            $servicios = Tipo_servicio::where('is_active',1)->get();

            $peluqueros = Empleados::whereHas('usuario',function ($consulta){
                $consulta->where('is_active',1);
            })->whereHas('usuario.tipo_usuario',function ($consulta){
                $consulta->where('cod_tipo_usuario','=',5);
            })->get();
            return view('secretaria.nuevaPeluqueria',compact('mascota','pagActual','servicios','peluqueros'));
        }
        return redirect()->back();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'cod_expediente' => ['required'],
            'cod_usuario'    => ['required']
        ]);

        if($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();
        try{
            $peluqueria = new Peluqueria;

            $peluqueria->fill([
                'cod_expediente'  => $request['cod_expediente'],
                'cod_usuario'  => $request['cod_usuario']
            ]);

            $success = $peluqueria->save();

            if($success){
                $servicios = Arr::except($request, ['cod_expediente','cod_usuario','_token'])->toArray();

                if(!$servicios){
                    return redirect()->back()->with('error', 'No hay servicios de peluqueria en la solicitud');
                }
                foreach ($servicios as $servicio){
                    $detalle = new Detalle_peluqueria;
                    $detalle->fill([
                        'cod_tipo_servicio' => $servicio,
                        'cod_peluqueria' => $peluqueria->cod_peluqueria
                    ]);
                    $detalle->save();
                }

                if($success){
                    DB::commit();
                }else{
                    DB::rollBack();
                    return redirect()->back()->with('error', 'Error al agregar el registro de detalle');
                }
            }else{
                DB::rollBack();
                return redirect()->back()->with('error', 'Error al agregar el registro de peluqueria');
            }
        }catch (\Exception $e){
            DB::rollBack();
            return redirect()->back()->with('error', 'Error al agregar el registro');
        }

        return redirect()->route('secretaria.consulta')->with('success', 'Registro agreado correctamente');
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
    public function update(Request $request, $id)
    {
        //
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
}
