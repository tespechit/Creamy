<?php

define('DB_USERNAME', 'sanitasventas');
define('DB_PASSWORD', 'Mis.Pupas123');
define('DB_HOST', 'localhost');
define('DB_NAME', 'sanitasventas');
define('DB_PORT', '3306');

define('EMAIL_FROM', 'info@tumejorseguromedico.com');
define('EMAIL_SUBJECT', 'Feliz cumpleaños, ');
define('EMAIL_CONTENT_FILE', 'content.html');
define('EMAIL_NAME_TAG', 'nameofcontact');

$conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME, DB_PORT);
$conn->set_charset('utf8');
if (mysqli_connect_errno()) {
	echo "Failed to connect to MySQL: " . mysqli_connect_error();
}

function sendEmail($toemail, $toname) {
	// set headers and subject
	$headers = "From: Tu Mejor Seguro Médico <" .EMAIL_FROM.">\r\n";
	$headers .= "Reply-To: Tu Mejor Seguro Médico <".EMAIL_FROM.">\r\n";
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-Type: text/html; charset=UTF-8\r\n";
	$parts = explode(' ', $toname);
	if (empty($parts)) {
		$name_first = $toname;
	} else {
		$name_first = array_shift($parts);
		if (empty($name_first)) $name_first = $toname;
	}
	$subject = EMAIL_SUBJECT.$name_first;

	// replace nameofcontact with the actual name
	$message = file_get_contents(EMAIL_CONTENT_FILE);
	$message = str_replace(EMAIL_NAME_TAG, $name_first, $message);

	// send email
	if (mail($toemail, $subject, $message, $headers)) {
		return true;
	} else {
		return false;
	}
}

function insertNotificationOfEmailSent($conn, $userid, $username, $tablename, $success) {
	$action = "http://tumejorseguromedico.com/gestionclientes/editcustomer.php?customerid=".$userid."&customer_type=".$tablename;
	$texto = "$username cumple años hoy. ";
	if ($success) $texto = $texto."Se le ha enviado correctamente una felicitación por correo.";
	else $texto = $texto."Se le intentó mandar una notificación por correo pero se produjo un error. Quizás quieras enviarle un mensaje o llamarle por teléfono.";
	return $conn->query("INSERT INTO notificaciones (usuario_destino, texto, fecha, action) VALUES ($userid, '$texto', now(), '$action')");
}


// Clientes de servicios.
$result = $conn->query("SELECT * FROM clientes_seguros WHERE DATE_FORMAT(fecha_nacimiento,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')");

//fetch the data from the database 
print "Clientes de seguros\n";
while ($obj = $result->fetch_assoc()) {
   print $obj['nombre']." (".$obj['email'].") cumple años hoy ".$obj['fecha_nacimiento']."\n";
	if (!empty($obj["nombre"]) && !empty($obj["email"])) {
		$success = sendEmail($obj["email"], $obj["nombre"]);
		if (!insertNotificationOfEmailSent($conn, $obj["id"], $obj["nombre"], 'clientes_seguros', $success)) {
			print "Hubo un error insertando notificación en la base de datos para cliente ".$obj["id"].": ".$conn->error;
		}
	}
}
die();

// Contactos
$result = $conn->query("SELECT * FROM clientes_contactos WHERE DATE_FORMAT(fecha_nacimiento,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')");

//fetch the data from the database 
print "Contactos potenciales\n";
while ($obj = $result->fetch_assoc()) {
   print $obj['nombre']." (".$obj['email'].") cumple años hoy ".$obj['fecha_nacimiento']."\n";
	if (!empty($obj["nombre"]) && !empty($obj["email"])) {
		$success = sendEmail($obj["email"], $obj["nombre"]);
		insertNotificationOfEmailSent($conn, $obj["id"], $obj["nombre"], 'clientes_contactos', $success);
	}
}

// Clientes de seguro
$result = $conn->query("SELECT * FROM clientes_seguros WHERE DATE_FORMAT(fecha_nacimiento,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')");

//fetch the data from the database 
print "Clientes de seguros\n";
while ($obj = $result->fetch_assoc()) {
   print $obj['nombre']." (".$obj['email'].") cumple años hoy ".$obj['fecha_nacimiento']."\n";
	if (!empty($obj["nombre"]) && !empty($obj["email"])) {
		$success = sendEmail($obj["email"], $obj["nombre"]);
		insertNotificationOfEmailSent($conn, $obj["id"], $obj["nombre"], 'clientes_seguros', $success);
	}

}



// Clientes de servicios.
$result = $conn->query("SELECT * FROM clientes_servicios WHERE DATE_FORMAT(fecha_nacimiento,'%m-%d') = DATE_FORMAT(NOW(),'%m-%d')");

//fetch the data from the database 
print "Clientes de servicioss\n";
while ($obj = $result->fetch_assoc()) {
   print $obj['nombre']." (".$obj['email'].") cumple años hoy ".$obj['fecha_nacimiento']."\n";
	if (!empty($obj["nombre"]) && !empty($obj["email"])) {
		$success = sendEmail($obj["email"], $obj["nombre"]);
		insertNotificationOfEmailSent($conn, $obj["id"], $obj["nombre"], 'clientes_servicios', $success);
	}
}



//close the connection
$result->close();

?>
