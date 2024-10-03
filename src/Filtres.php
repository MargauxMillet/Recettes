<?php

namespace App;

use Exception;

class Filtres {

    public function deleteFiltreSimple ($filtre)
    {
        if(isset($_GET['supfiltre']) && $_GET['supfiltre'] === $filtre) {
            $urlParts = parse_url($_SERVER['REQUEST_URI']);
            $query = [];
            parse_str($urlParts['query'], $query);
            $query[$filtre] = "";
            $query['supfiltre'] = "ok";
            $newUrlPartsQuery = http_build_query($query);
            $newUrl = $urlParts['path'] . '?' . $newUrlPartsQuery;
            header('Location:' . $newUrl);
            exit();
        }
    }

    public function deleteFiltreArray ($filtre)
    {
        if(isset($_GET['supfiltre']) && str_contains($_GET['supfiltre'], $filtre)) {
            $urlParts = parse_url($_SERVER['REQUEST_URI']);
            $query = [];
            parse_str($urlParts['query'], $query);
            $filtreID = str_replace($filtre, '', $_GET['supfiltre']);
            $index = array_search($filtreID, $query[$filtre . 's']);
            unset($query[$filtre . 's'][$index]);
            $query['supfiltre'] = "ok";
            $newUrlPartsQuery = http_build_query($query);
            $newUrl = $urlParts['path'] . '?' . $newUrlPartsQuery;
            header('Location:' . $newUrl);
            exit();
        }
    }

    public function deleteFiltreDurationminmax ()
    {
        if(isset($_GET['supfiltre']) && $_GET['supfiltre'] === 'durationminmax') {
            $urlParts = parse_url($_SERVER['REQUEST_URI']);
            $query = [];
            parse_str($urlParts['query'], $query);
            $query['durationmin'] = "";
            $query['durationmax'] = "";
            $query['supfiltre'] = "ok";
            $newUrlPartsQuery = http_build_query($query);
            $newUrl = $urlParts['path'] . '?' . $newUrlPartsQuery;
            header('Location:' . $newUrl);
            exit();
        }
    }

    public function applyFiltres ($pdo, $objetTest, $errors)
    {
        //Je recupere toutes les recettes correpondant aux filtres
        $success = 0;
        $filter = 0;
        
        if (!empty($errors)) {
            return true;
        } else {
            if (isset($_GET['categories'])) {
                $filter += 1;
                if ($this->byCategories($pdo, $objetTest)) {
                    $success += 1;
                }
            }
            if (isset($_GET['ingredients'])) {
                $filter += 1;
                if ($this->byIngredients($pdo, $objetTest)) {
                    $success += 1;
                }
            }

            if (isset($_GET['durationmin']) && isset($_GET['durationmax'])){

                if ($_GET['durationmin'] !== "" || $_GET['durationmax'] !== "") {
                    $filter += 1;
                }

                if ($this->byDuration($pdo, $objetTest) ) {
                    $success += 1;
                }
            }

            if ($success === $filter) {
                return true;
            }
        }
        
    }

    public function byCategories ($pdo, $objetTest)
    {
        $requete = $pdo->prepare('
        SELECT c.id, c.name
        FROM categorie_recette cr 
        JOIN categorie c ON cr.categorie_id = c.id
        WHERE cr.recette_id = :id
        ');
        $requete->execute(['id' => $objetTest['id']]);
        $categoriesRecette = $requete->fetchAll();
        foreach($categoriesRecette as $categorieRecette) {
            foreach($_GET['categories'] as $categorieFiltre) {
                if ($categorieRecette['id'] == htmlentities($categorieFiltre)) {
                    return true;
                }
            }
        }
    }

    public function byIngredients ($pdo, $objetTest)
    {
        $requete = $pdo->prepare('
        SELECT i.id, i.name, ir.quantite, ir.unite
        FROM ingredient_recette ir 
        JOIN ingredient i ON ir.ingredient_id = i.id
        WHERE ir.recette_id = :id
        ');
        $requete->execute(['id' => $objetTest['id']]);
        $ingredientsRecette = $requete->fetchAll();
        foreach($ingredientsRecette as $ingredientRecette) {
            foreach($_GET['ingredients'] as $ingredientFiltre) {
                if ($ingredientRecette['id'] == htmlentities($ingredientFiltre)) {
                    return true;
                }
            }
        }
    }

    public function byDuration ($pdo, $objetTest)
    {
        $true = 0;
        if($_GET['durationmin'] !== "" && $_GET['durationmax'] !== "") {
            if ($this->filtreDuree($pdo, $objetTest, htmlentities($_GET['durationmin']), htmlentities($_GET['durationmax']))) {
                $true += 1;
            }
        }

        if($_GET['durationmin'] !== "" && $_GET['durationmax'] == "") {
            if ($this->filtreDuree($pdo, $objetTest, htmlentities($_GET['durationmin']))) {
                $true += 1;
            }
        }

        if($_GET['durationmin'] == "" && $_GET['durationmax'] !== "") {
            if ($this->filtreDuree($pdo, $objetTest, 0, htmlentities($_GET['durationmax']))) {
                $true += 1;
            }
        }

        if ($true === 1) {
            return true;
        }
    }

    public function filtreDuree ($pdo, $objetTest, $min, $max = 1000)
    {
        $requete = $pdo->prepare('
        SELECT id
        FROM recette
        WHERE duration BETWEEN :durationmin AND :durationmax
        ');
        $requete->execute(['durationmin' => $min, 'durationmax' => $max]);
        $dureeRecette = $requete->fetchAll();
        foreach($dureeRecette as $duree) {
            if (in_array($objetTest['id'], $duree)) {
                return true;
            }
        }
        
    }

}

?>