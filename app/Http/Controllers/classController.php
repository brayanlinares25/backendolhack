<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;

class classController extends Controller
{
    public function ListClass(Request $req){
        $result = DB::select('select idclase,titulo from clase');

        return response()->json($result,200);
    }

    public function DetailsClass(Request $req, string $id){
     
        $result = DB::select('SELECT clase.idclase  , clase.titulo, clase.descripcion, clase.fecha_inicio, clase.fecha_finalizacion  ,tipo.codnombre ,nivel.nivnombre, usuario.nombre, usuario.apellido FROM clase JOIN nivel ON clase.nivel_codnivel = nivel.codnivel JOIN tipo ON clase.tipo_codtipo = tipo.codtipo JOIN profesor ON clase.profesor_idprofesor = profesor.idprofesor JOIN usuario ON profesor.usuario_idusuario = usuario.idusuario WHERE clase.idclase = ?',[$id]); 

        return response()->json($result,200);

    }

    public function MyClass(Request $req){
        $id = $req->id;

        $rol = $req->rol;

        if($rol === 'profesor'){
            $result = DB::select('SELECT idclase, titulo FROM clase WHERE profesor_idprofesor = ?',[$id]);

            if(empty($result)){
                return response()->json('aún no tienes una clase creada',403);
            }

            return response()->json($result,200);
        }

        $result = DB::select('SELECT clase.titulo, clase.idclase from estudiante_has_lista join estudiante on estudiante_idestudiante = estudiante.idestudiante join lista on estudiante_has_lista.lista_idlista = lista.idlista join clase on lista.clase_idclase = clase.idclase where estudiante.idestudiante = ?',[$id]);

        if(empty($result)){
            return response()->json('aún no estas inscrito a una clase',403);
        }

        return response()->json($result ,200);
   }

    public function AddClass(Request $req){

        $id = $req->id;

        $title = $req->Nombre;
        $descripcion = $req->Descripcion;
        $date_I = $req->Fecha_Inicio;
        $date_E = $req->Fecha_Finalizacion;
        $codN = $req->Nivel;
        $codT = $req->Tipo;

        $result = DB::insert('INSERT into clase (titulo, descripcion, fecha_inicio, fecha_finalizacion, nivel_codnivel, tipo_codtipo, profesor_idprofesor) values (?,?,?,?,?,?,?);',[$title,$descripcion,$date_I,$date_E,$codN,$codT,$id]);

        if($result == false){
            return response()->json("error");
        }

        $result = DB::select('SELECT * from clase where profesor_idprofesor = ? AND titulo = ? AND descripcion = ?',[$id, $title, $descripcion]);

        $result = DB::insert('INSERT into lista (clase_idclase) values (?)',[$result[0]->idclase]);

        return response()->json($result,200);

    }

    public function EnterClass(Request $req, string $idClass){

        $result = DB::select('SELECT * FROM clase WHERE idclase = ?',[$idClass]);

        if(empty($result)){
            return response()->json('La Clase no existe');
        }

        $id = $req->id;

        $rol = $req->rol;

        if($rol === 'profesor'){

            $result2 = DB::select('SELECT idclase,titulo FROM clase WHERE profesor_idprofesor = ? AND idclase = ?',[$id, $idClass]);

            if(empty($result2)){
                return response()->json('Esta clase no es tuya',500);
            }

            $Class = Crypt::encryptString($result2[0]->idclase);

            $cookie = cookie('Class', $Class, 1440);

            return response()->json($result2)->cookie($cookie);
 
        }

        $result3 = DB::select('SELECT clase.titulo, clase.idclase from estudiante_has_lista join estudiante on estudiante_idestudiante = estudiante.idestudiante join lista on estudiante_has_lista.lista_idlista = lista.idlista join clase on lista.clase_idclase = clase.idclase where estudiante.idestudiante = ? AND clase.idclase = ?',[$id, $idClass]);

        if(empty($result3)){
            return response()->json('No estas registrado en esta clase',500);
        }

        $Class = Crypt::encryptString($result3[0]->idclase);

        $cookie = cookie('Class', $Class, 1440);

        return response()->json($result3)->cookie($cookie);
   }

    public function ClassData(Request $req){

        $id = $req->id;

        $rol = $req->rol;
        //$result2 = DB::select('SELECT usuario.Nombre, rol.NombreRol FROM usuario JOIN rol ON usuario.CodRol = rol.CodRol WHERE IdUsuario = ?',[$id]);

        if($rol === 'profesor'){
            $result = DB::select('SELECT usuario.nombre, profesor.rol from profesor join usuario on profesor.usuario_idusuario = usuario.idusuario where profesor.idprofesor = ?',[$id]);
            
            return response()->json($result,200);
        }

        $result = DB::select('SELECT usuario.nombre, estudiante.rol from estudiante join usuario on estudiante.usuario_idusuario = usuario.idusuario where estudiante.idestudiante = ?',[$id]);

        return response()->json($result,200);
    }

