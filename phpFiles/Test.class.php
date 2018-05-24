<?php

class Backend {

	private $sysErrors = array();
	protected $db;
	protected $returnError;
	
	protected static $simbolo_moneda = "$";

	public function __construct() {
		try {
			$this->db = new db(DB_DSN, DB_USER, DB_PASS);
		} catch (PDOException $e) {
			$this->logSysError($e->getMessage());
			$this->errorPage();
		}
	}
	
	public function getDb(){
		return $this->db;
	}

	public function getError() {
		return $this->returnError;
	}
	
	public function logSysError($error) {
		array_push($this->sysErrors, $error);
		error_log($error);
	}
	
	public function getSysErrors() {
		$ret = "";
		foreach($this->sysErrors as $line)
			$ret .= $line."\n";
		
		return $ret;
	}
	
	public function errorPage($msg="") {
		$_SESSION["errores"] = '<h1>'.$msg.'</h1><p><pre>'.$this->getSysErrors().'</pre></p>';
		header('Location: error.php');
	}
	
	public static function formatoMoneda($monto, $simbolo=false){
		return (($simbolo)? self::$simbolo_moneda." ": "") . number_format($monto, 2, ",", ".");
	}
	
	// Funcion temporal para debuguear arrays...
	public function printArray($a) {
		$ret = '<ul>';
		if (is_array($a)) {
			foreach ($a as $k => $v) {
				if (is_array($v))
					$ret .= '<li>'.$k.' -> '.$this->printArray($v).'</li>';
				else
					$ret .= '<li>'.$k.' -> '.$v.'</li>';
			}
		}
		return $ret .= '</ul>';
	}

	public function pagina_valida($nro_pagina, $pagina){
	  $paginado_limite = 5;

    if ($pagina - $paginado_limite <= 0){
      // no se llegó al limite, controla solo hacia adelante

      return $nro_pagina <= $paginado_limite * 2;
    }

    return ($nro_pagina < ($pagina + $paginado_limite)
            &&
            $nro_pagina >= ($pagina - $paginado_limite));
  }

  /**
   * Envia un email a través del SMTP
   * @param string $email La dirección de email
   * @param string $asunto El asunto
   * @param string $contenido El contenido
   * @param array $adicional Vector adicional del envío
   * @return bool
   */
  public function enviarEmail($email, $asunto, $contenido, $adicional=[]){
    if (!isset($_SERVER["HTTP_HOST"]) || in_array($_SERVER["HTTP_HOST"], ["epagos", "epagos:8082"])){
      $encoding = "utf-8";
      $subject_preferences = array(
        "input-charset" => $encoding,
        "output-charset" => $encoding,
        "line-length" => 76,
        "line-break-chars" => "\r\n"
      );

      $header = "Content-type: text/html; charset=".$encoding." \r\n";
      $header .= "From: ".(($adicional["From"])? $adicional["From"]: "EPagos <noreply@".SITE_DOMAIN.">")." \r\n";
      if ($adicional["reply-to"]) {
        $header .= "replay-to: " . $adicional["reply-to"] . " \r\n";
      }
      $header .= "MIME-Version: 1.0 \r\n";
      $header .= "Content-Transfer-Encoding: 8bit \r\n";
      $header .= "Date: ".date("r (T)")." \r\n";
      $header .= iconv_mime_encode("Subject", $asunto, $subject_preferences);
      return mail($email, $asunto, $contenido, $header);
    }

    require_once(LIB_DIR."PHPMailer/PHPMailerAutoload.php");

    $mail = new PHPMailer;

    $mail->SMTPDebug = 3;
    $mail->isSMTP();
    $mail->SMTPAuth = true;
    $mail->Username = SMTP_USER;
    $mail->Password = SMTP_PASSWORD;

    $mail->CharSet = 'UTF-8';

    $mail->Port = SMTP_PORT;

    if ($adicional["reply-to"]){
      $mail->addReplyTo($adicional["reply-to"]);
    }

    if ($adicional["From"]){
      $mail->From = $adicional["From"];
      $mail->FromName = $adicional["From"];

    } else {
      $mail->From = "noreply@".SITE_DOMAIN;
      $mail->FromName = "EPagos";
    }

    $mail->addAddress($email);

    $mail->isHTML(true);

    $mail->Subject = $asunto;
    $mail->Body = $contenido;
    $mail->AltBody = strip_tags($contenido);
    $mail->SMTPSecure = "tls";
    $mail->Host = SMTP_HOST;
    $mail->SMTPDebug = 0;

    $ret = $mail->send();
    if (!$ret){
      error_log("EnviarEmail: Fallo el envio del email ".$mail->ErrorInfo);
    }

    return $ret;
  }

}

?>