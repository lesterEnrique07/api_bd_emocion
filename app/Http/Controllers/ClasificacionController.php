<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Clasificacion;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\ClasificacionException;
use Illuminate\Http\Request;

class ClasificacionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clasificaciones = Clasificacion::orderBy('id')->get();
        return response()->json($clasificaciones);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validate_clasificacion($request);
            $clasificacion = Clasificacion::create($validated);
            return response()->json([
                "clasificacion" => $clasificacion,
                "message" => "Clasificacion creada con éxito"
            ]);
        } catch (ClasificacionException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $clasificacion = Clasificacion::where('id', $id)->first();
        if (!$clasificacion) {
            return response()->json(['message' => 'No hay clasificaciones registradas con ese id'], 404);
        }
        return response()->json($clasificacion);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Clasificacion $clasificacion)
    {
        try {
            $clasificacion->update($this->validate_clasificacion($request));
            return response()->json([
                "message" => "Actualizado con éxito",
                "clasificacion" => $clasificacion
            ]);
        } catch (ClasificacionException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Clasificacion $clasificacion)
    {
        try {
            $clasificacion->delete();
            return response()->json([
                "message" => "Eliminada con éxito",
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'errors' => 'No es posible eliminar porque la clasificacion tiene una o varias sesiones asociadas',
            ], 422);
        }
    }

    /**
     * Método para verificar si una clasificación ya existe, si exite se devuelve el id, si no existe se devuelve null
     */
    public function checkClasificacion(Request $request)
    {
        try {
            $validated = $this->validate_clasificacion($request);
            $clasificacion = Clasificacion::where($validated)->first();
            if ($clasificacion) {
                return response()->json(['id' => $clasificacion->id]);
            } else {
                return response()->json(['id' => null]);
            }
        } catch (ClasificacionException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    

    private function validate_clasificacion(Request $request)
    {
        $rules = [
            'emocion_audio'           => 'required|in:Asco,Felicidad,Ira,Miedo,Neutralidad,Tristeza',
            'emocion_foto'            => 'required|in:Asco,Felicidad,Ira,Miedo,Neutralidad,Tristeza',
            'emocion_audio_foto'      => 'required|in:Asco,Felicidad,Ira,Miedo,Neutralidad,Tristeza',
        ];

        $messages = [
            'emocion_audio.required'        => 'La clasificación de la emoción del audio es un campo requerido',
            'emocion_audio.in'              => 'La clasificación de la emoción del audio solo puede tomar los valores: Asco, Felicidad, Ira, Miedo, Neutralidad y Tristeza',
            'emocion_foto.required'         => 'La clasificación de la emoción de las fotos es un campo requerido',
            'emocion_foto.in'               => 'La clasificación da la emoción de las fotos solo puede tomar los valores: Asco, Felicidad, Ira, Miedo, Neutralidad y Tristeza',
            'emocion_audio_foto.required'   => 'La clasificación de la emoción del audio y las fotos juntos es un campo requerido',
            'emocion_audio_foto.in'         => 'La clasificación de la emoción del audio y las fotos juntos solo puede tomar los valores: Asco, Felicidad, Ira, Miedo, Neutralidad y Tristeza',    
        ];

        //Se crea el validador pasándole la entrada de la request, las reglas y los mensajes
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new ClasificacionException($validator->errors()->first());
        }
        return $request->all();
    }
}
