<?php

namespace R301\Modele\Statistiques;

use R301\Modele\Joueur\Joueur;
use R301\Modele\Participation\Poste;

class StatistiquesJoueurs implements \JsonSerializable {
    private readonly array $participations;
    private readonly array $rencontresJouees;
    //Réecriture des classes pour remplacer par des tableaux pour que ce soit plus facile à parcourir
    public function __construct(
        array $participations,
        array $rencontres,
        array $joueurs
    ) {
        $this->participations = $participations;
        usort($rencontres, function ($a, $b) { return $a->getDateEtHeure() <=> $b->getDateEtHeure(); });
        $this->rencontresJouees = array_filter($rencontres, function ($rencontre) { return $rencontre->joue(); });
        $this->joueurs = $joueurs;
    }
    //Renvoie toutes les participations à des rencontres du joueur $joueur
    private function participationsDunJoueur(Joueur $joueur): array {
        return array_filter($this->participations, function ($participation) use ($joueur) {
            return $participation->getRencontre()->joue()
                && $participation->getParticipant()->getJoueurId() === $joueur->getJoueurId();
        });
    }
    //Renvoie toutes les participations à des rencontres du joueur $joueur au poste $poste
    private function participationsDunJoueurAuPoste(Joueur $joueur, Poste $poste): array {
        return array_filter($this->participations, function ($participation) use ($joueur, $poste) {
            return $participation->getRencontre()->joue()
                && $participation->getPoste() === $poste
                && $participation->getParticipant()->getJoueurId() === $joueur->getJoueurId();
        });
    }
    //Renvoie un booléen indiquant si le joueur participe à la rencontre ou non
    private function leJoueurAParticipeALaRencontre(Joueur $joueur, mixed $rencontre): bool {
        foreach ($this->participationsDunJoueur($joueur) as $participations) {
            if ($participations->getRencontre()->getRencontreId() === $rencontre->getRencontreId()) {
                return true;
            }
        }

        return false;
    }
    //Renvoie le poste le plus performant pour le joueur $joueur (le poste où il a les meilleures évaluations)
    public function posteLePlusPerformant(Joueur $joueur): ?Poste {
        $participations = $this->participationsDunJoueur($joueur);
        if  (count($participations) === 0) {
            return null;
        } else {
            $moyenneParPoste = [];
            foreach (Poste::cases() as $poste) {
                $moyenneParPoste[$poste->name] = $this->moyenneDesEvaluationsPourLePoste($joueur, $poste);
            }

            arsort($moyenneParPoste);
            return Poste::fromName(array_key_first($moyenneParPoste));
        }
    }

    //Renvoie le nombre de fois où le joueur $joueur à été choisi consécutivement à des rencontres à la date actuelle
    public function nbRencontresConsecutivesADate(Joueur $joueur): int {
        $nbRencontresConsecutivesADate = 0;

        foreach ($this->rencontresJouees as $rencontre) {
            if($this->leJoueurAParticipeALaRencontre($joueur, $rencontre)) {
                $nbRencontresConsecutivesADate++;
            } else {
                break;
            }
        }

        return $nbRencontresConsecutivesADate;
    }

    public function nbTitularisations(Joueur $joueur): int {
        return count(array_filter($this->participationsDunJoueur($joueur), function($participation) {
            return $participation->estTitulaire();
        }));
    }

    public function nbRemplacant(Joueur $joueur): int {
        return count(array_filter($this->participationsDunJoueur($joueur), function($participation) {
            return $participation->estRemplacant();
        }));
    }

    private function nbMatchsEvalues(Joueur $joueur): int {
        return count(
            array_filter($this->participationsDunJoueur($joueur), function($participation) {
                return $participation->getPerformance() !== null;
            })
        );
    }

    private function nbMatchsJoues(Joueur $joueur): int {
        return count(
            array_filter($this->participationsDunJoueur($joueur), function($participation) {
                return $participation->getRencontre()->getResultat() !== null;
            })
        );
    }

    private function nbMatchsGagnes(Joueur $joueur): int {
        return count(
            array_filter($this->participationsDunJoueur($joueur), function($participation) {
                return $participation->getRencontre()->gagne();
            })
        );
    }

    public function moyenneDesEvaluations(Joueur $joueur): ?float {
        $participations = $this->participationsDunJoueur($joueur);

        if ($this->nbMatchsEvalues($joueur) > 0) {
            return array_sum(array_map( function($participation) { return $participation->notePerformance(); }, $participations)) / $this->nbMatchsEvalues($joueur);
        } else {
            return null;
        }
    }

    private function moyenneDesEvaluationsPourLePoste(Joueur $joueur, Poste $poste) {
        $participations = $this->participationsDunJoueurAuPoste($joueur, $poste);

        if ($this->nbMatchsEvalues($joueur) > 0) {
            return array_sum(array_map( function($participation) { return $participation->notePerformance(); }, $participations)) / $this->nbMatchsEvalues($joueur);
        } else {
            return null;
        }
    }

    public function pourcentageDeMatchsGagnes(Joueur $joueur): ?int {
        if ($this->nbMatchsJoues($joueur) > 0) {
            return $this->nbMatchsGagnes($joueur) / $this->nbMatchsJoues($joueur) * 100;
        } else {
            return null;
        }
    }

    //Permet de faire ce traitement lorsqu'on appelle json_encode car certaines fonctions sont privées et on ne peut donc pas les appeler en dehors de la classe
    public function jsonSerialize(): array {
        $result = [];
        foreach($this->joueurs as $joueur){
            $poste = $this->posteLePlusPerformant($joueur); // obligé de décomposer le poste car peut être null et dans ce cas là alors on utilise pas name dessus
            $result[] = [
                'joueur_id' => $joueur->getJoueurId(),
                'posteLePlusPerformant' => $poste !== null ? $poste->name : null, // si le poste est pas nul alors retourner le nom de l'enum, sinon null
                'nbRencontresConsecutives' => $this->nbRencontresConsecutivesADate($joueur),
                'nbTitularisations' => $this->nbTitularisations($joueur),
                'nbRemplacant' => $this->nbRemplacant($joueur),
                'moyenneDesEvaluations' => $this->moyenneDesEvaluations($joueur),
                'pourcentageDeMatchsGagnes' => $this->pourcentageDeMatchsGagnes($joueur)
            ];
        }
        return $result;
    }
}


