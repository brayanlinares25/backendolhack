<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class calificacionController extends Controller
{
    public function ShowQuizEstudiantes(Request $req){
        $idC = $req->idClass;

        // arreglado xd

        $result = DB::select('SELECT DISTINCT estudiante.idestudiante ,quiz.idquiz ,quiz.titulo, usuario.nombre, usuario.apellido FROM quiz JOIN pregunta ON quiz.idquiz = pregunta.quiz_idquiz JOIN respuesta ON pregunta.idpregunta = respuesta.pregunta_idpregunta JOIN estudiante ON respuesta.estudiante_idestudiante = estudiante.idestudiante JOIN usuario ON estudiante.usuario_idusuario = usuario.idusuario where quiz.clase_idclase = ?',[$idC]);

        return response()->json($result,200);
    }

    public function ShowQuizEstudiante(Request $req, string $idE, string $idQ){
        $idC = $req->idClass;

        // arreglado

        $resul = DB::select(' SELECT respuesta.idrespuesta, estudiante.idestudiante ,pregunta.pregunta, respuesta.respuesta, usuario.nombre, usuario.apellido FROM respuesta JOIN pregunta ON respuesta.pregunta_idpregunta = pregunta.idpregunta JOIN estudiante ON respuesta.estudiante_idestudiante = estudiante.idestudiante JOIN usuario ON estudiante.usuario_idusuario = usuario.idusuario JOIN quiz ON pregunta.quiz_idquiz = quiz.idquiz WHERE quiz.clase_idclase = ? AND estudiante.idestudiante = ? AND quiz.idquiz = ? ;',[$idC, $idE, $idQ]);

        return response()->json($resul,200);
    }

    public function PostNotQuestion(Request $req, string $idR){
        $rol = $req->rol;

        $cal = $req->nota;

        if($rol === 'profesor'){
            $result = DB::update('UPDATE respuesta set calificación = ? where idrespuesta = ?',[$cal, $idR]);

            return response()->json($result, 200);
        }else{
            return response()->json('Solo el profesor puede calificar', 500);
        }
    }

    public function PostReport(Request $req, string $idE, string $idQ){
        $idC = $req->idClass;
        $rol = $req->rol;

        $cal = $req->calificacion;

        // Arreglado

        if($rol === 'profesor'){

            $valid = DB::select('SELECT * from reporte where estudiante_idestudiante = ? AND idquiz = ?', [$idE, $idQ]);

            if(empty($valid)){
                $resul = DB::insert('INSERT into reporte( estudiante_idestudiante, idquiz, clase_idclase,  calificaciontotal ) values (?, ?, ?, ?)',[$idE, $idQ, $idC, $cal]);

                return response()->json($resul, 200);
 
            }else{
                $resul = DB::update('UPDATE reporte set calificaciontotal = ? where estudiante_idestudiante = ? and idquiz = ?', [$cal, $idE, $idQ]);

                return response()->json($resul, 200);
            }

        }else{
            return response()->json('Solo el profesor puede calificar',500);
        }
    }

    public function GetReporte(Request $req){
        $idC = $req->idClass;

        $id = $req->id;

        $result = DB::select('select  quiz.idquiz , quiz.titulo, quiz.calificacion ,reporte.calificaciontotal from reporte left join quiz on reporte.idquiz  = quiz.idquiz where reporte.estudiante_idestudiante = ? AND quiz.clase_idclase = ?;',[$id, $idC]);

        return response()->json($result, 200);
    }

    public function deleteReporte(Request $req, string $idR){
        $idC = $req->idClass;

        $result = DB::delete('DELETE from reporte where idreporte = ?',[$idR]);

        return response()->json($result,200);
    }

    public function getList(Request $req, string $idQ){
        $idC = $req->idClass;
        $id = $req->id;

        $result = DB::select('SELECT pregunta.pregunta, respuesta.respuesta, respuesta.calificación from respuesta JOIN pregunta ON respuesta.idrespuesta = pregunta.idpregunta where pregunta.quiz_idquiz = ? and respuesta.estudiante_idestudiante = ?;',[$idQ, $id]);

        return response()->json($result);
    }
}
