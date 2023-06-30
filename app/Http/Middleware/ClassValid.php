<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class ClassValid
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $req, Closure $next): Response
    {
        $result = $req->cookie('token');

        if(empty($result)){
            return response()->json('authorization denied',401);
        }

        $id = Crypt::decryptString($result);

        $resultES = DB::select('select * from estudiante where usuario_idusuario = ?',[$id]);

        if(empty($resultES)){
            $resultPR = DB::select('select * from profesor where  usuario_idusuario = ?',[$id]);

            if(empty($resultPR)){
                return response()->json('token invalid',500);
            }

            $req->id = $resultPR[0]->idprofesor;
            $req->rol = $resultPR[0]->rol;

            //clase

            $result2 = $req->cookie('Class');

            if(empty($result2)){
                return response()->json('authorization denied of class',401);
            }

            $idClass = Crypt::decryptString($result2);

            $valid = DB::select('SELECT idclase from clase where idclase = ?',[$idClass]);
            
            if(empty($valid)){
                return response()->json('Clase invalida', 403);
            }

            $valid = DB::select('SELECT idclase from clase where idclase = ? and profesor_idprofesor = ?',[$idClass, $resultPR[0]->idprofesor]);

            if(empty($valid)){
                return response()->json('esta clase no te pertenece',500);
            }                       

            $req->idClass = $idClass;

            return $next($req);
        }

        $req->id = $resultES[0]->idestudiante;

        $req->rol = $resultES[0]->rol;

        //clase

        $result2 = $req->cookie('Class');

        if(empty($result2)){
            return response()->json('authorization denied of class',401);
        }

        $idClass = Crypt::decryptString($result2);

        $valid = DB::select('SELECT idclase from clase where idclase = ?',[$idClass]);
            
        if(empty($valid)){
            return response()->json('Clase invalida', 403);
        }

        $valid = DB::select('SELECT clase.titulo, clase.idclase from estudiante_has_lista join estudiante on estudiante_idestudiante = estudiante.idestudiante join lista on estudiante_has_lista.lista_idlista = lista.idlista join clase on lista.clase_idclase = clase.idclase where estudiante.idestudiante = ? AND clase.idclase = ?;',[$resultES[0]->idestudiante, $idClass]);

        if(empty($valid)){
            return response()->json('No estas registrado a esta clase',500);
        }                       

        $req->idClass = $idClass;

        return $next($req);
 
   }
}
