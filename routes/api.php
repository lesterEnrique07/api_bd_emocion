<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ClasificacionController;
use App\Http\Controllers\SesionController;
use App\Http\Controllers\MultimediaController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Registrar usuario
Route::post('auth/register', [AuthController::class, 'create']);
Route::apiResource('/paciente', PacienteController::class);
// Rutas de autenticación
Route::post('auth/login', [AuthController::class, 'login']);

//Mediador para proteger las rutas
Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('/clasificacion', ClasificacionController::class);
    Route::apiResource('/sesion', SesionController::class);
    Route::apiResource('/multimedia', MultimediaController::class);
    Route::apiResource('/user', AuthController::class);
    Route::post('/clasificacion/check', [ClasificacionController::class, 'checkClasificacion']);
    Route::get('/paciente/{id}/emocion-mas-repetida', [PacienteController::class, 'emocionesMasRepetidas']);
    Route::get('/paciente/{id}/distribucion-de-emociones', [PacienteController::class, 'distribucionEmociones']);
    Route::get('/paciente/{id}/diversidad-de-emociones', [PacienteController::class, 'diversidadEmociones']);
    Route::get('/paciente/{id}/lista-de-audios', [PacienteController::class, 'getAudiosByPatient']);
    Route::get('/paciente/{id}/lista-de-fotos', [PacienteController::class, 'getFotosByPatient']);
    Route::get('/paciente/{id}/lista-de-multimedias', [PacienteController::class, 'getMultimediasByPatient']);
    Route::get('/paciente/{id}/lista-de-sesiones', [PacienteController::class, 'listarSesionesPorPaciente']);
    Route::get('/sesion/{id}/lista-de-multimedia-por-sesion', [SesionController::class, 'listarMultimediasPorSesiones']);
    //Logout
    Route::get('auth/logout', [AuthController::class, 'logout']);
});