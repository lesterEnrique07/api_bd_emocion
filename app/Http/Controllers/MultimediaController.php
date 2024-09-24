<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Multimedia;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\MultimediaException;
use Illuminate\Http\Request;

class MultimediaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $multimedias = Multimedia::orderBy('id')->get();
        return response()->json($multimedias);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validate_multimedia($request);
            $multimedia = Multimedia::create($validated);
            return response()->json([
                "multimedia" => $multimedia,
                "message" => "Multimedia creada con éxito"
            ]);
        } catch (MultimediaException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Multimedia $multimedia)
    {
        return $multimedia;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Multimedia $multimedia)
    {
        try {
            $multimedia->update($this->validate_multimedia($request));
            return response()->json([
                "message" => "Actualizado con éxito",
                "multimedia" => $multimedia
            ]);
        } catch (MultimediaException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Multimedia $multimedia)
    {
            $multimedia->delete();
            return response()->json([
                "message" => "Eliminada con éxito",
            ]);
    }

    private function validate_multimedia(Request $request)
    {
        $rules = [
            'nombre'             => 'required|string|max:80',
            'tipo'               => 'required|in:Audio,Foto',
            'direccion_url'      => 'required|string',
            'sesion_id'          => 'required|numeric',
        ];

        $messages = [
            'nombre.required'            => 'El nombre es un campo requerido',
            'nombre.string'              => 'El nombre debe ser una cadena de texto',
            'nombre.max'                 => 'El nombre debe tener un máximo de 80 caracteres',
            'tipo.required'              => 'El tipo es un campo requerido',
            'tipo.in'                    => 'El tipo solo puede tomar los valores: Audio y Foto',
            'direccion_url.required'     => 'La dirección url es un campo requerido',
            'direccion_url.string'       => 'La dirección url debe ser una cadena de texto',
            'sesion_id.required'         => 'El identificador de la sesion es un campo requerido',
            'sesion_id.numeric'          => 'El identificador de la sesion solo puede tener números',
        ];

        //Se crea el validador pasándole la entrada de la request, las reglas y los mensajes
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new MultimediaException($validator->errors()->first());
        }
        return $request->all();
    }
}
