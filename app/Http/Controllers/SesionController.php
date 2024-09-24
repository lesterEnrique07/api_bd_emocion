<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Clasificacion;
use App\Models\Multimedia;
use App\Models\Sesion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\SesionException;
use Illuminate\Http\Request;

class SesionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sesiones = Sesion::orderBy('id')->get();
        return response()->json($sesiones);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validate_sesion($request);
            $sesion = Sesion::create($validated);
            return response()->json([
                "sesion" => $sesion,
                "message" => "Sesion creada con éxito"
            ]);
        } catch (SesionException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Sesion $sesion)
    {
        return $sesion;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sesion $sesion)
    {
        try {
            $sesion->update($this->validate_sesion($request));
            return response()->json([
                "message" => "Actualizado con éxito",
                "sesion" => $sesion
            ]);
        } catch (SesionException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sesion $sesion)
    {
        try {
            $sesion->delete();
            return response()->json([
                "message" => "Eliminada con éxito",
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'errors' => 'No es posible eliminar porque la sesion tiene una o varias multimedias asociadas',
            ], 422);
        }
    }

    /**
     * Método que permite obtener una lista con las multimedias asociadas a una sesion especifica
     */
    public function listarMultimediasPorSesiones($sesion_id)
    {
        // Encuentra la sesión por su ID
        $sesion = Sesion::find($sesion_id);
        // Verifica si la sesión existe
        if (!$sesion) {
            return response()->json(['error' => 'Sesión no encontrada'], 404);
        }
        // Obtén las multimedias asociadas a la sesión
        $multimedia = $sesion->multimedia;
        // Retorna las multimedias en formato JSON
        return response()->json($multimedia);
    }

    private function validate_sesion(Request $request)
    {
        $rules = [
            'paciente_id'           => 'required|numeric',
            'clasificacion_id'      => 'required|numeric',   
        ];

        $messages = [
            'paciente_id.required'        => 'El identificador del paciente es un campo requerido',
            'paciente_id.numeric'         => 'El identificador del paciente solo puede tener números',
            'clasificacion_id.required'   => 'El identificador de la clasificacion es un campo requerido',
            'clasificacion_id.numeric'    => 'El identificador de la clasificacion solo puede tener números',    
        ];

        //Se crea el validador pasándole la entrada de la request, las reglas y los mensajes
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new SesionException($validator->errors()->first());
        }
        return $request->all();
    }
}
