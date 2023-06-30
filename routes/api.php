<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\classController;
use App\Http\Controllers\examController;
use App\Http\Controllers\calificacionController;
use App\Http\Middleware\TokenIsValid;
use App\Http\Middleware\ClassValid;

// Router Login,Register,Logout, Profile

// Controller auth


Route::group(['middleware' => 'cors'], function () {

    // routes of auth and profile

    Route::get('/all',[AuthController::class, 'All']);

    Route::get('/all2',[AuthController::class, 'All2']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [AuthController::class, 'profile'])->middleware(TokenIsValid::class);

    Route::post('/profile', [AuthController::class, 'updateProfile'])->middleware(TokenIsValid::class);

    Route::get('/verificrol', [AuthController::class, 'verificrol'])->middleware(TokenIsValid::class);

    Route::get('/enterclass/{idClass}',[classController::class, 'EnterClass'])->middleware(TokenIsValid::class);

    Route::get('/class/data',[classController::class, 'ClassData'])->middleware(ClassValid::class);

    Route::get('/studen/{id}',[AuthController::class, 'foundStuden'])->middleware(TokenIsValid::class);

    // routes of class

    Route::get('/class',[classController::class, 'ListClass'])->middleware(TokenIsValid::class);

    Route::get('/class/page/{id}',[classController::class])->middleware(TokenIsValid::class);

    Route::get('/class/{id}',[classController::class, 'DetailsClass'])->middleware(TokenIsValid::class);

    Route::get('/myclass',[classController::class, 'MyClass'])->middleware(TokenIsValid::class);

    Route::post('/class',[classController::class, 'AddClass'])->middleware(TokenIsValid::class);

    Route::get('/enrollclass/{id}',[classController::class, 'enrollclass'])->middleware(TokenIsValid::class);

    Route::put('/class',[classController::class, 'UpdateClass'])->middleware(ClassValid::class);

    Route::delete('/class',[classController::class, 'DeleteClass'])->middleware(ClassValid::class);

    Route::get('/exit',[classController::class, 'exit'])->middleware(ClassValid::class);

    // routes of class interna

    Route::get('/list/student',[classController::class, 'studentlist'])->middleware(ClassValid::class); // ver la lista de estudiantes

    Route::get('posts',[classController::class, 'posts'])->middleware(ClassValid::class); // ver publicaciones

    Route::post('posts',[classController::class, 'post'])->middleware(ClassValid::class); // crear publicaciones

    // routes of exams
    Route::post('/class/quiz',[examController::class, 'PostQuiz'])->middleware(ClassValid::class); // crear quiz

    Route::post('/quiz/question/{id}',[examController::class, 'question'])->middleware(ClassValid::class); // crear preguntas

    Route::get('/quiz',[examController::class, 'showQuiz'])->middleware(ClassValid::class); // ver lista de quiz

    Route::get('/infoquiz/{id}',[examController::class, 'InfoQuiz'])->middleware(ClassValid::class); // ver toda la informacion del quiz

    Route::get('/quiz/{id}',[examController::class, 'showquestion'])->middleware(ClassValid::class); // ver preguntas 

    Route::post('/quiz/answer/{id}',[examController::class, 'answer'])->middleware(ClassValid::class); // responder las preguntas

    Route::put('/question/{id}',[examController::class, 'putQuestion'])->middleware(ClassValid::class); // Modificar preguntas

    Route::put('/class/quiz/{id}')->middleware(ClassValid::class, 'PutQuiz'); // modificar quiz

    Route::delete('/quiz/{id}',[examController::class, 'DeleteQuiz'])->middleware(ClassValid::class); // eliminar quiz

    Route::delete('/quizAll',[examController::class, 'DeleteAllQuiz'])->middleware(ClassValid::class); // eliminar todos los quiz

    Route::post('/prub',[classController::class,'Prub']); // Prueba de subir imagenes

    // routes of calificaciones

    Route::get('/response',[calificacionController::class, 'ShowQuizEstudiantes'])->middleware(ClassValid::class); // Ver examenes respondidos por estudiantes

    Route::get('/quiz/estudiante/{idE}/{idQ}',[calificacionController::class, 'ShowQuizEstudiante'])->middleware(ClassValid::class); // ver preguntas respondidas por estudiante

    Route::put('/examQualif/{idR}',[calificacionController::class, 'PostNotQuestion'])->middleware(ClassValid::class); // calificar respuestas

    Route::post('/examQualifPost/{idE}/{idQ}',[calificacionController::class, 'PostReport'])->middleware(ClassValid::class); // Crear calificacion
    
    Route::get('/qualif/result',[calificacionController::class, 'GetReporte'])->middleware(ClassValid::class); // ver las calificaciones

    Route::get('/resultQuiz/{idQ}',[calificacionController::class, 'getList'])->middleware(ClassValid::class); // lista de preguntas y respuestas

    Route::delete('/quiz/estudiante/result/{idR}',[calificacionController::class, 'deleteReporte'])->middleware(ClassValid::class); // eliminar calificacion


});





##########################################

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
