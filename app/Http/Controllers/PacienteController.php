<?php

namespace App\Http\Controllers;

use App\Models\Sesion;
use App\Models\Multimedia;
use App\Models\Paciente;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Exceptions\PacienteException;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pacientes = Paciente::orderBy('id')->get();
        return response()->json($pacientes);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $this->validate_paciente($request);
            $paciente = Paciente::create($validated);
            return response()->json([
                "paciente" => $paciente,
                "message" => "Paciente creado con éxito"
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return response()->json([
                'errors' => 'Ya existe un paciente con ese Carné de Identidad, correo o usuario',
            ], 422);
        } catch (PacienteException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $usuario)
    {
        $paciente = Paciente::where('usuario', $usuario)->first();
        if (!$paciente) {
            return response()->json(['message' => 'No hay pacientes registrados con ese usuario'], 404);
        }
        return response()->json($paciente);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Paciente $paciente)
    {
        try {
            $paciente->update($this->validate_paciente($request));
            //Preparar mensaje
            $message = "Actualizado con éxito";
            return response()->json([
                "message" => $message,
                "paciente" => $paciente
            ]);
        } catch (UniqueConstraintViolationException $exception) {
            return response()->json([
                'errors' => 'Ya existe un paciente con ese Carné de Identidad, correo o usuario',
            ], 422);
        } catch (PacienteException $exception) {
            return response()->json(['errors' => $exception->getMessage()], 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Paciente $paciente)
    {
        try {
            $paciente->delete();
            return response()->json([
                "message" => "Eliminado con éxito",
            ]);
        } catch (QueryException $exception) {
            return response()->json([
                'errors' => 'No es posible eliminar porque el paciente tiene una o varias sesiones registradas',
            ], 422);
        }
    }

    /**
     * Método que permite obtener la emoción más repetida en la clasificación del audio, las fotos y audio y fotos juntos
     * asociadas a un paciente específico.
     */
    public function emocionesMasRepetidas($paciente_id)
    {
        $emocionAudio = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_audio', DB::raw('count(clasificacions.emocion_audio) as count'))
            ->groupBy('clasificacions.emocion_audio')
            ->orderBy('count', 'desc')
            ->first();
        $emocionFoto = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_foto', DB::raw('count(clasificacions.emocion_foto) as count'))
            ->groupBy('clasificacions.emocion_foto')
            ->orderBy('count', 'desc')
            ->first();
        $emocionAudioFoto = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_audio_foto', DB::raw('count(clasificacions.emocion_audio_foto) as count'))
            ->groupBy('clasificacions.emocion_audio_foto')
            ->orderBy('count', 'desc')
            ->first();
        return response()->json([
            'emocion_audio' => [
                'emocion' => $emocionAudio->emocion_audio,
                'cantidad' => $emocionAudio->count
            ],
            'emocion_foto' => [
                'emocion' => $emocionFoto->emocion_foto,
                'cantidad' => $emocionFoto->count
            ],
            'emocion_audio_foto' => [
                'emocion' => $emocionAudioFoto->emocion_audio_foto,
                'cantidad' => $emocionAudioFoto->count
            ]
        ]);
    }

    /**
     * Método que permite obtener la distribución de emociones en la clasificación del audio, las fotos y audio y fotos juntos
     * asociadas a un paciente específico.
     */
    public function distribucionEmociones($paciente_id)
    {
        $emociones = ['Asco', 'Felicidad', 'Ira', 'Miedo', 'Neutralidad', 'Tristeza'];
        $emocionAudio = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_audio', DB::raw('count(clasificacions.emocion_audio) as count'))
            ->groupBy('clasificacions.emocion_audio')
            ->get()
            ->pluck('count', 'emocion_audio')
            ->toArray();
        $emocionFoto = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_foto', DB::raw('count(clasificacions.emocion_foto) as count'))
            ->groupBy('clasificacions.emocion_foto')
            ->get()
            ->pluck('count', 'emocion_foto')
            ->toArray();
        $emocionAudioFoto = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_audio_foto', DB::raw('count(clasificacions.emocion_audio_foto) as count'))
            ->groupBy('clasificacions.emocion_audio_foto')
            ->get()
            ->pluck('count', 'emocion_audio_foto')
            ->toArray();
        $distribucion = [
            'emocion_audio' => [],
            'emocion_foto' => [],
            'emocion_audio_foto' => []
        ];
        foreach ($emociones as $emocion) {
            $distribucion['emocion_audio'][$emocion] = $emocionAudio[$emocion] ?? 0;
            $distribucion['emocion_foto'][$emocion] = $emocionFoto[$emocion] ?? 0;
            $distribucion['emocion_audio_foto'][$emocion] = $emocionAudioFoto[$emocion] ?? 0;
        }
        return response()->json($distribucion);
    }

    /**
     * Método que permite obtener la diversidad de emociones en la clasificación del audio, las fotos y audio y fotos juntos
     * asociadas a un paciente específico.
     */
    public function diversidadEmociones($paciente_id)
    {
        $emociones = ['Asco', 'Felicidad', 'Ira', 'Miedo', 'Neutralidad', 'Tristeza'];
        $emocionAudio = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_audio', DB::raw('count(clasificacions.emocion_audio) as count'))
            ->groupBy('clasificacions.emocion_audio')
            ->get()
            ->pluck('count', 'emocion_audio')
            ->toArray();
        $emocionFoto = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_foto', DB::raw('count(clasificacions.emocion_foto) as count'))
            ->groupBy('clasificacions.emocion_foto')
            ->get()
            ->pluck('count', 'emocion_foto')
            ->toArray();
        $emocionAudioFoto = DB::table('sesions')
            ->join('clasificacions', 'sesions.clasificacion_id', '=', 'clasificacions.id')
            ->where('sesions.paciente_id', $paciente_id)
            ->select('clasificacions.emocion_audio_foto', DB::raw('count(clasificacions.emocion_audio_foto) as count'))
            ->groupBy('clasificacions.emocion_audio_foto')
            ->get()
            ->pluck('count', 'emocion_audio_foto')
            ->toArray();
        $totalAudio = array_sum($emocionAudio);
        $totalFoto = array_sum($emocionFoto);
        $totalAudioFoto = array_sum($emocionAudioFoto);
        $diversidad = [
            'emocion_audio' => [],
            'emocion_foto' => [],
            'emocion_audio_foto' => []
        ];
        foreach ($emociones as $emocion) {
            $diversidad['emocion_audio'][$emocion] = $totalAudio ? ($emocionAudio[$emocion] ?? 0) / $totalAudio * 100 : 0;
            $diversidad['emocion_foto'][$emocion] = $totalFoto ? ($emocionFoto[$emocion] ?? 0) / $totalFoto * 100 : 0;
            $diversidad['emocion_audio_foto'][$emocion] = $totalAudioFoto ? ($emocionAudioFoto[$emocion] ?? 0) / $totalAudioFoto * 100 : 0;
        }
        return response()->json($diversidad);
    }

    /**
     * Método que permite obtener todos los audios asociados a un paciente especifico divididos por emociones
     */
    public function getAudiosByPatient($pacienteId)
    {
        // Obtener todas las sesiones del paciente
        $sesiones = Sesion::where('paciente_id', $pacienteId)->pluck('id');
        // Obtener todas las multimedias de tipo Audio asociadas a las sesiones del paciente
        $audios = Multimedia::whereIn('sesion_id', $sesiones)
                    ->where('tipo', 'Audio')
                    ->get();
        // Inicializar un array para almacenar los audios divididos por emociones
        $audiosPorEmocion = [
            'Asco' => [],
            'Felicidad' => [],
            'Ira' => [],
            'Miedo' => [],
            'Neutralidad' => [],
            'Tristeza' => []
        ];
        // Clasificar los audios por emoción
        foreach ($audios as $audio) {
            $emocion = $audio->sesion->clasificacion->emocion_audio;
            if (array_key_exists($emocion, $audiosPorEmocion)) {
                $audiosPorEmocion[$emocion][] = $audio;
            }
        }
        return response()->json($audiosPorEmocion);
    }

    /**
     * Método que permite obtener todas las fotos asociadas a un paciente especifico divididos por emociones
     */
    public function getFotosByPatient($pacienteId)
    {
        // Obtener todas las sesiones del paciente
        $sesiones = Sesion::where('paciente_id', $pacienteId)->pluck('id');
        // Obtener todas las multimedias de tipo Foto asociadas a las sesiones del paciente
        $fotos = Multimedia::whereIn('sesion_id', $sesiones)
                    ->where('tipo', 'Foto')
                    ->get();
        // Inicializar un array para almacenar las fotos divididas por emociones
        $fotosPorEmocion = [
            'Asco' => [],
            'Felicidad' => [],
            'Ira' => [],
            'Miedo' => [],
            'Neutralidad' => [],
            'Tristeza' => []
        ];
        // Clasificar las fotos por emoción
        foreach ($fotos as $foto) {
            $emocion = $foto->sesion->clasificacion->emocion_foto;
            if (array_key_exists($emocion, $fotosPorEmocion)) {
                $fotosPorEmocion[$emocion][] = $foto;
            }
        }
        return response()->json($fotosPorEmocion);
    }

    public function getMultimediasByPatient($pacienteId)
    {
        // Obtener todas las sesiones del paciente
        $sesiones = Sesion::where('paciente_id', $pacienteId)->pluck('id');
        // Obtener todas las multimedias asociadas a las sesiones del paciente
        $multimedias = Multimedia::whereIn('sesion_id', $sesiones)->get();
        // Inicializar un array para almacenar las multimedias divididas por emociones
        $multimediasPorEmocion = [
            'Asco' => [],
            'Felicidad' => [],
            'Ira' => [],
            'Miedo' => [],
            'Neutralidad' => [],
            'Tristeza' => []
        ];
        // Clasificar las multimedias por emoción
        foreach ($multimedias as $multimedia) {
            $emocion = $multimedia->sesion->clasificacion->emocion_audio_foto;
            if (array_key_exists($emocion, $multimediasPorEmocion)) {
                $multimediasPorEmocion[$emocion][] = $multimedia;
            }
        }
        return response()->json($multimediasPorEmocion);
    }

    /**
     * Método que permite obtener una lista con las sesiones asociadas a un paciente especifico
     */
    public function listarSesionesPorPaciente($paciente_id)
    {
        $sesiones = DB::table('sesions')
        ->where('paciente_id', $paciente_id)
        ->select('id as sesion_id', 'fecha', 'clasificacion_id')
        ->get();
    return response()->json($sesiones);
    }

    private function validate_paciente(Request $request)
    {
        $rules = [
            'nombre'             => 'required|string|max:50',
            'apellido'           => 'required|string|max:50',
            'ci'                 => 'required|regex:/^[0-9]+$/|size:11',
            'fecha_nacimiento'   => 'required|date',
            'sexo'               => 'required|in:Femenino,Masculino',
            'direccion'          => 'required|string',
            'telefono'           => 'required|regex:/^[1-9][0-9]*$/|size:8',
            'correo'             => 'required|string|email|max:100',
            'usuario'            => 'required|string|min:5|max:20|alpha_num',
            'contrasena'         => 'required|string|size:8|regex:/[A-Z]/|regex:/[0-9]/|regex:/[@$!%*?&#]/',
        ];

        $messages = [
            'nombre.required'            => 'El nombre es un campo requerido',
            'nombre.string'              => 'El nombre debe ser una cadena de texto',
            'nombre.max'                 => 'El nombre debe tener un máximo de 50 caracteres',
            'apellido.required'          => 'Los apellidos es un campo requerido',
            'apellido.string'            => 'Los apellidos deben ser una cadena de texto',
            'apellido.max'               => 'Los apellidos deben tener un máximo de 50 caracteres',
            'ci.required'                => 'El Carné de Identidad es un campo requerido',
            'ci.regex'                   => 'El Carné de Identidad debe contener solo números',
            'ci.size'                    => 'El Carné de Identidad debe contener exáctamente 11 caracteres',
            'fecha_nacimiento.required'  => 'La fecha de nacimiento es un campo requerido',
            'fecha_nacimiento.date'      => 'La fecha debe tener un formato de fecha válido',
            'sexo.required'              => 'El sexo es un campo requerido',
            'sexo.in'                    => 'El sexo solo puede tomar los valores: Femenino(F) y Masculino(M)',
            'direccion.required'         => 'La dirección es un campo requerido',
            'direccion.string'           => 'La dirección debe ser una cadena de texto',
            'telefono.required'          => 'El teléfono es un campo requerido',
            'telefono.size'              => 'El teléfono debe tener 8 números',
            'telefono.regex'             => 'El teléfono debe contener solo números',
            'correo.required'            => 'El correo es un campo requerido',
            'correo.string'              => 'El correo debe ser una cadena de texto',
            'correo.email'               => 'El correo debe tener un formato de correo válido',
            'correo.max'                 => 'El correo debe tener un máximo de 100 caracteres',
            'usuario.required'           => 'El usuario es un campo requerido',
            'usuario.string'             => 'El usuario debe ser una cadena de texto',
            'usuario.min'                => 'El usuario debe tener un mínimo de 5 cracteres',
            'usuario.max'                => 'El usuario debe tener un máximo de 20 cracteres',
            'usuario.alpha_num'          => 'El usuario debe contener solo caracteres alfanuméricos (letras y números)',
            'contrasena.required'        => 'La contraseña es un campo requerido',
            'contrasena.string'          => 'La contraseña debe ser una cadena de texto',
            'contrasena.size'            => 'La contraseña debe contener exáctamente 8 caracteres',
            'contrasena.regex'           => 'La contraseña debe contener al menos una letra mayúscula, al menos un dígito y al menos un caracter especial (@, $, !, %, *, ?, &, #)',

        ];

        //Se crea el validador pasándole la entrada de la request, las reglas y los mensajes
        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            throw new PacienteException($validator->errors()->first());
        }
        return $request->all();
    }
}
