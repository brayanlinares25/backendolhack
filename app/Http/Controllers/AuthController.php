<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class AuthController extends Controller
{
    public function All(Request $req){
        $result =  DB::select('select * from estudiante_has_lista JOIN lista ON estudiante_has_lista.lista_idlista = lista.idlista JOIN clase ON lista.clase_idclase = clase.idclase JOIN estudiante ON estudiante_has_lista.estudiante_idestudiante = estudiante.idestudiante JOIN usuario ON estudiante.usuario_idusuario = usuario.idusuario');

        return response()->json($result, 200);
    }
    public function All2(Request $req){
        $result = DB::select('SELECT * from respuesta JOIN pregunta ON respuesta.pregunta_idpregunta = pregunta.idpregunta');

        return response()->json($result,200);
    }
    public function register(Request $req){
        
            $name = $req->Nombre;
            $last = $req->Apellido;
            $email = $req->Correo;
            $doc = $req->Documento;
            $rol = $req->Rol;

            if($rol === 'profesor'){

                $result =DB::select('select * from correo where correo = ?',[$email]);
                        
                if(empty($result)){

                    $password = bcrypt($req->Contraseña);

                    DB::insert('insert into usuario (nombre, apellido, documento, contraseña) values(?, ?, ?,?);',[$name, $last, $doc, $password]);

                    $result = DB::select('select idusuario from usuario where documento = ?', [$doc]);

                    DB::insert('insert into correo (correo, idusuario) values (?, ?)',[$email, $result[0]->idusuario]);

                    DB::insert('insert into profesor (usuario_idusuario) values (?)',[$result[0]->idusuario]);

                    $token = Crypt::encryptString($result[0]->idusuario);

                    $cookie = cookie('token',$token,1440);

                    return response()->json($result,200)->cookie($cookie);
                }
                
                return response()->json('El usuario que ingresaste ya existe',500);

            }

            if($rol === 'estudiante'){

                $result = DB::select('select * from correo where correo = ?',[$email]);
                        
                if(empty($result)){

                    $password = bcrypt($req->Contraseña);

                    DB::insert('insert into usuario (nombre, apellido, documento, contraseña) values(?, ?, ?,?);',[$name, $last, $doc, $password]);

                    $result = DB::select('select idusuario from usuario where documento = ?', [$doc]);

                    DB::insert('insert into correo (correo, idusuario) values (?, ?)',[$email, $result[0]->idusuario]);

                    DB::insert('insert into estudiante (usuario_idusuario) values (?)',[$result[0]->idusuario]);

                    $token = Crypt::encryptString($result[0]->idusuario);

                    $cookie = cookie('token',$token,1440);

                    return response()->json($result,200)->cookie($cookie);
                }
                
                return response()->json('El usuario que ingresaste ya existe',500);

            }   

    } 

    public function login(Request $req){
        
        $email = $req->Correo;
        $password = $req->Contraseña;

        $result = DB::select('select * from correo where correo = ?',[$email]);

        if(empty($result)){
            return response()->json('El usuario que ingresaste no existe',400);
        }

        $result1 = DB::select('select * from usuario where idusuario = ?',[$result[0]->idusuario]);

        $verif = password_verify($password,$result1[0]->contraseña);

        if($verif == false){
            return response()->json('Contraseña incorrecta',401);
        }

        $resultPR = DB::select('select * from profesor where usuario_idusuario = ?',[$result1[0]->idusuario]);

        if(empty($resultPR)){

            $resultES = DB::select('select * from estudiante where usuario_idusuario = ?', [$result1[0]->idusuario]);

            $token = Crypt::encryptString($resultES[0]->usuario_idusuario);

            $cookie = cookie('token',$token,1440);

            $result = DB::select('SELECT usuario.nombre, usuario.apellido, usuario.documento, correo.correo ,estudiante.rol FROM estudiante JOIN usuario ON estudiante.usuario_idusuario = usuario.idusuario JOIN correo ON usuario.idusuario = correo.idusuario WHERE estudiante.idestudiante = ?',[$resultES[0]->idestudiante]);

            return response()->json($result,200)->cookie($cookie);
            
        }
        else{

            $token = Crypt::encryptString($resultPR[0]->usuario_idusuario);

            $cookie = cookie('token',$token,1440);

            $result = DB::select('SELECT usuario.nombre, usuario.apellido, usuario.documento, correo.correo ,profesor.rol FROM profesor JOIN usuario ON profesor.usuario_idusuario = usuario.idusuario JOIN correo ON usuario.idusuario = correo.idusuario WHERE profesor.idprofesor = ?',[$resultPR[0]->idprofesor]);

            return response()->json($result,200)->cookie($cookie);

        }

    }

    public function logout(){
        $cookie = cookie('token',' ', 1);

        return response()->json('true',200)->cookie($cookie);
    }

    public function profile(Request $req){

        $id = $req->id;

        $rol = $req->rol;

        if($rol === 'profesor'){

            $result = DB::select('SELECT usuario.nombre, usuario.apellido, usuario.img ,usuario.documento, correo.correo, telefono.numero ,usuario.biografia ,profesor.rol FROM profesor JOIN usuario ON profesor.usuario_idusuario = usuario.idusuario JOIN correo ON usuario.idusuario = correo.idusuario LEFT JOIN telefono ON usuario.idusuario = telefono.idusuario WHERE profesor.idprofesor = ?',[$id]);

            return response()->json($result,200); 
        }else if($rol === 'estudiante'){
            $result = DB::select('SELECT usuario.nombre, usuario.apellido, usuario.img ,usuario.documento, correo.correo, telefono.numero, usuario.biografia ,estudiante.rol FROM estudiante JOIN usuario ON estudiante.usuario_idusuario = usuario.idusuario JOIN correo ON usuario.idusuario = correo.idusuario LEFT JOIN telefono ON usuario.idusuario = telefono.idusuario WHERE estudiante.idestudiante = ?',[$id]);

            return response()->json($result,200);
            
        }

    }

    public function updateProfile(Request $req){

        $id = $req->id;

        $name = $req->nombre;
        $last = $req->apellido;
        $bio = $req->biografia;
        $cel = $req->telefono;

        $rol = $req->rol;

        if($rol === 'profesor'){
            if($req->hasFile('image')){
                    $img = $req->file('image');

                    $path = $img->getRealPath();

                    $resultIMG = Cloudinary::upload($path,['folder' => 'perfilesDolhack']);

                    $url = $resultIMG->getSecurePath();

                    $Consult = DB::select('SELECT * from profesor where idprofesor = ?',[$id]);

                    DB::delete('DELETE from telefono where idusuario = ?',[$Consult[0]->usuario_idusuario]);

                    DB::insert("INSERT into telefono (numero,idusuario) values (?, ?)",[$cel, $Consult[0]->usuario_idusuario]); 

                    $result = DB::update("UPDATE usuario set nombre = ? , apellido = ?, biografia = ?, img = ? where idusuario in (SELECT usuario_idusuario from profesor where idprofesor = ?)",[$name,$last, $bio,$url, $id]);

                    return response()->json([$result, $name, $bio, $id],200);
            }
            
            $Consult = DB::select('SELECT * from profesor where idprofesor = ?',[$id]);

            DB::delete('DELETE from telefono where idusuario = ?',[$Consult[0]->usuario_idusuario]);

            DB::insert("INSERT into telefono (numero,idusuario) values (?, ?)",[$cel, $Consult[0]->usuario_idusuario]);

            $result = DB::update("UPDATE usuario set nombre = ? , apellido = ?, biografia = ? where idusuario = ?",[$name, $last, $bio, $id]);

            if($result == 1){
                $result1 = DB::select('select usuario.Nombre, usuario.Apellido , usuario.Correo, usuario.Biografia, usuario.Telefono, usuario.Documento, usuario.Imagen  ,rol.NombreRol from usuario join rol on usuario.CodRol = rol.CodRol where usuario.IdUsuario = ?;',[$id]);
             
                return response()->json($result1,200);
           }
 
        }

        if($req->hasFile('image')){
                $img = $req->file('image');

                $path = $img->getRealPath();

                $resultIMG = Cloudinary::upload($path,['folder' => 'perfilesDolhack']);

                $url = $resultIMG->getSecurePath();

                $Consult = DB::select('SELECT * from estudiante where idestudiante = ?',[$id]);

                DB::delete('DELETE from telefono where idusuario = ?',[$Consult[0]->usuario_idusuario]);

                DB::insert("INSERT into telefono (numero,idusuario) values (?, ?)",[$cel, $Consult[0]->usuario_idusuario]);

                $result = DB::update("UPDATE usuario set nombre = ? , apellido = ?, biografia = ?, img = ? where idusuario in (select usuario_idusuario from estudiante where idestudiante = ?)",[$name,$last, $bio,$url, $id]);

                return response()->json([$result, $name, $bio, $id],200);
        }

        $Consult = DB::select('SELECT * from estudiante where idestudiante = ?',[$id]);

        DB::delete('DELETE from telefono where idusuario = ?',[$Consult[0]->usuario_idusuario]);

        DB::insert("INSERT into telefono (numero,idusuario) values (?, ?)",[$cel, $Consult[0]->usuario_idusuario]);

        $result = DB::update("UPDATE usuario set nombre = ? , apellido = ?, biografia = ? where idusuario = ?",[$name, $last, $bio, $id]);

        if($result == 1){
            $result1 = DB::select('select usuario.Nombre, usuario.Apellido , usuario.Correo, usuario.Biografia, usuario.Telefono, usuario.Documento, usuario.Imagen  ,rol.NombreRol from usuario join rol on usuario.CodRol = rol.CodRol where usuario.IdUsuario = ?;',[$id]);
             
            return response()->json($result1,200);
        }
    }

    public function verificrol(Request $req){
        $id = $req->id;

        $rol = $req->rol;

        return response()->json([$rol], 200);
    }
    
    // Terminar esto xd

    public function foundStuden(Request $req, string $id){
        $result = DB::select('SELECT usuario.nombre, usuario.apellido, usuario.img ,usuario.documento, correo.correo, telefono.numero, usuario.biografia ,estudiante.rol FROM estudiante JOIN usuario ON estudiante.usuario_idusuario = usuario.idusuario JOIN correo ON usuario.idusuario = correo.idusuario LEFT JOIN telefono ON usuario.idusuario = telefono.idusuario WHERE estudiante.idestudiante = ?',[$id]);

        return response()->json($result,200);
    }
}

/*
{
  "Nombre":"Test",
  "Apellido":"Epico",
  "Biografia":"hola soy un profesor de la naza quieres vermela?",
  "Telefono": 32254623,
  "Correo":"Test8@gmail.com",
  "Contraseña":"1234",
  "Documento":46456464,
  "CodRol":"PROF"
}
*/ 
