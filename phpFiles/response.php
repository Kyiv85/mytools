<?php
//Página para responder requests AJAX para Operaciones con Proveedores
require_once(dirname(__FILE__)."/../head.php");

$prov = new Proveedores();
$pro = new busquedaProveedores();

//Registar pago a proveedor
if (isset($_POST["formVals"])){
  //Se realiza un primer explode para los datos del formulario
  $data = $prov->getRealValue("&",$_POST["formVals"]);
  $datos = $_POST["datos"];
  
  $resp = $prov->registrarPago($data,$datos);
  if ($resp) {
    $errorMsg = "Se registró el pago al proveedor satisfactoriamente";
    $errTipo  = "alert-success";
  }
   else {
    $errorMsg = $prov->error;
    $errTipo  = "alert-danger";
  }
  echo '<div class="alert '.$errTipo.'">'.$errorMsg.'</div>';

}
//Buscar detalles de un pago a proveedor dado el id
else if (isset($_POST["idProveedorPago"])){
  //Buscar detalles del pago
  $data = $prov->obtenerDetallesPago($_POST["idProveedorPago"]);
  
  if (count($data) == 0) {
    $data["mensaje"] = '<div class="alert alert-danger">No se consiguieron detalles de pago</div>';
  }
  echo json_encode($data);
  exit();
}
//Crear nuevo proveedor
else if (isset($_POST["createNewProveedor"])){
  $data = $_POST;
  $resp = $prov->createProveedor($data);
  
  if ($resp == 0){
    $data["mensaje"] = '<div class="alert alert-danger">Hubo un error al resgistrar el pago: '.$prov->error.'</div>';
  }
  else{
    $data["nuevoID"] = $resp;
  }
  echo json_encode($data);
  exit();
}
//Eliminar compra de forma lógica
else if (isset($_POST["accion"]) && ($_POST["accion"] == "EliminarCompra")){
  $comp = new Compras();
  $resp = $comp->eliminar($_POST["comID"]);
  
  if ($resp) {
    $errorMsg = "Se eliminó la compra satisfactoriamente";
    $errTipo  = "alert-success";
  }
  else {
    $errorMsg = $comp->error;
    $errTipo  = "alert-danger";
  }
  echo '<div class="alert '.$errTipo.'">'.$errorMsg.'</div>';
}
//Mostrar modal con pagos relacionados para una compra
else if (isset($_POST["accion"]) && ($_POST["accion"] == "verPagosRelacionados")){
  $comp = new Gastos();
  $resp["gastos"] = $comp->getGastosCompraRelacion($_POST['comID']);
  $resp["notasCredito"] = $comp->getNotasCreditoRelacionadas($_POST["comID"]);
  
  //Verificar si existen certificados de retención para la compra
  $cert = CertificadosRetencion::getCertByCompra($_POST["comID"]);
  if (count($cert)>0){
    $resp["certificado"] = $cert;
  }
  echo json_encode($resp);
  exit();
}
//Buscar gastos disponibles para asociar con compra
else if (isset($_POST["accion"]) && $_POST["accion"] == "buscarAsociarGasto"){
  $gas = new Gastos();
  //Verificar si la compra ya fue acreditada
  $compra = $gas->loadCompraFromID($_POST["comID"]);
  if($compra["comEstado"] == 'A'){
    $data["mensaje"] = 'La compra ya se encuentra acreditada';
  }
  else {
    $gas->setIdGastoModal($_POST["idGasto"]);
    $gas->setNotaCreditoModal($_POST["notaCredito"]);
    $gas->setCcoIDModal($_POST["ccoID"]);
    $gas->setGasMontoModal($_POST["gasMonto"]);
    $gas->setGasFechaDesde($_POST["gasFechaDesde"]);
    $gas->setGasFechaHasta($_POST["gasFechaHasta"]);
    $data = $gas->getGastosSinRelacion();
  
    if ($gas->error != ""){
      $data["mensaje"] = '<div class="alert alert-danger">Hubo un error al realizar la búsqueda: '.$gas->error.'</div>';
    }
  }
  echo json_encode($data);
  exit();
}
//Asociar compra con gastos
else if (isset($_POST["accion"]) && $_POST["accion"] == "asociarCompraGasto"){
  
  $gas = new Gastos();
  if($_POST["isNC"] == "NO"){
    $resp = $gas->asociarCompraGasto($_POST["comID"],$_POST["idGasto"]);
  }
  else{
    $resp = $gas->asociarCompraGastoNotaCredito($_POST["comID"],$_POST["idGasto"]);
  }
  
  if ($resp){
    //Definir nuevo objeto para traer los datos de la compra
    $data["registro"] = $gas->loadCompraFromID($_POST["comID"]);
    $data["mensaje"] = $gas->mensaje;
  }
  else {
    $data["errorMensaje"] = '<div class="alert alert-danger">Hubo un error al intentar asociar la compra: '.$gas->error.'</div>';
  }
  
  echo json_encode($data);
  exit();
}
//Eliminar compra relacionada de Gastos
else if (isset($_POST["accion"]) && ($_POST["accion"] == "EliminarCompraRelacionada")){
  $gas = new Gastos();
  $resp = $gas->eliminarCompraRelacionada($_POST["comID"],$_POST["idGasto"]);
  
  if ($resp){
    //Definir nuevo objeto para traer los datos de la compra
    $data["registro"] = $gas->loadCompraFromID($_POST["comID"]);
    $data["mensaje"] = "Se eliminó la compra asociada satisfactoriamente";
  }
  else {
    $data["errorMensaje"] = '<div class="alert alert-danger">Hubo un error al intentar eliminar la compra asociada: '.$gas->error.'</div>';
  }
  
  echo json_encode($data);
  exit();

}
else if (isset($_POST["accion"]) && ($_POST["accion"] == "validarExistenciaCuit")){

  $pro->setCuit($_POST['cuit']);
  $pro->setProID($_POST['proID']);
  $aProovedores = $pro->search();

  if(!empty($aProovedores)){
    $data["errorMensaje"] = 'El cuit ya se encuentra registrado.';
  }else{

    $data["errorMensaje"] = '1';
  }

  echo json_encode($data);
  exit();
}
else if (isset($_POST["accion"]) && $_POST["accion"] == "relacionarRetencion"){
  $comp = new Compras();
  $data = $_POST;
  
  //Buscar la información del gasto
  $gasto = $comp->loadGastoFromID($data["idGasto"]);
  
  //Buscar la información de la compra
  $compra = $comp->loadCompraFromID($data["comID"]);
  
  //Verificar que el resto de la compra sea igual al monto del gasto más el monto de la retención
  $total = $gasto["gasMonto"] + $data["cerMonto"];
  if($compra["comResto"] == $total) {
    
    $retencion = new CertificadosRetencion();
    $resp = $retencion->saveRetencion($data);
    if ($resp) {
      $gas = new Gastos();
      $res = $gas->asociarCompraGasto($_POST["comID"],$_POST["idGasto"],$data["cerMonto"]);
      if ($res){
        //Definir nuevo objeto para traer los datos de la compra
        $ret["registro"] = $gas->loadCompraFromID($_POST["comID"]);
        $errorMsg = $gas->mensaje;
        $errTipo = "alert-success";
      }
      else {
        $errorMsg = "Hubo un error al asociar la compra: ".$gas->error;
        $errTipo = "alert-danger";
      }
    } else {
      $errorMsg = "Hubo un error al intentar guardar la retención ".$gas->error;
      $errTipo = "alert-danger";
    }
  }
  else{
    $errorMsg = "El monto de la retención y el monto del gasto no coinciden con el monto restante de la compra";
    $errTipo = "alert-danger";
  }
  
  $ret["mensaje"] = '<div class="alert '.$errTipo.'">'.$errorMsg.'</div>';
  echo json_encode($ret);
  exit();
}
else if (isset($_POST["accion"]) && ($_POST["accion"] == "compraInformacion")){
  
  //Capturar los identificadores de cada compra
  $comID = json_decode($_POST["comID"]);
  $comp = new Compras();
  
  //Verificar la cantidad de elementos seleccionados para el banco Galicia
  $selGalicia = $comp->verifySelectedItems($comID);
  if($selGalicia){
    $data["mensaje"] = "No se puede seleccionar más de una compra en la que el proveedor tenga cuenta del banco Galicia";
  }
  else {
    $acumMonto=0;
    $cantCompras=0;
    $provsID = [];
    $idToProcess = [];
    //Recorrer cada ID de compra
    foreach ($comID as $id){
      //Buscar la información de la compra
      $compra = $comp->loadCompraFromID($id);
      //Acumular resto
      $comResto=floatval($compra["comResto"]);
    
      //Si el resto es mayor a cero la compra no está acreditada y se cuenta el valor
      if($comResto>0){
        $cantCompras++;
        $acumMonto=$acumMonto+$comResto;
        array_push($idToProcess, $id);
        array_push($provsID,$compra["proID"]);
      }
    }
  
    //Agrupagar los proveedores para tener la cantidad real
    $proID = array_unique($provsID);
  
    //Devolver como estructura
    $data["cantCompras"] = $cantCompras;
    $data["acumMonto"] = $acumMonto;
    $data["ids"] = $idToProcess;
    $data["provsIDS"] = json_encode($proID);
    $data["cantProvs"] = count($proID);
  }
  echo json_encode($data);
  exit();
}
else if (isset($_POST["accion"]) && $_POST["accion"] == "PagoProveedorCompras"){
  
  $data = $_POST;
  
  //Setear valores
  $prov->setIdCuenta($data["ccoID"]);
  $prov->setPpaCantidad($data["ppaCantidad"]);
  $prov->setPpaMonto($data["ppaMonto"]);
  $prov->setPpaFecha(date("Y-m-d H:i:s",strtotime($data["ppaFecha"])));
  
  //Convertir los identificadores de compra y proveedor a array
  //$comprasIDS = explode('"',$data["comID"]);
  //$comID = explode(',',$comprasIDS[1]);
  $str=json_decode($data["proID"]);
  $proID=json_decode($str,true);
  
  //Registrar el pago en base a las compras elegidas
  $res = $prov->registrarPagoFromCompras($proID,$data["comID"]);
  
  if ($res){
    $errorMsg = "Se registro el pago de la compra satisfactoriamente con el número ".$prov->ppaID;
    $errTipo = "alert-success";
  }
  else {
    $errorMsg = "Hubo un error al registrar el pago de la compra: ".$prov->error;
    $errTipo = "alert-danger";
  }
  
  $ret["mensaje"] = '<div class="alert '.$errTipo.'">'.$errorMsg.'</div>';
  echo json_encode($ret);
  exit();
}
//Eliminar Nota de crédito relacionada de Gastos
else if (isset($_POST["accion"]) && ($_POST["accion"] == "EliminarNotaCreditoRelacionada")){
  $gas = new Gastos();
  $resp = $gas->eliminarNotaCreditoRelacionada($_POST["comID"],$_POST["comID_NC"]);
  
  if ($resp){
    //Definir nuevo objeto para traer los datos de la compra
    $data["registro"] = $gas->loadCompraFromID($_POST["comID"]);
    $data["mensaje"] = "Se eliminó la relación entre la compra y la nota de crédito satisfactoriamente";
  }
  else {
    $data["errorMensaje"] = '<div class="alert alert-danger">Hubo un error al intentar eliminar la relación entre la compra y la nota de crédito: '.$gas->error.'</div>';
  }
  
  echo json_encode($data);
  exit();
  
}
//Eliminar Archivo de pago a Proveedor
else if (isset($_POST["accion"]) && ($_POST["accion"] == "eliminarArchivo")){
  $prov = new Proveedores();
  $resp = $prov->eliminarArchivoProveedorPago($_POST["ppaID"]);
  
  if ($resp){
    //Definir nuevo objeto para traer los datos de la compra
    $data["registro"] = $gas->loadCompraFromID($_POST["comID"]);
    $data["mensaje"] = "Se eliminó la relación entre la compra y la nota de crédito satisfactoriamente";
  }
  else {
    $data["errorMensaje"] = '<div class="alert alert-danger">Hubo un error al intentar eliminar el archivo de pago a proveedor: '.$prov->error.'</div>';
  }
  
  echo json_encode($data);
  exit();
  
}
else {
  header('Location:buscar.php');
}