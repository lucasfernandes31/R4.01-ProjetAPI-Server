<?php
case 'POST':
    $postedData = file_get_contents('php://input');//pour récupérer le username et password
    $data = json_decode($postedData, true);
    $context = stream_context_create([
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/json',
            'content'=>json_encode($data),//pour transformer le tableau data en string JSON
            'ignore_errors' => true
        ]
    ]);
    $response = file_get_contents("http://localhost/projet-api/R4.01-ProjetAPI-Auth/authapi.php", false, $context);
    $responseTab = json_decode($response, true);
    deliver_response($responseTab['status_code'], $responseTab['status_message'], $responseTab['data']);//renvoie le code, le message du status et le token
    break;
?>