<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

class TokenIsValid
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

            return $next($req);

        }else{
            
            $resultES = DB::select('select * from estudiante where  usuario_idusuario = ?',[$id]);

            if(empty($resultES)){
                return response()->json('token invalid',500);
            }

            $req->id = $resultES[0]->idestudiante;

            $req->rol = $resultES[0]->rol;

            return $next($req);
 
        }

   }
}