    public function enrollclass(Request $req, string $id){
        $idU = $req->id;
        
        $rol = $req->rol;

        if($rol === 'profesor'){
            return response()->json('No puedes unirte a una clase con cuenta de profesor',200);
        }

        $result = DB::select('SELECT * from lista where clase_idclase = ?',[$id]);

        $result = DB::insert('INSERT into estudiante_has_lista (estudiante_idestudiante, lista_idlista) values (?, ?)',[$idU,$result[0]->idlista ]);

        if($result === true){
            return response()->json('Registro completado',200);
        }

        return response()->json('ocurrio un error',500);

    }

    public function studentlist(Request $req){
        $idC = $req->idClass;

        $result = DB::select('SELECT estudiante.idestudiante ,usuario.nombre, usuario.apellido from estudiante_has_lista join estudiante on estudiante_idestudiante = estudiante.idestudiante join lista on estudiante_has_lista.lista_idlista = lista.idlista join clase on lista.clase_idclase = clase.idclase join usuario on estudiante.usuario_idusuario = usuario.idusuario  where clase.idclase = ?',[$idC]);

        return response()->json($result,200);

    }

    public function posts(Request $req){
        $idC = $req->idClass;

        $result = DB::select('SELECT * from publicacion where clase_idclase = ?',[$idC]);

        $result = DB::select('SELECT usuario.nombre, usuario.apellido, usuario.img, publicacion.titulo, publicacion.descripcion, publicacion.imagen ,publicacion.fecha_publicacion, publicacion.idpublicacion from publicacion join profesor on publicacion.profesor_idprofesor = profesor.idprofesor join usuario on profesor.usuario_idusuario = usuario.idusuario where publicacion.clase_idclase = ?',[$idC]);

        return response()->json($result,200);
    }

    public function post(Request $req){
        $id = $req->id;
        $idC = $req->idClass;
        $rol = $req->rol;

        $title = $req->titulo;
        $descripcion = $req->descripcion;

        if($rol === 'estudiante'){
            return response()->json('Los estudiantes no pueden publicar',500);
        }else{
            if($req->hasFile('image')){
                $img = $req->file('image');

                $path = $img->getRealPath();

                $resultIMG = Cloudinary::upload($path,['folder' => 'publicacionesDolhack']);

                $url = $resultIMG->getSecurePath();

                $result = DB::insert('INSERT into publicacion (titulo, descripcion, imagen,clase_idclase, profesor_idprofesor) values(?, ?,?, ?, ?)',[$title,$descripcion, $url,$idC, $id]);

                return response()->json($result,200);
            }

            $result = DB::insert('INSERT into publicacion (titulo, descripcion,clase_idclase, profesor_idprofesor) values(?, ?, ?, ?)',[$title,$descripcion,$idC, $id]);

            return response()->json($result,200);
        }
    }

    public function UpdateClass(Request $req){

        $idClass = $req->idClass;
        $rol = $req->rol;

        if($rol === 'estudiante'){
            return response()->json('Estudiantes no pueden modificar nada',500);
        }

        $title = $req->Nombre;
        $descripcion = $req->Descripcion;
        $codN = $req->Nivel;
        $codT = $req->Tipo;

        $result = DB::update('UPDATE clase SET titulo  = ?, descripcion = ?, nivel_codnivel = ?, tipo_codtipo = ?  WHERE idclase = ?',[$title, $descripcion, $codN, $codT, $idClass]);

        if($result == false){
            return response()->json('No se pudo eliminar la clase',500);
        }

        return response()->json('Clase Actualizada con exito',200);
    }

    public function DeleteClass(Request $req){

        $idClass = $req->idClass;

        $rol = $req->rol;

        if($rol === 'estudiante'){
            return response()->json('Estudiantes no pueden eliminar clases',500);
        }

        DB::delete("DELETE from publicacion where clase_idclase = ?",[$idClass]);

        DB::delete("DELETE from estudiante_has_lista where lista_idlista in (select idlista from lista where clase_idclase = ?)",[$idClass]);

        DB::delete("DELETE from lista where clase_idclase = ?",[$idClass]);

        DB::delete('DELETE FROM respuesta WHERE pregunta_idpregunta IN (SELECT idpregunta FROM pregunta WHERE quiz_idquiz IN (SELECT idquiz FROM quiz WHERE clase_idclase = ?))',[$idClass]);   
        
        DB::delete('DELETE FROM pregunta WHERE quiz_idquiz IN (SELECT idquiz FROM quiz WHERE clase_idclase = ?)',[$idClass]);

        DB::delete('DELETE FROM quiz WHERE clase_idclase = ?;',[$idClass]);

        $result = DB::delete('DELETE FROM clase WHERE idclase = ?',[$idClass]);

        if(!$result){
            return response()->json("No se pudo eliminar la clase",500);
        }

        return response()->json("La clase se elimino correctamente",200);

    }
    public function exit(){
        $cookie = cookie('Class',' ', 1);

        return response()->json('true',200)->cookie($cookie);
    }
    public function Prub(Request $req){

        //Tutotial https://github.com/cloudinary-devs/cloudinary-laravel

        $img = $req->file('image');

        $path = $img->getRealPath();

        $result = Cloudinary::upload($path,['folder' => 'publicacionesDolhack']);

        $result2 = $result->getSecurePath();

        return response()->json($result2);
    }
}
