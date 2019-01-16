//Aceptar sólo números Ctrl+A,Ctrl+C,Ctrl+V,Ctrl+X Command+A y otras teclas funcionales
$("#cotTarjeta").keydown(function (e) {
  // Allow: backspace, delete, tab, escape, enter
  if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110]) !== -1 ||
    // Allow: Ctrl+A,Ctrl+C,Ctrl+V,Ctrl+X Command+A
    ((e.keyCode == 65 || e.keyCode == 86 || e.keyCode == 67 || e.keyCode == 88) && (e.ctrlKey === true || e.metaKey === true)) ||
    // Allow: home, end, left, right, down, up
    (e.keyCode >= 35 && e.keyCode <= 40)) {
    // let it happen, don't do anything
    return;
  }
  // Ensure that it is a number and stop the keypress
  if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
    e.preventDefault();
  }
});

//Limitar el upload de archivos a solo pdf e imagénes y que su peso sea menor a 5MB
$("#cotComprobante").change(function(){
  var file = $(this).val();
  var fileSize = $(this)[0].files[0].size;
  var size = parseInt(fileSize / 1024);
  var ext = file.substring(file.lastIndexOf("."));
  if((ext != ".pdf") && (ext != ".jpg") && (ext != ".jpeg") && (ext != ".png") && (ext != ".gif")){
    alert("La extensión del archivo " + ext + " no es válida");
    document.getElementById("colBoleto").value = "";
  }
  else if (size > 5120) {
    alert("El archivo debe pesar menos de 5MB");
    document.getElementById("colBoleto").value = "";
  }
});


//Comparación de fechas - ESTE APLICA PARA QUE FECHA DADA NO SEA MAYOR A HOY
function compareDates(){
  //Fecha de hoy
  var date = new Date();
  var hoy = date.getTime();

  //Tomar el string de la fecha y pasarlo separado FUNCIONA PARA TODOS LOS BROWSERS
  var xvals = comFecha.split('-');
  //Pasar año - mes - día
  var date2 = new Date(
    parseInt(xvals[2]),
    parseInt(xvals[1]) - 1,
    parseInt(xvals[0])
  );
  var fecha = date2.getTime();
}

//Prueba de procesamiento por ajax
//Búsqueda de gastos para asociar
$('#btnBuscarAsociarGasto').click(function(e){

  //Ir al principio de la página
  $("div.modal-body").animate({ scrollTop: 0 }, "slow");
  $("body").scrollTop(0);

  //Capturar valores existentes en el formulario
  comID = $('#comID').val();
  proRazonSocial = $('#proRazonSocial').val();
  comImporte = $('#comImporte').val();
  comFecha = $('#comFecha').val();
  idGasto = $('#idGasto').val();
  notaCredito = $('#notaCredito').val();
  ccoID = $('#ccoID').val();
  gasMonto = $('#gasMonto').val();
  gasFechaDesde = $('#gasFechaDesde').val();
  gasFechaHasta = $('#gasFechaHasta').val();

  //Validaciones
  //Aunque sea algún campo con información
  if ((idGasto == "" || idGasto == null) && (notaCredito == "" || notaCredito == null) && (ccoID == "" || ccoID == null) && (gasMonto == "" || gasMonto == null) && (gasFechaDesde == "" || gasFechaDesde == null) && (gasFechaHasta == "" || gasFechaHasta == null)){
    $('#mensajesAsociar').html("<div class='alert alert-danger'>\n" +
      "  Al menos uno de los campos debe contener información.\n" +
      "</div>");
    window.setTimeout(function() {
      $("#mensajesAsociar div").fadeTo(500, 0).slideUp(500, function(){
        $(this).remove();
      });
    }, 60000);
  }
  else {
    $.ajax({
      url: 'response.php',
      type: "POST",
      data: {
        accion: "buscarAsociarGasto",
        comID: comID,
        idGasto: idGasto,
        notaCredito: notaCredito,
        ccoID: ccoID,
        gasMonto: gasMonto,
        gasFechaDesde: gasFechaDesde,
        gasFechaHasta: gasFechaHasta
      },
      success: function (data) {
        //Parsear JSON
        var obj = jQuery.parseJSON(data);

        //Si hay mensaje de error mostrarlo
        if (typeof obj["mensaje"] != "undefined" || obj["mensaje"] != null) {
          $('#mensajesAsociar').html("<div class='alert alert-danger'>"+obj['mensaje']+"</div>");
          window.setTimeout(function() {
            $("#mensajesAsociar div").fadeTo(500, 0).slideUp(500, function(){
              $(this).remove();
            });
          }, 60000);
        }
        else {

          //Ajustar overflow de la ventana del modal
          $('div.modal-dialog').css('overflow-y','initial !important');
          $('div.modal-body').css('height', '400px')
            .css('overflow-y', 'auto');

          //Ocultar botón de buscar gastos y mostrar el de volver
          $('#btnBuscarAsociarGasto').css('display','none');
          $('#btnBackAsociarGasto').css('display','');

          //Mostrar section con la tabla
          $('#secBuscarGastos').fadeOut('slow');
          $('#secTableGastos').fadeIn(2000);

          //Agregar resultados al array de objetos
          datos = obj;

          //Eliminar los mensajes de error si existen
          $("#mensajesAsociar div").remove();

          //Eliminar información de la tabla
          $('#tableGastos tbody tr').remove();

          if(datos.length == 0) {
            //Si no hay elementos se muestra mensaje indicando que no se consiguieron datos
            $('#tableGastos tbody').html("<tr><td class='text-center' colspan=5>No se consiguieron gastos que coincidan con los criterios de búsqueda</td></tr>");
          }
          else{
            //Mostrar los datos de la compra
            $('#datosCompra').html("<h4>Datos de la compra actual:</h4>" +
              "              - <b>Proveedor:</b> "+proRazonSocial+"<br>\n" +
              "              - <b>Fecha:</b> "+comFecha+"<br>\n" +
              "              - <b>Importe:</b> "+parseFloat(comImporte).toFixed(2)+"<br>");

            //Se actualiza la tabla con los elementos en el objeto
            for (var i=0;i<datos.length;i++) {
              //Formtear fecha
              var fecha = new Date(datos[i]["gasFecha"]);
              fecha.setDate(fecha.getDate());

              //Poner ceros al día y mes de ser necesario
              if(fecha.getDate()<10){
                dia = "0"+fecha.getDate();
              }
              else {
                dia = fecha.getDate();
              }
              if((fecha.getMonth()+1)<10){
                mes = "0"+(fecha.getMonth()+1);
              }
              else {
                mes = fecha.getMonth()+1;
              }

              //Variable de control NC - No mostrar botón de asociar retención para las notas de crédito
              if (datos[i]["notaCredito"] == "NO"){
                isNC = 'NO';
                retencion = "           <a href='#' onClick='cargarAsociarRetencion("+comID+","+datos[i]["idGasto"]+")' class='table-link warning' title='Relacionar con retención'>\n";
              }
              else {
                isNC = 'SI';
                retencion = "           <a href='#' class='table-link' style='pointer-events:none;color:gray'>\n";
              }

              $('#tableGastos tbody').append("<tr>\n" +
                "                    <td class='text-center'>"+datos[i]["idGasto"]+"</td>\n" +
                "                    <td class='text-center'>"+datos[i]["banDescrip"]+"</td>\n" +
                "                    <td class='text-center'>"+parseFloat(datos[i]["gasMonto"]).toFixed(2)+"</td>\n" +
                "                    <td class='text-center'>"+dia+"-"+mes+"-"+fecha.getFullYear()+"</td>\n" +
                // Acciones de gastos
                "                    <td style='width:100px;'>\n" +
                "                     <a href='#' onClick='asociarCompraGasto("+comID+","+datos[i]["idGasto"]+",\""+isNC+"\")' class='table-link success' title='Relacionar compra con gasto'>\n" +
                "                       <span class='fa-stack'>\n" +
                "                         <i class='fa fa-square fa-stack-2x'></i>\n" +
                "                         <i class='fa fa-check-circle fa-stack-1x fa-inverse'></i>\n" +
                "                       </span>\n" +
                "                     </a>\n" +
                retencion +
                "                       <span class='fa-stack'>\n" +
                "                         <i class='fa fa-square fa-stack-2x'></i>\n" +
                "                         <i class='fa fa-check-circle fa-stack-1x fa-inverse'></i>\n" +
                "                       </span>\n" +
                "                     </a>\n" +
                "                    </td>\n" +
                "                   </tr>");
            }
          }
        }
      },
      error: function(){
        $('#mensajesAsociar').html("<div class='alert alert-danger'>\n" +
          "  Hubo un error al intentar realizar la búsqueda.\n" +
          "</div>");
        window.setTimeout(function() {
          $("#mensajesAsociar div").fadeTo(500, 0).slideUp(500, function(){
            $(this).remove();
          });
        }, 60000);
      }
    });
  }
  e.preventDefault();
  return false;
});


