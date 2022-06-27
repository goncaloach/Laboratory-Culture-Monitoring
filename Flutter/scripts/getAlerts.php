<?php
	ini_set("log_errors", 1); 
	$url="localhost";
	$database="mylab"; // Alterar nome da BD se necessario
    $conn = mysqli_connect($url,"root","",$database);	
	//$conn = mysqli_connect($url,$_POST['username'],$_POST['password'],$database);
	// Alterar nome da tabela Alerta e nome do campo Hora se necessario
	$sql = "SELECT Alerta.IdAlerta, Alerta.idMedicao, Alerta.Zona, Alerta.Sensor, Alerta.Hora, Alerta.Leitura, Alerta.NivelAlerta, Alerta.NomeCultura, Alerta.Mensagem, Alerta.IdUtilizador, Alerta.IdCultura, Alerta.HoraEscrita 
	from Alerta, Utilizador where Utilizador.IdUtilizador = Alerta.IdUtilizador AND Utilizador.Username = '". $_POST['username'] ."' AND DATE(Alerta.Hora) = DATE('".$_POST['date']."')";	
	
	$sql .= $_POST['fromDateTime'] != '' ? " and Alerta.Hora > STR_TO_DATE('".$_POST['fromDateTime']."','%Y-%m-%d %H:%i:%s');":";";
	
	error_log($_POST['username']);
	error_log($_POST['date']);
	error_log($sql);
	$result = mysqli_query($conn, $sql);
	$response["alerts"] = array();
	if ($result){
		if (mysqli_num_rows($result)>0){
			//error_log("aqui");
			while($r=mysqli_fetch_assoc($result)){
				$ad = array();
				// Alterar nome dos campos da tabela se necessario
				$ad["Zona"] = $r['Zona'];
				$ad["Sensor"] = $r['Sensor'];
				$ad["Hora"] = $r['Hora'];
				$ad["Leitura"] = $r['Leitura'];
				$ad["NivelAlerta"] = $r['NivelAlerta']; 
				$ad["NomeCultura"] = $r['NomeCultura'];
				$ad["Mensagem"] = $r['Mensagem'];
				array_push($response["alerts"], $ad);
			}
		}
	}
	mysqli_close ($conn);
	
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *');
	// tell browser that its a json data
	echo json_encode($response);
	//converting array to JSON string
?>