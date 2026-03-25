<?php

namespace R301\Modele\Statistiques;

use R301\Modele\Rencontre\RencontreResultat;

class StatistiquesEquipe implements \JsonSerializable {
    private readonly array $rencontres;

    //Réecriture des classes pour remplacer par des tableaux pour que ce soit plus facile à parcourir
    public function __construct(
        array $rencontres
    ) {
        $this->rencontres = $rencontres;
    }

    private function nbMatchsJoues(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->joue();}));
    }

    public function nbVictoires(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->gagne(); }));
    }

    public function nbNuls(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->nul(); }));
    }

    public function nbDefaites(): int {
        return count(array_filter($this->rencontres, function($rencontre) { return $rencontre->perdu() ;}));
    }

    public function pourcentageDeVictoires(): int {
        return $this->nbVictoires() / $this->nbMatchsJoues() * 100;
    }

    public function pourcentageDeNuls(): int {
        return $this->nbNuls() / $this->nbMatchsJoues() * 100;
    }

    public function pourcentageDeDefaites(): int {
        return $this->nbDefaites() / $this->nbMatchsJoues() * 100;
    }

    public function jsonSerialize(): array {
        return [
            'nbVictoires' => $this->nbVictoires(),
            'nbNuls' => $this->nbNuls(),
            'nbDefaites' => $this->nbDefaites(),
            'pourcentageDeVictoires' => $this->pourcentageDeVictoires(),
            'pourcentageDeNuls' => $this->pourcentageDeNuls(),
            'pourcentageDeDefaites' => $this->pourcentageDeDefaites()
        ];
    }
}


