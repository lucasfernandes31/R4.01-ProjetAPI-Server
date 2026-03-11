<?php

require_once __DIR__ . '/autoload.php';
require_once __DIR__ . '/utils.php';

use R301\Controleur\JoueurControleur;
use R301\Modele\Joueur\JoueurStatut;


$http_method = $_SERVER['REQUEST_METHOD'];

$joueurControleur = JoueurControleur::getInstance();

switch($http_method){

  case 'GET':

    ///////////
    // Vérification de la validité des paramètres (fait ici car plus simple et mieux qu'un if englobant)
    $allowed_params = ['id', 'recherche', 'statut', 'rencontreId'];
    $unknown_params = array_diff(array_keys($_GET), $allowed_params);
    
    if(!empty($unknown_params)){
      deliver_response(400, "Paramètre(s) inconnu(s)");
      break;
    }

    ///////////
    // Récupération joueurs par ID
    if(isset($_GET['id'])){
      if(!isIntString($_GET['id'])){
        deliver_response(400, "L'id doit être un entier.");
        break;
      }

      // try catch parce que si aucun joueur de l'ID spécifié, le mapToJoueur plante dans le DAO
      try {
        $joueur = $joueurControleur->getJoueurById((int)$_GET['id']);
        deliver_response(200, "Joueur d'id " . $_GET['id'] . " récupéré avec succès.", $joueur);
      } catch(\Throwable $e) {
        deliver_response(404, "Aucun joueur d'id " . $_GET['id'] . ".");
      }
      break;
    }

    ///////////
    // Récupération joueurs par recherche
    if(isset($_GET['recherche']) && isset($_GET['statut'])){
      $joueurs = $joueurControleur->rechercherLesJoueurs($_GET['recherche'], $_GET['statut']);
      deliver_response(200, "Liste des joueurs recherchés récupérée avec succès", $joueurs);
      break;
    }

    ///////////
    // Liste des joueurs sélectionnables pour un match
    if(isset($_GET['rencontreId'])){
      if(!isIntString($_GET['rencontreId'])){
        deliver_response(400, "L'id de match doit être un entier.");
        break;
      }

      // try catch parce que si aucun match de l'ID spécifié, le DAO plante
      try {
        $joueurs = $joueurControleur->listerLesJoueursSelectionnablesPourUnMatch((int)$_GET['rencontreId']);
        deliver_response(200, "Liste des joueurs sélectionnables pour le match d'ID " . $_GET['rencontreId'] . " récupérée avec succès", $joueurs);
      } catch(\Throwable $e) {
        deliver_response(404, "Aucun match d'id " . $_GET['rencontreId'] . ".");
      }
      break;
    }

    ///////////
    // Si aucune option spécifiée : liste de tous les joueurs
    $joueurs = $joueurControleur->listerTousLesJoueurs();
    if(empty($joueurs)){
      deliver_response(204, "Liste des joueurs récupérée mais vide.", $joueurs);
    } else {
      deliver_response(200, "Liste des joueurs récupérée avec succès.", $joueurs);
    }

    break;
  
  
  case 'POST':

    ///////////
    // Insertion d'un nouveau joueur
    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    // Vérification validité des champs
    if(arePostPutChampsValids($data['nom'] ?? null,$data['prenom'] ?? null,$data['numeroDeLicence'] ?? null,$data['dateDeNaissance'] ?? null,
                              $data['tailleEnCm'] ?? null,$data['poidsEnKg'] ?? null,$data['statut'] ?? null)) {

        if(!isDateNaissanceValide($data['dateDeNaissance'])){
          deliver_response(400, 'Date de naissance au mauvais format. Format attendu : Y-m-d. Joueur de maximum 90 ans et de minimum 10 ans.');
          break;
        }

        if(!isStatutValid($data['statut'])){
          deliver_response(400, 'Statut invalide, format attendu : ACTIF, BLESSE, ABSENT ou SUSPENDU.');
          break;
        }

        $dateDeNaissance = DateTime::createFromFormat('Y-m-d', $data['dateDeNaissance']);

        $success = $joueurControleur->ajouterJoueur(
          $data['nom'],
          $data['prenom'],
          $data['numeroDeLicence'],
          $dateDeNaissance,
          (int)$data['tailleEnCm'],
          (int)$data['poidsEnKg'],
          $data['statut']
        );
        
        if($success){
          deliver_response(201, "La requête a réussi et un nouveau joueur a été créée.");
        } else {
          deliver_response(500, "Erreur interne, impossible de créer la ressource.");
        }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants ou au mauvais format.");
    }
    break;


  case 'PUT':

    ///////////
    // Insertion d'un nouveau joueur
    $postedData = file_get_contents('php://input');
    $data = json_decode($postedData, true);

    // Vérification validité des champs
    if(isset($_GET['id']) && arePostPutChampsValids($data['nom'] ?? null,$data['prenom'] ?? null,$data['numeroDeLicence'] ?? null,$data['dateDeNaissance'] ?? null,
                             $data['tailleEnCm'] ?? null,$data['poidsEnKg'] ?? null,$data['statut'] ?? null) && isIntString($_GET['id']) ) {

        if(!isDateNaissanceValide($data['dateDeNaissance'])){
          deliver_response(400, 'Date de naissance au mauvais format. Format attendu : Y-m-d. Joueur de maximum 90 ans et de minimum 10 ans.');
          break;
        }

        $dateDeNaissance = DateTime::createFromFormat('Y-m-d', $data['dateDeNaissance']);

        try {
          $success = $joueurControleur->modifierJoueur(
            $_GET['id'],
            $data['nom'],
            $data['prenom'],
            $data['numeroDeLicence'],
            $dateDeNaissance,
            (int)$data['tailleEnCm'],
            (int)$data['poidsEnKg'],
            $data['statut']
          );
          deliver_response(200, "La requête a réussi et le joueur d'id ". $_GET['id'] ." a été mis à jour.");
        } catch (\Throwable $ex){
          deliver_response(404, "Le joueur d'ID ". $_GET['id'] ." n'existe pas.");
        }
    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants ou au mauvais format.");
    }
    break;

  
  case 'DELETE':

    if(isset($_GET['id']) && isIntString($_GET['id'])){

      try{
        $joueurControleur->supprimerJoueur((int)$_GET['id']);
        deliver_response(200, "Joueur d'ID ".$_GET['id']." supprimé avec succès.");
      } catch (\Throwable $ex) {
        deliver_response(404, "Le joueur d'ID ".$_GET['id']." n'existe pas.");
      }

    } else {
      deliver_response(400, "Syntaxe de la requête non conforme, paramètres manquants ou au mauvais format.");
    }

    break;


  default:
    deliver_response(403, "Méthode HTTP non supportée.");
    break;


}