//Elimina un contacto de la tabla
function deleteContactoRendicion(btn) {
  var row = btn.parentNode.parentNode;
  row.parentNode.removeChild(row);
}


//Agregar los contactos de rendición para organismo
function agregarContactoRendicion(){
  //Capturar valores
  var recNombre = document.getElementById("recNombre").value;
  var recEmail = document.getElementById("recEmail").value;
  var recTipo = document.querySelector("select#recTipo").value;console.log("rectipo "+recTipo);
  var lastRow = document.getElementById("lastRow").value;
  //Varibale para verificar correo correcto
  var rege = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

  //Validaciones
  if(recNombre == ""){
    alert("Indique el nombre");
    return false;
  }
  if(recEmail == ""){
    alert("Indique el correo");
    return false;
  }
  if(recTipo == ""){
    alert("Indique el tipo de contacto");
    return false;
  }
  if (!(rege.test(recEmail))){
    alert("Indique un correo válido");
    return false;
  }
  //Obtener información de la tabla
  var table = document.querySelector("#tableContactosAgregados tbody");
  var cantRows = lastRow+1;
  document.getElementById("lastRow").value = cantRows;
  //Tipo de contacto
  switch(recTipo) {
    case "A":
      var tipo = '<span class="label label-success">Ambos</span>';
      break;
    case "R":
      var tipo = '<span class="label label-default">Rendiciones</span>';
      break;
    case "L":
      var tipo = '<span class="label label-warning">Liquidaciones</span>';
      break;
  }
  //Imprimir información en la tabla
  var newRow = '<td>'+recNombre+'</td>' +
    '<td>'+recEmail+'</td>' +
    '<td>'+tipo+'</td>' +
    '<td><a href="javascript:void(0)" onClick="deleteContactoRendicion(this)" class="table-link danger" title="Eliminar cuenta">' +
    '   <span class="fa-stack">' +
    '     <i class="fa fa-square fa-stack-2x"></i>' +
    '     <i class="fa fa-trash-o fa-stack-1x fa-inverse"></i>' +
    '   </span>' +
    ' </a></td>';
  var tr = document.createElement("tr");
  tr.innerHTML = newRow;
  table.appendChild(tr);
  tr.setAttribute("id", cantRows+1);
}