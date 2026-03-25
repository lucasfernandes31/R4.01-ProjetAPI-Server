#### ########################################### ####
#### Documentation de l'API : SERVEUR DE DONNÉES ####

## GROUPE D4 - Rémi SAGNES - Lucas FERNANDES
# Documentation de l'API : Serveur de données

Tous les endpoints nécessitent un token JWT valide transmis dans le header 'Authorization', sauf mention contraire.  
Les méthodes POST, PUT et DELETE sont en général réservées aux administrateurs.

'''
Authorization: Bearer <jwt_token>
'''

---

## POST /auth

Transmet les identifiants au serveur d'authentification et retourne le token JWT correspondant.  
Le serveur de données joue ici le rôle d'intermédiaire : le client ne contacte jamais directement le serveur d'auth.

Aucun token requis pour cet endpoint.

Corps de la requête (JSON)
'''json
{
  "login": "zi-hao",
  "password": "$iutinfo"
}
'''

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Token délivré avec succès.",
  "data": "<jwt_token>"
}
'''

Erreurs

- '400' : Attributs 'login' ou 'password' manquants.
- '403' : Login inexistant ou password incorrect.
- '403' : Méthode HTTP non supportée (seul 'POST' est accepté).

---

## GET /commentaire : 'GET /joueur/{id}/commentaire'

Retourne la liste des commentaires associés au joueur d'ID 'joueurId'.  
Réservé aux administrateurs.

Paramètre d'URL

- 'joueurId' (entier) : ID du joueur dont on veut récupérer les commentaires.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Commentaires du joueur d'ID 2 récupérés avec succès.",
  "data": [...]
}
'''

Erreurs

- '400' : Paramètre 'joueurId' manquant ou invalide.
- '404' : Le joueur d'ID spécifié n'existe pas.

---

## POST /commentaire

Crée un nouveau commentaire pour un joueur donné.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "contenu": "Très bonne performance ce soir.",
  "joueurId": 2
}
'''

Le champ 'contenu' ne peut pas être vide.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Commentaire inséré avec succès pour le joueur d'ID 2.",
  "data": null
}
'''

Erreurs

- '400' : Champ 'contenu' ou 'joueurId' manquant, ou 'contenu' vide.
- '404' : Le joueur d'ID spécifié n'existe pas.
- '500' : Erreur interne du serveur.

---

## DELETE /commentaire : 'DELETE /commentaire/{id}'

Supprime le commentaire d'ID 'commentaireId'.  
Réservé aux administrateurs.

Paramètre d'URL

- 'commentaireId' (entier) : ID du commentaire à supprimer.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Commentaire d'ID 8 supprimé avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Paramètre 'commentaireId' manquant ou invalide.
- '404' : Le commentaire d'ID spécifié n'existe pas.
- '403' : Méthode HTTP non supportée.

---

---

## GET /joueur

Retourne la liste complète de tous les joueurs.  
Token requis (tous rôles).

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Liste des joueurs récupérée avec succès.",
  "data": [...]
}
'''

Retourne un '204' si la liste est vide.

Erreurs

- '400' : Paramètre(s) inconnu(s) dans la requête.
- '403' : Méthode HTTP non supportée.

---

## GET /joueur/{id}

Retourne le joueur correspondant à l'ID fourni.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Joueur d'id 8 récupéré avec succès.",
  "data": {...}
}
'''

Erreurs

- '400' : L'ID n'est pas un entier.
- '404' : Aucun joueur ne correspond à cet ID.

---

## GET /joueur/recherche/{recherche}/{statut}

Retourne la liste des joueurs filtrés par une chaîne de recherche et un statut.  
Le statut peut être vide pour ne pas filtrer dessus.

Paramètres d'URL

- 'recherche' (string) : Texte recherché dans les champs du joueur (nom, prénom, etc.).
- 'statut' (string, optionnel) : Statut du joueur parmi : 'ACTIF', 'BLESSE', 'ABSENT', 'SUSPENDU'.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Liste des joueurs recherchés récupérée avec succès",
  "data": [...]
}
'''

---

## GET /joueur/rencontre/{rencontreId}

Retourne la liste des joueurs sélectionnables pour le match d'ID 'rencontreId' (joueurs non encore assignés à cette rencontre).

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Liste des joueurs sélectionnables pour le match d'ID 4 récupérée avec succès",
  "data": [...]
}
'''

Erreurs