//////////
// Fonction permettant de vérifier si un statut fourni est valide ou non
// retourne true si valide, false si invalide
function isStatutValid($statutStr){
  return !is_null(JoueurStatut::fromName($statutStr));
}

//////////
// Fonction permettant de vérifier la validité des champs passés pour les méthodes POST et PUT
// retourne true si valides, false si invalides
function arePostPutChampsValids($nom,$prenom,$numLicence,$dateDeNaissance,$tailleCm,$poidsKg,$statut){

  return  !is_null($nom) && is_string($nom) &&
          !is_null($prenom) && is_string($prenom) &&
          !is_null($numLicence) && is_string($numLicence) &&
          !is_null($dateDeNaissance)  &&
          !is_null($tailleCm) && isIntString($tailleCm) &&
          !is_null($poidsKg) && isIntString($poidsKg) &&
          !is_null($statut);
}

//////////
// Fonction permettant de vérifier la validité d'une date de naissance (format + comprise entre il y a 90 ans et il y a 10 ans)
// retourne true si valide, false si invalide
function isDateNaissanceValide($dateDeNaissance){
  
  $ddn = DateTime::createFromFormat('Y-m-d', $dateDeNaissance);
  // est false si format invalide
  // vérification des limites aussi avec new DateTime
  return $ddn && $ddn >= new DateTime('-90 years') && $ddn <= new DateTime('-10 years');
}

?>