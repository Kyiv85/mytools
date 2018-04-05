<?php

/**
 * Realiza un envio de email por SMTP
 * @param array $datos Vector con los datos a enviar.
 *    Recibe: destinatario, asunto, adjuntos, contenido
 *            smtp_username, smtp_password
 * @return bool
 */
public function enviarEmail($datos){
  $email = new PHPMailer();
  $email->setFrom('info@epagos.com.ar', "EPagos");
  
  $email->isSMTP();
  $email->Host = 'smtp.mailgun.org';
  $email->Port = 465;
  $email->SMTPAuth = true;
  $email->Username = ($datos["smtp_username"] != "")? $datos["smtp_username"]: 'postmaster@www.epagos.com.ar';
  $email->Password = ($datos["smtp_password"] != "")? $datos["smtp_password"]: "9ea4622f158892aa19bb879dce47c496";
  $email->SMTPSecure = 'ssl';
  
  $email->addAddress($datos["destinatario"]);
  
  $email->isHTML(true);
  $email->Subject = utf8_decode($datos["asunto"]);
  $email->Body    = $datos["contenido"];
  $email->AltBody = strip_tags($datos["contenido"]);
  
  if (is_array($datos["adjuntos"]) && count($datos["adjuntos"]) > 0)
    foreach ($datos["adjuntos"] as $adjunto)
      $email->addAttachment($adjunto["ruta"], $adjunto["nombre"]);
  
  if (!$email->send()) {
    $this->email_error = $email->ErrorInfo;
    return false;
  }
  
  return true;
}

?>