- '400' : L'ID de match n'est pas un entier.
- '404' : Aucun match ne correspond à cet ID.

---

## POST /joueur

Crée un nouveau joueur.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "nom": "Dupont",
  "prenom": "Jean",
  "numeroDeLicence": "LIC-0042",
  "dateDeNaissance": "1998-04-15",
  "tailleEnCm": 182,
  "poidsEnKg": 78,
  "statut": "ACTIF"
}
'''

Tous les champs sont obligatoires. La date de naissance doit être au format 'Y-m-d', et le joueur doit avoir entre 10 et 90 ans. Le statut doit être l'un de : 'ACTIF', 'BLESSE', 'ABSENT', 'SUSPENDU'.

Réponse en cas de succès (201)
'''json
{
  "status_code": 201,
  "status_message": "La requête a réussi et un nouveau joueur a été créé.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, format invalide, date de naissance incorrecte ou statut invalide.
- '403' : Droits insuffisants.
- '500' : Erreur interne, impossible de créer la ressource.

---

## PUT /joueur/{id}

Met à jour les informations du joueur d'ID 'id'.  
Réservé aux administrateurs.

Le corps de la requête est identique au 'POST'. Tous les champs sont obligatoires.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "La requête a réussi et le joueur d'id 8 a été mis à jour.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, format invalide ou date de naissance incorrecte.
- '403' : Droits insuffisants.
- '404' : Le joueur d'ID spécifié n'existe pas.

---

## DELETE /joueur/{id}

Supprime le joueur d'ID 'id'.  
Réservé aux administrateurs.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Joueur d'ID 8 supprimé avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Paramètre 'id' manquant ou non entier.
- '403' : Droits insuffisants.
- '403' : Méthode HTTP non supportée.
- '404' : Le joueur d'ID spécifié n'existe pas.

---

---

## GET /participation

Retourne la liste complète de toutes les participations.  
Token requis (tous rôles).

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Liste de toutes les participations obtenue avec succès.",
  "data": [...]
}
'''

---

## GET /participation/{rencontreId}

Retourne la feuille de match (liste des participations) pour la rencontre d'ID 'rencontreId'.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Feuille de match obtenue avec succès.",
  "data": [...]
}
'''

La liste peut être vide si aucun joueur n'a encore été assigné au match.

Erreurs

- '400' : L'ID n'est pas un entier ou paramètre inconnu.
- '403' : Méthode HTTP non supportée.

---

## POST /participation

Assigne un joueur à un match avec son poste et son statut titulaire/remplaçant.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "joueurId": 3,
  "rencontreId": 1,
  "poste": "AILIER",
  "titulaireOuRemplacant": "TITULAIRE"
}
'''

Tous les champs sont obligatoires. Le joueur et la rencontre doivent exister. Les valeurs acceptées pour 'poste' et 'titulaireOuRemplacant' dépendent des énumérations définies côté serveur.

Réponse en cas de succès (201)
'''json
{
  "status_code": 201,
  "status_message": "Joueur assigné au match avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, format invalide, joueur ou rencontre inexistants, joueur déjà sur la feuille de match, ou poste déjà occupé.
- '403' : Droits insuffisants.

---

## PUT /participation/{participationId}

Modifie le poste et le statut titulaire/remplaçant d'une participation existante.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "joueurId": 3,
  "poste": "PIVOT",
  "titulaireOuRemplacant": "REMPLACANT"
}
'''

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Participation d'ID 24 modifiée avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, format invalide, joueur déjà sur la feuille, ou poste déjà occupé.
- '403' : Droits insuffisants.

---

## PUT /participation/{participationId} : mise à jour de la performance

Met à jour la performance d'un joueur pour une participation donnée.  
Le match doit avoir déjà eu lieu.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "performance": "BON"
}
'''

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Performance de la participation d'ID 24 mise à jour avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Champ 'performance' manquant ou invalide, ou 'participationId' non entier.
- '403' : Droits insuffisants.
- '422' : Le match n'a pas encore eu lieu, impossible de saisir une performance.

---

## DELETE /participation/{participationId}

Supprime la participation d'ID 'participationId'.  
Réservé aux administrateurs.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Participation d'ID 8 supprimée avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Paramètre manquant ou non entier.
- '403' : Droits insuffisants.
- '404' : La participation n'existe pas.

---

## DELETE /participation/performance/{participationId}

