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