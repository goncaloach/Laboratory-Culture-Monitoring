<?php
	$url="localhost";
	$database="mylab"; // Alterar nome da BD se necessario
    $conn = mysqli_connect($url,$_POST['username'],$_POST['password'],$database);
	#$conn = mysqli_connect($url,'root','abc',$database);
	// Alterar nome da tabela Medicao, nome dos campos Hora e Leitura, e a sigla do tipo de sensor de temperatura ("TEM") se necessario
	// AND DataHoraObjectId >= now() - interval 3 minute ORDER BY Hora ASC"
	$sql = "SELECT IdMedicao, DataHoraObjectId, Leitura, Sensor from Medicao where (Sensor='T1' or Sensor='T2') AND Excluido = 0 AND DataHoraObjectId >= now() - interval 3 minute ORDER BY DataHoraObjectId ASC";
	$result = mysqli_query($conn, $sql);
	$response["readings"] = array();
	if ($result){
		
		if (mysqli_num_rows($result)>0){
			//error_log("CHEGOU ATE AQUI");
			while($r=mysqli_fetch_assoc($result)){
				$ad = array();
				
				// Alterar nome dos campos se necessario
				
				$ad["Sensor"] = $r['Sensor'];
				$ad["IdMedicao"] = $r['IdMedicao'];
				if($r['Sensor'] == "T1")
				{
					$ad["DataHoraObjectId"] = $r['DataHoraObjectId'];
					$ad["Leitura"] = $r['Leitura'];
				}else if($r['Sensor'] == "T2")
				{
					$ad["DataHoraObjectId2"] = $r['DataHoraObjectId'];
					$ad["Leitura2"] = $r['Leitura'];
				}
				// error_log($ad["DataHora"]);
				// error_log($ad["Leitura"]);
				array_push($response["readings"], $ad);
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