Supprime la performance associée à la participation d'ID 'participationId'.  
Le match doit avoir déjà eu lieu.  
Réservé aux administrateurs.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Performance de la participation d'ID 8 supprimée avec succès.",
  "data": null
}
'''

Erreurs

- '400' : Paramètre manquant ou non entier.
- '403' : Droits insuffisants.
- '403' : Méthode HTTP non supportée.
- '404' : La participation n'existe pas ou le match n'a pas encore eu lieu.

---

---

## GET /rencontre

Retourne la liste complète de toutes les rencontres.  
Aucun token requis pour cet endpoint.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Toutes les rencontres obtenues avec succès.",
  "data": [...]
}
'''

---

## GET /rencontre/{rencontreId}

Retourne la rencontre correspondant à l'ID fourni.  
Aucun token requis pour cet endpoint.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Rencontre d'ID 4 obtenue avec succès.",
  "data": {...}
}
'''

Retourne un '200' avec 'data: null' si la rencontre n'existe pas.

Erreurs

- '400' : L'ID n'est pas un entier ou paramètre inconnu.

---

## POST /rencontre

Crée une nouvelle rencontre.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "dateHeure": "2025-06-15T20:30",
  "equipeAdverse": "FNATICS",
  "adresse": "12 rue des Sports, Toulouse",
  "lieu": "DOMICILE"
}
'''

La date doit être au format 'Y-m-d\TH:i' et obligatoirement dans le futur. Le lieu doit être 'DOMICILE' ou 'EXTERIEUR'.

Réponse en cas de succès (201)
'''json
{
  "status_code": 201,
  "status_message": "La requête a réussi et une nouvelle rencontre a été créée.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, date dans le passé ou format invalide, lieu invalide.
- '500' : Erreur interne, impossible de créer la ressource.

---

## PUT /rencontre : modification

Modifie une rencontre existante. La rencontre ne doit pas être passée.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "rencontreId": 4,
  "dateHeure": "2025-06-20T18:00",
  "equipeAdverse": "FNATICS",
  "adresse": "12 rue des Sports, Toulouse",
  "lieu": "EXTERIEUR"
}
'''

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "La rencontre d'ID 4 a bien été modifiée.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, ID non entier, rencontre inexistante, date invalide, lieu invalide, ou rencontre déjà passée.

---

## PUT /rencontre : enregistrement du résultat

Enregistre le résultat d'une rencontre passée.  
Réservé aux administrateurs.

Corps de la requête (JSON)
'''json
{
  "rencontreId": 4,
  "resultat": "VICTOIRE"
}
'''

Le résultat doit être 'VICTOIRE', 'DEFAITE' ou 'NULL'. Le match doit avoir déjà eu lieu.

Réponse en cas de succès (201)
'''json
{
  "status_code": 201,
  "status_message": "Le résultat de la rencontre d'ID 4 a bien été enregistré.",
  "data": null
}
'''

Erreurs

- '400' : Champs manquants, ID non entier, rencontre inexistante, résultat invalide, ou match pas encore joué.

---

## DELETE /rencontre/{rencontreId}

Supprime la rencontre d'ID 'rencontreId'. Impossible si les résultats ont déjà été saisis.  
Réservé aux administrateurs.

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "La rencontre d'ID 4 a bien été supprimée.",
  "data": null
}
'''

Erreurs

- '400' : Paramètre manquant, non entier, rencontre inexistante, ou résultats déjà saisis.
- '403' : Méthode HTTP non supportée.

---

---

## GET /statistiques/equipe/

Retourne les statistiques globales de l'équipe.  
Token requis (tous rôles).

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Statistiques de l'équipe récupérées avec succès.",
  "data": {...}
}
'''

Erreurs

- '400' : Paramètre(s) inconnu(s), aucun paramètre fourni, ou plus d'un paramètre fourni.
- '403' : Méthode HTTP non supportée.
- '500' : Erreur interne du serveur.

---

## GET /statistiques/joueurs/

Retourne les statistiques individuelles de tous les joueurs.  
Token requis (tous rôles).

Réponse en cas de succès (200)
'''json
{
  "status_code": 200,
  "status_message": "Statistiques des joueurs récupérées avec succès.",
  "data": [...]
}
'''

Erreurs

- '400' : Paramètre(s) inconnu(s), aucun paramètre fourni, ou plus d'un paramètre fourni.
- '403' : Méthode HTTP non supportée.
- '500' : Erreur interne du serveur.
