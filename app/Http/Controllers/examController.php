<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;


class examController extends Controller
{
    public function PostQuiz(Request $req){
        
        $id = $req->id;
        $rol = $req->rol;
        $idC = $req->idClass;
        
        $title = $req->titulo;
        $descripcion = $req->descripcion;
        $calificacion =$req->calificacion;
        
        if($rol === 'profesor'){
            $result = DB::insert('INSERT into  quiz (titulo, descripcion, calificacion, profesor_idprofesor, clase_idclase) values (?,?,?,?,?);',[$title,$descripcion, $calificacion,$id, $idC]);
            if($result == true){
                $result = DB::select('SELECT idquiz from quiz where profesor_idprofesor = ? AND clase_idclase = ? AND titulo = ?', [$id, $idC, $title]);
                return response()->json($result,200);
            }else{
                return response()->json('Ubo un error',500);
            }
        }else {
            return response()->json('Solo el profesor puede crear quiz',500);
        }
    }

    public function showQuiz(Request $req){
        $idC = $req->idClass;
        
        $result = DB::select('SELECT idquiz, titulo, descripcion, calificacion from quiz where clase_idclase = ?',[$idC]);
        
        return response()->json($result, 200);
    }

    public function InfoQuiz(Request $req, string $idE){
        $id = $req->idClass;

        $result = DB::select('SELECT titulo, descripcion from quiz where clase_idclase = ? and idquiz = ?',[$id, $idE]);

        return response()->json($result, 200);
    }

    public function question(Request $req, string $id){
        $rol = $req->rol;

        $question = $req->pregunta;

        if($rol === 'profesor'){

            $result = DB::insert('INSERT INTO pregunta(pregunta, quiz_idquiz) values (?,?)',[$question, $id]);

            return response()->json($result,200);            

        }else{
            return response()->json('Un estudiante no puede hacer preguntas',500);
        }
    }
 
    public function showquestion(Request $req, string $id){
        $idC = $req->idClass;

        $result = DB::select('SELECT * from pregunta where quiz_idquiz = ?',[$id]);

        return response()->json($result,200);
    }

    public function answer(Request $req, string $id){
        $rol = $req->rol;
        $idE = $req->id;

        $respuesta = $req->respuesta;

        if($rol === 'estudiante'){
            $result = DB::insert('INSERT into respuesta (respuesta, pregunta_idpregunta, estudiante_idestudiante) values(?, ?, ?)',[$respuesta, $id, $idE]);

            return response()->json($result,200);
        }else{
            return response()->json('Profesor no puedes contestar tus preguntas',500);
        }
    }

    public function putQuestion(Request $req, string $id){
        $pre = $req->pregunta;

        $result = DB::update("UPDATE pregunta set pregunta = ? where idpregunta = ? ",[$pre, $id]);

        return response()->json($result, 200);
    }

    public function PutQuiz(Request $req, string $id){
        $idP = $req->id;
        $rol = $req->rol;
        $idC = $req->idClass;
        
        $title = $req->titulo;
        $descripcion = $req->descripcion;
        $calificacion =$req->calificacion;
        
        if($rol === 'profesor'){
            $result = DB::update('UPDATE quiz set titulo = ?, descripcion = ?, calificacion = ? where idquiz = ?',[$title,$descripcion, $calificacion, $id]);
            if($result == true){
                return response()->json('Quiz creado',200);
            }else{
                return response()->json('Ubo un error',500);
            }
        }else {
            return response()->json('Solo el profesor puede modificar quiz',500);
        }        
    }

    public function DeleteQuiz(Request $req, string $id){
        $rol = $req->rol;

        if($rol === 'profesor'){
            DB::delete('DELETE from respuesta where pregunta_idpregunta in (select idpregunta from pregunta where quiz_idquiz = ?)',[$id]);

            DB::delete('DELETE from pregunta where quiz_idquiz = ? ',[$id]);

            $result = DB::delete('DELETE from quiz where idquiz = ?',[$id]);

            return response()->json($result,200);
        }else {
            return response()->json('Solo el profesor puede eliminar quiz',500);
        }
    }

    public function DeleteAllQuiz(Request $req){
        $idC = $req->idClass;

        DB::delete('DELETE FROM respuesta WHERE pregunta_idpregunta IN (SELECT idpregunta FROM pregunta WHERE quiz_idquiz IN (SELECT idquiz FROM quiz WHERE clase_idclase = ?))',[$idC]);   
        
        DB::delete('DELETE FROM pregunta WHERE quiz_idquiz IN (SELECT idquiz FROM quiz WHERE clase_idclase = ?)',[$idC]);

        DB::delete('DELETE FROM quiz WHERE clase_idclase = ?;',[$idC]);

        return response()->json('Se elimino todos los quiz',200);
    }
}   
