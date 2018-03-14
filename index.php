<?php 
require_once 'vendor/autoload.php';

use \Slim\App;
$db = new mysqli('localhost','root','root','angularUdemy');

$app = new App();

//LISTAR TODOS LOS PRODUCTOS 

$app->get('/productos', function() use ($app,$db){
  $sql="SELECT * FROM productos ORDER BY id DESC";
  $query= $db->query($sql);

  $productos=array();
  while($producto = $query->fetch_assoc()){
    $productos[]=$producto;
  }
  $result =array(
    'status' => 'success',
    'code' => '200',
    'data' => $productos
  );

  echo json_encode($result);


});

//DEVOLVER UN SOLO PRODUCTO

$app->get('/producto/{id}', function($request, $response, $arguments) use ($app,$db){
  $sql="SELECT * FROM productos WHERE id='".$arguments['id']."';";
  $query= $db->query($sql);

  $result = array(
      'status' => 'error',
      'code' => '404',
      'message' => 'Producto NO disponible'
   );

  if($query->num_rows==1){
    $producto=$query->fetch_assoc();
    $result =array(
      'status' => 'success',
      'code' => '200',
      'data' => $producto
    );
  }

  echo json_encode($result);

});

//ELIMINAR UN PRODUCTO

$app->get('/delete-producto/[{id}]', function($request, $response, $arguments) use ($app,$db){
  
  $sql="DELETE FROM productos WHERE id='".$arguments['id']."';";
  $query= $db->query($sql);

  echo "total filas: ".$query->affected_rows;

  if($query->affected_rows>0){
    $producto=$query->fetch_assoc();
    $result =array(
      'status' => 'success',
      'code' => '200',
      'message' => 'El producto se ha eliminado correctamente.'
    );
  }else{
      $result = array(
        'status' => 'error',
        'code' => '404',
        'message' => 'Producto NO se ha eliminado.'
     );
  }

  echo json_encode($result);

});

//ACTUALIZAR UN PRODUCTO

$app->post('/update-producto/[{id}]', function($request, $response, $arguments) use ($app,$db){

    $json=$request->getParam('json');
    $data=json_decode($json,true); //lo convertimos a un objeto
    //var_dump($data);
    if(isset($arguments['id'])){
      $sql="UPDATE productos SET name = '".$data['name']."',
       description ='".$data['description']."',
       price ='".$data['price']."'";

       if(isset($data['image'])){
        $sql.=",image ='".$data['image']."'";
       }

       $sql.="WHERE id='".$arguments['id']."';";

       $query= $db->query($sql);

         if($query){
            $result =array(
              'status' => 'success',
              'code' => '200',
              'message' => 'El producto se ha actualizado correctamente.'
            );
          }else{
              $result = array(
                'status' => 'error',
                'code' => '404',
                'message' => 'Producto NO se ha actualizado.'
             );
          }
    }

     echo json_encode($result);

});

//SUBIR UNA IMAGEN A UN PRODUCTO
$app->post('/upload-file',function($request, $response, $arguments) use($db, $app){

  $result = array(
    'status' => 'error',
    'code' => '404',
    'message' => 'El fichero no ha podido subirse.'
  );

  if(isset($_FILES['uploads'])){

    //echo 'dentro';

    $piramideUploader= new PiramideUploader();

    $upload= $piramideUploader->upload('image','uploads','uploads',array('image/jpg','image/png','image/gif'));
    $file=$piramideUploader->getInfoFile();
    $file_name=$file['complete_name'];

    if(isset($upload) && $upload['uploaded']==false){
      $result = array(
        'status' => 'error',
        'code' => '404',
        'message' => 'El fichero no ha podido subirse.'
      );
    }else{
      $result =array(
          'status' => 'success',
          'code' => '200',
          'message' => 'El fichero se ha subido correctamente.',
          'filename'=> $file_name
        );
    }
    

  }

  echo json_encode($result);

});

//GUARDAR PRODUCTOS EN LA BASE DE DATOS
$app->post('/productos',function($request, $response, $arguments) use($app,$db){

  // $params = $request->getParams();
  //   print_r($params);

  $json=$request->getParam('json');
  // var_dump($json);

  $data=json_decode($json,true);
   var_dump($data);
  if(!isset($data['name'])){
    $data['name']=null;
  }
  if(!isset($data['description'])){
    $data['description']=null;
  }
  if(!isset($data['price'])){
    $data['price']=null;
  }
  if(!isset($data['image'])){
    $data['image']=null;
  }
  $query="INSERT INTO productos (name, description, price, image) 
  VALUES('{$data['name']}','{$data['description']}','{$data['price']}','{$data['image']}');";

  $insert= $db->query($query);

  $result = array(
      'status' => 'error',
      'code' => '404',
      'message' => 'Producto NO se creado correctamente'
   );
  if($insert){
    $result = array(
      'status' => 'success',
      'code' => '200',
      'message' => 'Producto creado correctamente'
   );
  }
  //var_dump($result);

  echo json_encode($result);

});

$app->run();


?>