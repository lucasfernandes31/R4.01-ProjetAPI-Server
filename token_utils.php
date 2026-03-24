<?php
require_once __DIR__ . '/utils.php';

//////////
// Fonction appellant le serveur d'authentification pour
// vérifier la validité du token JWT
function verifier_token(string $role_requis = null): array {
    $token = $_SERVER['HTTP_AUTHORIZATION'] ?? null;

    if (!$token) {
        deliver_response(401, "Token manquant");
        die();
    }

    $context = stream_context_create([
        'http' => [
            'method' => 'GET',
            'header' => "Authorization: " . $token,
            'ignore_errors' => true
        ]
    ]);

    // Requête au serveur auth
    $response = file_get_contents("https://r401-jwt.alwaysdata.net/authapi", false, $context);
    $responseTab = json_decode($response, true);

    // Si réponse pas 200 alors le token est pas valide
    if (!$responseTab || $responseTab['status_code'] !== 200) {
        deliver_response(401, "Token invalide");
        die();
    }

    $tokenValue = str_replace('Bearer ', '', $token);
    $payload = get_payload($tokenValue);

    // Si le rôle requis ne correspond pas au role du token, alors interdiction, code 403
    if ($role_requis && $payload['role'] !== $role_requis) {
        deliver_response(403, "Vous n'avez pas les droits pour effectuer cette action");
        die();
    }

    return $payload;
}