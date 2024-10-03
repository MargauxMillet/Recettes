<?php

use App\ConnectionBD;
use App\Errors;

$pdo = ConnectionBD::getPDO();

// Recuperation des données de la recette
$requete = $pdo->prepare("SELECT * FROM recette WHERE slug = :slug");
$requete->execute(['slug' => $params['slug']]);
$recette = $requete->fetch();

$title = 'Modifier la recette: ' . $recette['name'];
$selected = 'none';

$errors = [];
if(!empty($_POST)) {

    // Je verifie si il y a des erreurs dans les informations rentrées
    // Titre
    // Titre et slug
    $titre = str_replace(' ', '', $_POST['titre']);
    if(strlen($titre) < 3) {
        $errors['titre'] = 'Le titre est trop court';
    }
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $titre);
    $slug = strtolower($slug);
    $slug = preg_replace('/[^a-z0-9]+/', '', $slug);
    if ($slug !== $recette['slug']) {
        $requete = $pdo->query("SELECT slug FROM recette");
        $allRecettesSlug = $requete->fetchALL();
        $allRecettesSlugListe = [];
        foreach ($allRecettesSlug as $recetteSlug) {
            $allRecettesSlugListe[]= $recetteSlug['slug'];
        }
        if(in_array($slug, $allRecettesSlugListe)) {
            $errors['slugused'] = "Le titre de la recette est déjà utilisé";
        }
    }
    
    // Durée
    if(!(int)$_POST['duree']) {
        $errors['duree'] = 'La durée doit être exprimée en chiffres';
    }
    // TypeCat
    if(!isset($_POST['typeCat'])) {
        $errors['typeCat'] = "Le type de la recette doit être sélectionné";
    }
    // NbPersonne
    if(!(int)$_POST['nbPersonne']) {
        $errors['nbPersonneint'] = "Le nombre de personnes doit être exprimé en chiffres";
    }
    if($_POST['nbPersonne'] < 1) {
        $errors['nbPersonneneg'] = "Le nombre de personnes doit être supérieur ou égal à 1";
    }
    // Quantité
    $notInt = false;
    for($i = 0; $i < count($_POST['ingredientQuantite']); $i++){
        if(!(int)($_POST['ingredientQuantite'][$i]) && ($_POST['ingredientQuantite'][$i]) !== "") {
            $notInt = true;
        }
    }
    if($notInt) {
        $errors['quantiteint'] = "La quantité doit être exprimée en chiffres";
    }

    for($i = 0; $i < count($_POST['ingredientQuantite']); $i++){
        if($_POST['ingredientQuantite'][$i] == '' && $_POST['ingredientUnite'][$i] !== '') {
            $errors['quantiteunitematch'] = "Si une unite est choisie, la quantité ne peut pas être nulle";
        }
    }
    
    // Ingredient
    $null = true;
    $match = false;
    for($i = 0; $i < count($_POST['ingredient']); $i++){
        if($_POST['ingredient'][$i] !== "") {
            $null = false;
        }
        if(($_POST['ingredient'][$i] == "") && ($_POST['ingredientUnite'][$i] == "") && ($_POST['ingredientQuantite'][$i] == "")) {
            $match = true;
        }
    }
    if($null) {
        $errors['ingredientnull'] = "Au moins un ingrédient doit être sélectionné";
    }
    if($match) {
        $errors['ingredientmatch'] = "Un ingrédient doit être sélectionné pour chaque ligne";
    }
    
    $ingredientsPresents = [];
    $duplica = false;
    foreach($_POST['ingredient'] as $ingredient) {
        if (in_array($ingredient, $ingredientsPresents)) {
            $duplica = true;
        } else {
            $ingredientsPresents[] = $ingredient;
        }
    }
    if ($duplica == true) {
        $errors['ingredientduplica'] = "Le même ingrédient a été sélectionné plusieurs fois";
    }
    // Content
    if(strlen($_POST['content']) < 10) {
        $errors['content'] = 'La recette est trop courte';
    }

    // Si il n'y a pas d'erreurs, je modifie la base de données et renvoie vers le livre de recettes
    if (empty($errors)) {
        $requete = $pdo->prepare('
        UPDATE recette
        SET name = :name, slug = :slug, duration = :duration, content = :content
        WHERE id = :id
        ');
        $requete->execute(['name' => ucfirst($_POST['titre']), 'slug' => $slug, 'duration' => $_POST['duree'], 'content' => nl2br($_POST['content']), 'id' => $recette['id']]);

        $requete = $pdo->prepare('
        DELETE FROM typeCat_recette
        WHERE recette_id = :id
        ');
        $requete->execute(['id' => $recette['id']]);
        for($i=0; $i<count($_POST['typeCat']); $i++) {
            $requete = $pdo->prepare('
            INSERT INTO typeCat_recette (type_id, recette_id)
            VALUES (:type_id, :recette_id)
            ');
            $requete->execute(['type_id' => $_POST['typeCat'][$i], 'recette_id' => $recette['id']]);
        }

        $requete = $pdo->prepare('
        DELETE FROM categorie_recette
        WHERE recette_id = :id
        ');
        $requete->execute(['id' => $recette['id']]);
        if(isset($_POST['categorie'])) {
            for($i=0; $i<count($_POST['categorie']); $i++) {
            $requete = $pdo->prepare('
            INSERT INTO categorie_recette (categorie_id, recette_id)
            VALUES (:categorie_id, :recette_id)
            ');
            $requete->execute(['categorie_id' => $_POST['categorie'][$i], 'recette_id' => $recette['id']]);
            }
        }
        
        $requete = $pdo->prepare('
        DELETE FROM ingredient_recette
        WHERE recette_id = :id
        ');
        $requete->execute(['id' => $recette['id']]);
        for($i=0; $i<count($_POST['ingredient']); $i++) {
            $quantite = null;
            $unite = '';
            if($_POST['ingredientQuantite'][$i] !== '') {
                if($_POST['ingredientUnite'][$i] == 'L') {
                    $quantite = ($_POST['ingredientQuantite'][$i]) * 100;
                    $unite = 'cL';
                } elseif($_POST['ingredientUnite'][$i] == 'cL') {
                    $quantite = ($_POST['ingredientQuantite'][$i]);
                    $unite = 'cL';
                } elseif($_POST['ingredientUnite'][$i] == 'Kg') {
                    $quantite = ($_POST['ingredientQuantite'][$i]) * 1000;
                    $unite = 'g';
                } elseif($_POST['ingredientUnite'][$i] == 'g') {
                    $quantite = ($_POST['ingredientQuantite'][$i]);
                    $unite = 'g';
                } elseif($_POST['ingredientUnite'][$i] == '') {
                    $quantite = ($_POST['ingredientQuantite'][$i]);
                }
                $quantite = $quantite /($_POST['nbPersonne']*1);
            }
            $requete = $pdo->prepare('
            INSERT INTO ingredient_recette (quantite, unite, ingredient_id, recette_id)
            VALUES (:quantite, :unite, :ingredient_id, :recette_id)
            ');
            $requete->execute(['quantite' => $quantite, 'unite' => $unite, 'ingredient_id' => $_POST['ingredient'][$i], 'recette_id' => $recette['id']]);
        }

        header('Location: ' . '/mon-livre-de-recettes/' . $slug . '?modif=1');
        exit();
        
    } 

}

$Erreurs = new Errors($errors);

// Je recupere tous les ingredients
$requete = $pdo->query ('SELECT * FROM ingredient ORDER BY name ASC');
$ingredients = $requete->fetchAll();

// Je recupere les ingredients de la recette
$requete = $pdo->prepare('
    SELECT i.id, i.name, ir.quantite, ir.unite
    FROM ingredient_recette ir 
    JOIN ingredient i ON ir.ingredient_id = i.id
    WHERE ir.recette_id = :id 
');
$requete->execute(['id' => $recette['id']]);
$ingredientsRecette = $requete->fetchAll();

$ingredientsRecetteliste = [];
foreach ($ingredientsRecette as $ingredientRecette) {
    $ingredientsRecetteliste[] = $ingredientRecette['id'];
} 

// Je recupere tous les types (Entrée, Plat, Dessert)
$requete = $pdo->query ('SELECT * FROM typeCat ORDER BY id ASC');
$typeCats = $requete->fetchAll();

// Je recupere tous les types de la recette 
$requete = $pdo->prepare('
    SELECT *
    FROM typeCat_recette tr
    JOIN typeCat t ON tr.type_id = t.id
    WHERE tr.recette_id = :id
');
$requete->execute(['id' => $recette['id']]);
$typeCatsRecette = $requete->fetchAll();

$typeCatsRecetteliste = [];
foreach ($typeCatsRecette as $typeCatRecette) {
    $typeCatsRecetteliste[] = $typeCatRecette['type_id'];
}

// Je recupere toutes les categories (Végétarien, Au four, ...)
$requete = $pdo->query ('SELECT * FROM categorie ORDER BY id ASC');
$categories = $requete->fetchAll();

// Je recupere toutes les categories de la recette
$requete = $pdo->prepare('
    SELECT * 
    FROM categorie_recette cr
    JOIN categorie c ON cr.categorie_id = c.id
    WHERE cr.recette_id = :id
');
$requete->execute(['id' => $recette['id']]);
$categoriesRecette = $requete->fetchAll();

$categoriesRecetteliste = [];
foreach ($categoriesRecette as $categorieRecette) {
    $categoriesRecetteliste[] = $categorieRecette['id'];
}

function roundUpToHalf($number) {
    return ceil($number * 2) / 2;
}

?>

<div class="width-80p">
    <?php $newRequestURI = str_replace('/edit', '', $_SERVER['REQUEST_URI']) ?>
    <a href="<?=$newRequestURI?>" class="display-flex align-items-center recette-lien-retour margin-top-35">
        <div class="recette-lien-retour-fleche"></div>
        <p class="catamaran p-20 noir recette-lien-retour-text">Retour</p>
    </a>
</div>

<h1 class="page-title margin-top-40 cursor-default">Modifier la recette</h1>

<?php if (!empty($errors)): ?>
    <div class="page-recette-alerte-echec cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">La recette n'a pas pu être modifiée</h2>
    </div>
<?php endif ?>


<form action="" method="POST">

    <input type="text" name="id" value="<?= $recette['id']?>" class="hidden">

    <div class="display-flex flex-direction-column align-items-center margin-top-70 cursor-default">

        <?php if(isset($errors['typeCat'])):?>
            <p class="red catamaran p-20"><?= $errors['typeCat']?></p>
            <div class="display-flex justify-content-center margin-top-10">
                <?php foreach($typeCats as $typeCat): ?>
                    <div class="edit-recette-input-typecat">
                        <p class="catamaran p-25 js-edit-recette-input-typecat-p cursor-pointer hover-orange noir"><?= $typeCat['name']?></p>
                        <input type="checkbox" id="<?= $typeCat['id']?>" name="typeCat[]" value="<?= $typeCat['id']?>" class="hidden js-edit-recette-input-typecat-value">
                    </div>
                <?php endforeach ?>
            </div>
        <?php elseif(!empty($errors)): ?>
            <?php 
                $catsSelectionnees = [];
                foreach($_POST['typeCat'] as $cat) {
                    $catsSelectionnees[] = $cat;
                }
            ?>
            <div class="display-flex justify-content-center">
                <?php foreach($typeCats as $typeCat): ?>
                    <div class="edit-recette-input-typecat">
                        <p class="catamaran p-25 js-edit-recette-input-typecat-p cursor-pointer hover-orange <?php if (in_array($typeCat['id'], $catsSelectionnees)): ?>orange bold<?php else: ?>noir<?php endif ?>"><?= $typeCat['name']?></p>
                        <input type="checkbox" id="<?= $typeCat['id']?>" name="typeCat[]" value="<?= $typeCat['id']?>" <?php if (in_array($typeCat['id'], $catsSelectionnees)): ?> checked <?php endif?> class="hidden js-edit-recette-input-typecat-value">
                    </div>
                <?php endforeach ?>
            </div>
        <?php else: ?>
            <div class="display-flex justify-content-center">
                <?php foreach($typeCats as $typeCat): ?>
                    <div class="edit-recette-input-typecat">
                        <p class="catamaran p-25 js-edit-recette-input-typecat-p cursor-pointer hover-orange <?php if (in_array($typeCat['id'], $typeCatsRecetteliste)): ?>orange bold<?php else: ?>noir<?php endif ?>"><?= $typeCat['name']?></p>
                        <input type="checkbox" id="<?= $typeCat['id']?>" name="typeCat[]" value="<?= $typeCat['id']?>" <?php if (in_array($typeCat['id'], $typeCatsRecetteliste)): ?> checked <?php endif?> class="hidden js-edit-recette-input-typecat-value">
                    </div>
                <?php endforeach ?>
            </div>
        <?php endif?>
    </div>

    <div class="display-flex flex-direction-column margin-center edit-recette-input-titre-div margin-top-30 cursor-default">
        <?php if(isset($errors['titre']) || isset($errors['slugused'])):?>
            <?php if(isset($errors['titre'])):?>
                <p class="red catamaran p-20"><?= $errors['titre']?></p>
            <?php endif ?>
            <?php if(isset($errors['slugused'])):?>
                <p class="red catamaran p-20"><?= $errors['slugused']?></p>
            <?php endif ?>
            <input type="text" id="titre" name="titre" placeholder="Titre" value="<?= $_POST['titre']?>" class="catamaran noir p-20 edit-recette-input-titre edit-recette-input-titre-red js-edit-recette-input-titre">
        <?php elseif(!empty($errors)): ?>
            <p class="catamaran edit-recette-input-titre-label js-edit-recette-input-titre-label">Titre</p>
            <input type="text" id="titre" name="titre" placeholder="Titre" value="<?= $_POST['titre'] ?>" class="catamaran noir p-20 edit-recette-input-titre js-edit-recette-input-titre">
        <?php else: ?>
            <p class="catamaran edit-recette-input-titre-label js-edit-recette-input-titre-label">Titre</p>
            <input type="text" id="titre" name="titre" placeholder="Titre" value="<?= $recette['name'] ?>" class="catamaran noir p-20 edit-recette-input-titre js-edit-recette-input-titre">
        <?php endif?>
    </div>

    <div class="display-flex justify-content-center margin-top-50">
        <?php foreach($categories as $categorie): ?>
            <?php
                $src = '';
                if($categorie['name'] == 'Salade'){
                    $src = 'salade';
                }
                if($categorie['name'] == 'Végétarien'){
                    $src = 'vegetarien';
                }
                if($categorie['name'] == 'Au four'){
                    $src = 'four';
                }
                if($categorie['name'] == 'Rapide'){
                    $src = 'rapide';
                }
            ?>
            <div class="display-flex flex-direction-column align-items-center">
                <div class="page-edit-recette-categorie-img-div display-flex justify-content-center align-items-center margin-bottom-5">
                    <img src="/img/<?=$src?>.svg" alt="">
                </div>
                <p class="margin-leftright-30 catamaran p-20 noir cursor-default"><?= $categorie['name']?></p>
                <?php if (!empty($errors)): ?>
                    <div class="<?php if (in_array($categorie['id'], $_POST['categorie'])): ?>page-edit-recette-checkbox-categorie-selected<?php else: ?>page-edit-recette-checkbox-categorie<?php endif ?> cursor-pointer js-checkbox-categorie margin-top-10"></div>
                    <input type="checkbox" id="<?= $categorie['id']?>" name="categorie[]" value="<?= $categorie['id']?>" <?php if (in_array($categorie['id'], $_POST['categorie'])): ?> checked <?php endif ?> class="hidden js-checkbox-categorie-value">
                <?php else: ?>
                    <div class="<?php if (in_array($categorie['id'], $categoriesRecetteliste)): ?>page-edit-recette-checkbox-categorie-selected<?php else: ?>page-edit-recette-checkbox-categorie<?php endif ?> cursor-pointer js-checkbox-categorie margin-top-10"></div>
                    <input type="checkbox" id="<?= $categorie['id']?>" name="categorie[]" value="<?= $categorie['id']?>" <?php if (in_array($categorie['id'], $categoriesRecetteliste)): ?> checked <?php endif ?> class="hidden js-checkbox-categorie-value">
                <?php endif ?>
            </div>
        <?php endforeach ?>
    </div>

    <div class="margin-center width-542 margin-top-70 cursor-default">
        <?php if(isset($errors['duree'])):?>
            <p class="red catamaran p-20"><?= $errors['duree']?></p>
            <div class="display-flex align-items-center">
                <p class="catamaran p-20 bold red">Préparation:</p>
                <input type="text" name="duree" placeholder="..." value="<?= $_POST['duree'] ?>" class="edit-recette-duree-input edit-recette-duree-input-red catamaran noir p-20">
                <p class="catamaran p-20 noir">minutes</p>
            </div>
        <?php elseif(!empty($errors)) :?>
            <div class="display-flex align-items-center">
                <p class="catamaran p-20 bold noir">Préparation:</p>
                <input type="text" name="duree" placeholder="..." value="<?= $_POST['duree'] ?>" class="edit-recette-duree-input catamaran noir p-20">
                <p class="catamaran p-20 noir">minutes</p>
            </div>
        <?php else :?>
            <div class="display-flex align-items-center">
                <p class="catamaran p-20 bold noir">Préparation:</p>
                <input type="text" name="duree" placeholder="..." value="<?= $recette['duration'] ?>" class="edit-recette-duree-input catamaran noir p-20">
                <p class="catamaran p-20 noir">minutes</p>
            </div>
        <?php endif ?>
    </div>

    <?php if ((isset($errors['nbPersonneneg'])) || (isset($errors['nbPersonneint'])) || (isset($errors['quantiteint'])) || (isset($errors['ingredientnull'])) || (isset($errors['ingredientmatch'])) || (isset($errors['ingredientduplica'])) || (isset($errors['quantiteunitematch']))): ?>
        <div class="display-flex flex-direction-column margin-center width-598 margin-top-30 cursor-default">
            <?php if(isset($errors['nbPersonneneg'])): ?>
                <p class="catamaran p-20 red margin-left-160 width-500"><?=$errors['nbPersonneneg']?></p>
            <?php elseif(isset($errors['nbPersonneint'])):?>
                <p class="catamaran p-20 red margin-left-160 width-500"><?=$errors['nbPersonneint']?></p>
            <?php endif ?>
            <div class="display-flex align-items-center margin-left-28">
                <p class="catamaran p-20 bold red">Ingrédients:</p>
                <p class="catamaran p-20 noir margin-left-30">Pour</p>
                <input type="text" id="nbPersonne" name="nbPersonne" value="<?=$_POST['nbPersonne']?>" placeholder="..." class="edit-recette-personnes-input catamaran noir p-20 <?php if((isset($errors['nbPersonneneg'])) || (isset($errors['nbPersonneint']))):?> edit-recette-personnes-input-red <?php endif ?>">
                <p class="catamaran p-20 noir">personne(s)</p>
            </div>
            <div class="js-liste-ingredients margin-left-160 margin-top-10">

                <?php if(isset($errors['quantiteint'])): ?>
                    <p class="catamaran p-20 red width-500"><?=$errors['quantiteint']?></p>
                <?php endif ?>
                <?php if(isset($errors['quantiteunitematch'])):?>
                    <p class="catamaran p-20 red width-500"><?=$errors['quantiteunitematch']?></p>
                <?php endif ?>
                <?php if(isset($errors['ingredientnull'])): ?>
                    <p class="catamaran p-20 red width-500"><?=$errors['ingredientnull']?></p>
                <?php elseif(isset($errors['ingredientmatch'])):?>
                    <p class="catamaran p-20 red width-500"><?=$errors['ingredientmatch']?></p>
                <?php endif ?>
                <?php if(isset($errors['ingredientduplica'])):?>
                    <p class="catamaran p-20 red width-500"><?=$errors['ingredientduplica']?></p>
                <?php endif ?>

                <?php for($i=0; $i < count($_POST['ingredient']); $i++): ?>
                    <?php 
                        $ingredientName = "";
                        foreach($ingredients as $ingredient){
                            if($ingredient['id'] == $_POST['ingredient'][$i]) {
                                $ingredientName = $ingredient['name'];
                            }
                        }
                    ?>
                    <div class="display-flex align-items-center margin-top-10 js-ingredient-ligne">
                        <input type="text" value="<?= $_POST['ingredientQuantite'][$i] ?>" placeholder="Qt" name="ingredientQuantite[]" class="edit-recette-ingredient-quantite-input catamaran noir p-20 js-div-quantite <?php if(isset($errors['quantiteint']) || isset($errors['quantiteunitematch'])):?>edit-recette-ingredient-quantite-input-red<?php endif?>">
                        
                        <div class="position-relative js-div-unite">
                            <div class="edit-recette-ingredient-unite-select <?php if(isset($errors['quantiteunitematch'])):?>edit-recette-ingredient-unite-select-red<?php endif ?> js-edit-recette-ingredient-unite-select">
                                <p class="catamaran noir p-20 text-align-center js-edit-recette-ingredient-unite-select-p <?php if($_POST['ingredientUnite'][$i] == ''): ?>opacity-20<?php endif?>"><?php if($_POST['ingredientUnite'][$i] == ''):?>Ut<?php else:?><?= $_POST['ingredientUnite'][$i]?><?php endif?></p>
                                <div class="edit-recette-select-fleche opacity-20"></div>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-options hidden js-edit-recette-ingredient-unite-select-options">
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">cL</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">L</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">g</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">Kg</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">Aucune</p>
                                </div>
                            </div>
                            <select name="ingredientUnite[]" id="ingredientUnite[]" class="hidden">
                                <option value="<?php if($_POST['ingredientUnite'][$i]!== ''):?><?= $_POST['ingredientUnite'][$i]?><?php endif?>" class="js-edit-recette-ingredient-unite-select-option-value"></option>
                            </select>
                        </div>

                        <div class="position-relative js-div-ingredient">
                            <input type="text" placeholder="Ingrédients" <?php if($ingredientName !== ""): ?> value="<?= $ingredientName ?>"<?php endif?> class="js-input-recherche-ingredients edit-recette-ingredient-select p-20 noir catamaran <?php if(isset($errors['ingredientnull']) || isset($errors['ingredientmatch']) || isset($errors['ingredientduplica'])): ?>edit-recette-ingredient-select-red<?php endif?>">
                            <div class="hidden js-edit-recette-ingredient-select-id"><?=$_POST['ingredient'][$i]?></div>
                            <div class="js-input-recherche-ingredients-div-suggestions edit-recette-ingredient-select-options hidden"></div>
                            <select name="ingredient[]" class="hidden">
                                <option value="<?= $_POST['ingredient'][$i] ?>" class="js-edit-recette-ingredient-select-option-value"></option>
                            </select>
                        </div>

                        <div class="js-table-row-supp edit-recette-img-poubelle-poss-hover margin-left-10"></div>

                    </div>
                <?php endfor ?>
            </div>

            <div class="display-flex margin-left-160 margin-top-15">
                <div class="choix-recette-bouton-moins-poss-hover js-bouton-ingredients-moins margin-right-15"></div>
                <div class="choix-recette-bouton-plus js-bouton-ingredients-plus cursor-pointer"></div>
            </div>

        </div>

    <?php elseif(!empty($errors)): ?>
        <div class="display-flex flex-direction-column margin-center width-598 margin-top-30 cursor-default">
            <div class="display-flex align-items-center margin-left-28">
                <p class="catamaran p-20 bold noir">Ingrédients:</p>
                <p class="catamaran p-20 noir margin-left-30">Pour</p>
                <input type="text" id="nbPersonne" name="nbPersonne" value="<?=$_POST['nbPersonne']?>" placeholder="..." class="edit-recette-personnes-input catamaran noir p-20">
                <p class="catamaran p-20 noir">personne(s)</p>
            </div>
            <div class="js-liste-ingredients margin-left-160 margin-top-10">
                <?php for($i=0; $i< count($_POST['ingredient']); $i++): ?>
                    <?php 
                        $ingredientName = "";
                        foreach($ingredients as $ingredient) {
                            if($ingredient['id'] == $_POST['ingredient'][$i]) {
                                $ingredientName = $ingredient['name'];
                            }
                        }
                    ?>
                    <div class="display-flex align-items-center margin-top-10 js-ingredient-ligne">
                        <input type="text" value="<?= $_POST['ingredientQuantite'][$i] ?>" name="ingredientQuantite[]" placeholder="Qt" class="edit-recette-ingredient-quantite-input catamaran noir p-20 js-div-quantite">
                        
                        <div class="position-relative js-div-unite">
                            <div class="edit-recette-ingredient-unite-select js-edit-recette-ingredient-unite-select">
                                <p class="catamaran noir p-20 text-align-center js-edit-recette-ingredient-unite-select-p <?php if($_POST['ingredientUnite'][$i] == ''): ?>opacity-20<?php endif?>"><?php if($_POST['ingredientUnite'][$i] == ''):?>Ut<?php else:?><?= $_POST['ingredientUnite'][$i]?><?php endif?></p>
                                <div class="edit-recette-select-fleche opacity-20"></div>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-options hidden js-edit-recette-ingredient-unite-select-options">
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">cL</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">L</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">g</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">Kg</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">Aucune</p>
                                </div>
                            </div>
                            <select name="ingredientUnite[]" id="ingredientUnite[]" class="hidden">
                                <option value="<?php if($_POST['ingredientUnite'][$i]!== ''):?><?= $_POST['ingredientUnite'][$i]?><?php endif?>" class="js-edit-recette-ingredient-unite-select-option-value"></option>
                            </select>
                        </div>

                        <div class="position-relative js-div-ingredient">
                            <input type="text" placeholder="Ingrédients" value="<?= $ingredientName ?>" class="js-input-recherche-ingredients edit-recette-ingredient-select p-20 noir catamaran">
                            <div class="hidden js-edit-recette-ingredient-select-id"><?=$_POST['ingredient'][$i]?></div>
                            <div class="js-input-recherche-ingredients-div-suggestions edit-recette-ingredient-select-options hidden"></div>
                            <select name="ingredient[]" class="hidden">
                                <option value="<?= $_POST['ingredient'][$i] ?>" class="js-edit-recette-ingredient-select-option-value"></option>
                            </select>
                        </div>

                        <div class="js-table-row-supp cursor-pointer edit-recette-img-poubelle-poss-hover margin-left-10"></div>

                    </div>
                <?php endfor ?>
            </div>

            <div class="display-flex margin-left-160 margin-top-15">
                <div class="choix-recette-bouton-moins-poss-hover js-bouton-ingredients-moins margin-right-15 cursor-pointer"></div>
                <div class="choix-recette-bouton-plus js-bouton-ingredients-plus cursor-pointer"></div>
            </div>

        </div>
    <?php else: ?>
        <div class="display-flex flex-direction-column margin-center width-598 margin-top-30 cursor-default">
            <div class="display-flex align-items-center margin-left-28">
                <p class="catamaran p-20 bold noir">Ingrédients:</p>
                <p class="catamaran p-20 noir margin-left-30">Pour</p>
                <input type="text" id="nbPersonne" name="nbPersonne" value="4" placeholder="..." class="edit-recette-personnes-input catamaran noir p-20">
                <p class="catamaran p-20 noir">personne(s)</p>
            </div>

            <div class="js-liste-ingredients margin-left-160 margin-top-10">
                <?php foreach($ingredientsRecette as $ingredientRecette): ?>
                    <?php 
                        $quantite = roundUpToHalf($ingredientRecette['quantite'] * 4);
                        $unite = $ingredientRecette['unite'];
                        if($quantite == 0) {
                            $quantite = "";
                        }
                        if($quantite > 1000 && $ingredientRecette['unite']=='g') {
                            $quantite = $quantite/1000;
                            $unite = 'Kg';
                        }
                        if($quantite > 100 && $ingredientRecette['unite']=='cL') {
                            $quantite = $quantite/100;
                            $unite = 'L';
                        }
                    ?>
                    <div class="display-flex align-items-center margin-top-10 js-ingredient-ligne">
                        <input type="text" value="<?= $quantite ?>" name="ingredientQuantite[]" placeholder="Qt" class="edit-recette-ingredient-quantite-input catamaran noir p-20 js-div-quantite">
                        
                        <div class="position-relative js-div-unite">
                            <div class="edit-recette-ingredient-unite-select js-edit-recette-ingredient-unite-select">
                                <p class="catamaran noir p-20 text-align-center js-edit-recette-ingredient-unite-select-p <?php if($unite == ''): ?>opacity-20<?php endif?>"><?php if($unite == ''):?>Ut<?php else:?><?= $unite?><?php endif?></p>
                                <div class="edit-recette-select-fleche opacity-20"></div>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-options hidden js-edit-recette-ingredient-unite-select-options">
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">cL</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">L</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">g</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">Kg</p>
                                </div>
                                <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                    <p class="catamaran noir p-20">Aucune</p>
                                </div>
                            </div>
                            <select name="ingredientUnite[]" id="ingredientUnite[]" class="hidden">
                                <option value="<?php if($unite !== ''):?><?= $unite ?><?php endif?>" class="js-edit-recette-ingredient-unite-select-option-value"></option>
                            </select>
                        </div>

                        <div class="position-relative js-div-ingredient">
                            <input type="text" placeholder="Ingrédients" value="<?=$ingredientRecette['name']?>" class="js-input-recherche-ingredients edit-recette-ingredient-select p-20 noir catamaran">
                            <div class="hidden js-edit-recette-ingredient-select-id"><?=$ingredientRecette['id']?></div>
                            <div class="js-input-recherche-ingredients-div-suggestions edit-recette-ingredient-select-options hidden"></div>
                            <select name="ingredient[]" class="hidden">
                                <option value="<?= $ingredientRecette['id'] ?>" class="js-edit-recette-ingredient-select-option-value"></option>
                            </select>
                        </div>

                        <div class="js-table-row-supp edit-recette-img-poubelle-poss-hover margin-left-10"></div>

                    </div>
                <?php endforeach ?>
            </div>

            <div class="display-flex margin-left-160 margin-top-15">
                <div class="choix-recette-bouton-moins-poss-hover js-bouton-ingredients-moins margin-right-15"></div>
                <div class="choix-recette-bouton-plus js-bouton-ingredients-plus cursor-pointer"></div>
            </div>

        </div>
    <?php endif ?>

    <div class="background-color-beige margin-top-70 page-recette-div-recette-contenu cursor-default">
        <h4 class="catamaran p-30 bold noir text-align-center">RECETTE</h4>
        <div class="margin-top-40">
            <?php if(isset($errors['content'])):?>
                <p class="catamaran p-20 red"><?= $errors['content'] ?></p>
                <textarea name="content" id="content" placeholder="Contenu de la recette" class="margin-top-10 catamaran p-20 noir edit-recette-contenu-div edit-recette-contenu-div-red js-edit-recette-contenu-div"><?= htmlspecialchars($_POST['content']) ?></textarea>
            <?php elseif(!empty($errors)): ?>
                <textarea name="content" id="content" placeholder="Contenu de la recette" class="margin-top-10 catamaran p-20 noir edit-recette-contenu-div js-edit-recette-contenu-div"><?= htmlspecialchars($_POST['content']) ?></textarea>
            <?php else: ?>
                <textarea name="content" id="content" placeholder="Contenu de la recette" class="catamaran p-20 noir edit-recette-contenu-div js-edit-recette-contenu-div"><?= htmlspecialchars(strip_tags($recette['content']), ENT_QUOTES) ?></textarea>
            <?php endif ?>
        </div>
    </div>

    <div class="display-flex justify-content-center">
        <button type="submit" class="bouton-valider margin-top-70">Valider</button>
    </div>

</form>

<div class="display-flex justify-content-center">
    <?php $newRequestURI = str_replace('/edit', '', $_SERVER['REQUEST_URI']) ?>
    <a href="<?=$newRequestURI?>" class="catamaran p-20 orange">
        <button class="bouton-secondaire margin-top-20">Annuler</button>
    </a>
</div>


<script>
    /// Titre de la recette : change au focus
        const inputTitre = document.querySelector('.js-edit-recette-input-titre');
        const inputTitreLabel = document.querySelector('.js-edit-recette-input-titre-label');

        inputTitre.addEventListener('focus', ()=>{
            inputTitreLabel.classList.add('edit-recette-input-titre-label-orange');
        })

        inputTitre.addEventListener('blur', ()=>{
            inputTitreLabel.classList.remove('edit-recette-input-titre-label-orange');
        })

    /// Type de la recette : permet de sélectionner ou désélectionner un type
        const inputTypecatValue = Array.from(document.querySelectorAll('.js-edit-recette-input-typecat-value'));
        const inputTypecatP = Array.from(document.querySelectorAll('.js-edit-recette-input-typecat-p'));

        inputTypecatP.forEach((p)=>{
            p.addEventListener('click', ()=> {
            if (p.classList.contains('noir')) {
                p.classList.remove('noir');
                p.classList.add('orange');
                p.classList.add('bold');
                p.nextElementSibling.checked = true;
            } else if(p.classList.contains('orange')) {
                p.classList.remove('orange');
                p.classList.remove('bold');
                p.classList.add('noir');
                p.nextElementSibling.checked = false;
            }
        })
        })

    /// Catégorie de la recette : permet de sélectionner ou désélectionner une categorie
        const checkboxCategories = Array.from(document.querySelectorAll('.js-checkbox-categorie'));

        checkboxCategories.forEach((checkbox)=>{
            checkbox.addEventListener('click', ()=> {
                if(checkbox.classList.contains('page-edit-recette-checkbox-categorie-selected')) {
                    checkbox.classList.remove('page-edit-recette-checkbox-categorie-selected');
                    checkbox.classList.add('page-edit-recette-checkbox-categorie');
                    checkbox.nextElementSibling.checked = false;
                } else if(checkbox.classList.contains('page-edit-recette-checkbox-categorie')) {
                    checkbox.classList.remove('page-edit-recette-checkbox-categorie');
                    checkbox.classList.add('page-edit-recette-checkbox-categorie-selected');
                    checkbox.nextElementSibling.checked = true;
                }
            })
            
        })

    
    /// Unité d'un ingrédient : 
        // Permet d'ouvrir et de fermer le select : 
            let ingredientUniteSelect = Array.from(document.querySelectorAll('.js-edit-recette-ingredient-unite-select'));
            ingredientUniteSelect.forEach((select)=>{
                select.addEventListener('click', ()=>{
                    if(select.classList.contains('edit-recette-ingredient-unite-select-ouvert')) {
                        select.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                        select.nextElementSibling.classList.add('hidden');
                    } else {
                        Array.from(document.querySelectorAll('.js-edit-recette-ingredient-unite-select')).forEach((selectTous)=>{
                            selectTous.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                            selectTous.nextElementSibling.classList.add('hidden');
                        })
                        select.classList.add('edit-recette-ingredient-unite-select-ouvert')
                        select.nextElementSibling.classList.remove('hidden');
                    }
                })
            })

        // Permet de selectionner une unité et de mettre à jour la valeur : 
            const ingredientUniteSelectOptionItem = Array.from(document.querySelectorAll('.js-edit-recette-ingredient-unite-select-option-item'));
            const ingredientUniteSelectValue = Array.from(document.querySelectorAll('.js-edit-recette-ingredient-unite-select-option-value'));

            ingredientUniteSelectOptionItem.forEach((item)=>{
                item.addEventListener('click', ()=>{
                    let divIngredient = item.closest('.position-relative')
                    let ingredientSelect = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select');
                    let ingredientSelectP = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select-p');
                    let ingredientSelectOptions = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select-options');
                    let ingredientSelectValue = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select-option-value');

                    ingredientSelect.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                    ingredientSelectP.classList.remove('opacity-20');
                    if(item.querySelector('p').innerHTML !== 'Aucune') {
                        ingredientSelectP.innerHTML = item.querySelector('p').innerHTML;
                    } else {
                        ingredientSelectP.innerHTML = '';
                    }
                    ingredientSelectOptions.classList.add('hidden');
                    ingredientSelectValue.value = ingredientSelectP.innerHTML;
                })
            })

    /// Choix d'un ingrédient :
        const inputRechercheIngredients = Array.from(document.querySelectorAll('.js-input-recherche-ingredients')); 
        const inputRechercheIngredientsId = Array.from(document.querySelectorAll('.js-edit-recette-ingredient-select-id'));
        const divSuggestions = Array.from(document.querySelectorAll('.js-input-recherche-ingredients-div-suggestions'));
        let ingredientName = '';
        let inputValue = '';
        let listeIngredientRecherche = [];
        console.log(inputRechercheIngredients)

        for(let i=0; i<inputRechercheIngredients.length; i++) {
            inputRechercheIngredients[i].addEventListener('input', ()=>{
                listeIngredientRecherche = [];
                <?php foreach($ingredients as $ingredient): ?>
                    ingredientName = ("<?=$ingredient['name']?>".normalize('NFD').replace(/[\u0300-\u036f]/g, "")).replace(/[^a-zA-Z0-9]/g, '');
                    ingredientName = ingredientName.toLowerCase();
                    inputValue = (inputRechercheIngredients[i].value).normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9]/g, '');
                    inputValue = inputValue.toLowerCase();

                    if(ingredientName.includes(inputValue) && inputValue !== '') {
                        listeIngredientRecherche.push({name: "<?=$ingredient['name']?>", id: "<?=$ingredient['id']?>"});
                    }
                <?php endforeach ?>
                
                divSuggestions[i].innerHTML = listeIngredientRecherche.map((ingredient)=>{
                    return `
                        <div class="edit-recette-ingredient-select-option-item js-edit-recette-ingredient-select-option-item">
                            <p class="catamaran noir p-20 js-edit-recette-ingredient-select-option-item-p">${ingredient['name']}</p>
                            <div class="hidden js-edit-recette-ingredient-select-option-item-id">${ingredient['id']}</div>
                        </div>
                    `;
                }).join('');

                if(divSuggestions[i].children.length >= 1) {
                    inputRechercheIngredients[i].classList.add('edit-recette-ingredient-select-ouvert');
                    divSuggestions[i].classList.remove('hidden');
                } else {
                    inputRechercheIngredients[i].classList.remove('edit-recette-ingredient-select-ouvert');
                    divSuggestions[i].classList.add('hidden');
                }

                if(inputRechercheIngredients[i].value == "") {
                    let div = divSuggestions[i].closest('.js-div-ingredient');
                    div.querySelector('.js-edit-recette-ingredient-select-option-value').value = "";
                }

                divSuggestions[i].querySelectorAll('.js-edit-recette-ingredient-select-option-item').forEach((option)=>{
                    option.addEventListener('click', ()=>{
                        inputRechercheIngredients[i].classList.remove('edit-recette-ingredient-select-ouvert');
                        divSuggestions[i].classList.add('hidden');
                        inputRechercheIngredients[i].value = option.querySelector('.js-edit-recette-ingredient-select-option-item-p').innerHTML;
                        inputRechercheIngredientsId[i].innerHTML = option.querySelector('.js-edit-recette-ingredient-select-option-item-id').innerHTML;
                        let div = divSuggestions[i].closest('.js-div-ingredient');
                        div.querySelector('.js-edit-recette-ingredient-select-option-value').value = inputRechercheIngredientsId[i].innerHTML;
                    })
                })
                
            })

        }

    /// Mise à jour de l'état des boutons moins et poubelles
        function MettreAJourBoutons () {
            const divListeIngredients = document.querySelector('.js-liste-ingredients');
            const boutonMoins = document.querySelector('.js-bouton-ingredients-moins');
            const ligneIngredients = Array.from(divListeIngredients.querySelectorAll('.js-ingredient-ligne'));
            if (ligneIngredients.length === 1) {
                boutonMoins.classList.add('choix-recette-bouton-moins');
                const boutonPoubelle = document.querySelector('.js-table-row-supp');
                boutonPoubelle.classList.add('edit-recette-img-poubelle');
            } else {
                if(boutonMoins.classList.contains('choix-recette-bouton-moins')) {
                    boutonMoins.classList.remove('choix-recette-bouton-moins');
                }

                const boutonsPoubelleTous = Array.from(document.querySelectorAll('.js-table-row-supp'));
                boutonsPoubelleTous.forEach((poubelle)=>{
                    if (poubelle.classList.contains('edit-recette-img-poubelle')){
                        poubelle.classList.remove('edit-recette-img-poubelle');
                    }
                })

            }
        }

        MettreAJourBoutons();

    /// Suppression de la dernière ligne avec le bouton Moins
        const boutonMoins = document.querySelector('.js-bouton-ingredients-moins');
        boutonMoins.addEventListener('click', ()=>{
            const divListeIngredients = document.querySelector('.js-liste-ingredients');
            const ligneIngredients = Array.from(divListeIngredients.querySelectorAll('.js-ingredient-ligne'));
            if (ligneIngredients.length > 1) {
                divListeIngredients.lastElementChild.remove();
                MettreAJourBoutons();
            }
        })

    /// Supression de la ligne souhaitée avec le bouton poubelle
        const boutonsPoubelleTous = Array.from(document.querySelectorAll('.js-table-row-supp'));
        boutonsPoubelleTous.forEach((poubelle)=>{
            poubelle.addEventListener('click', ()=>{
                const divListeIngredients = document.querySelector('.js-liste-ingredients');
                const ligneIngredients = Array.from(divListeIngredients.querySelectorAll('.js-ingredient-ligne'));
                if (ligneIngredients.length > 1) {
                    const ligne = poubelle.parentElement.remove();
                    MettreAJourBoutons();
                }
            })
        })

    /// Ajout d'une nouvelle ligne avec le bouton plus
        const boutonPlus = document.querySelector('.js-bouton-ingredients-plus');
        boutonPlus.addEventListener('click', ()=>{
            // J'ajoute la nouvelle ligne
                let newdiv = document.createElement('div');
                newdiv.classList.add('display-flex');
                newdiv.classList.add('align-items-center');
                newdiv.classList.add('margin-top-10');
                newdiv.classList.add('js-ingredient-ligne');
                newdiv.innerHTML = `
                    <input type="text" value="" name="ingredientQuantite[]" placeholder="Qt" class="edit-recette-ingredient-quantite-input catamaran noir p-20 js-div-quantite">
                    
                    <div class="position-relative js-div-unite">
                        <div class="edit-recette-ingredient-unite-select js-edit-recette-ingredient-unite-select">
                            <p class="catamaran noir p-20 text-align-center js-edit-recette-ingredient-unite-select-p opacity-20">Ut</p>
                            <div class="edit-recette-select-fleche opacity-20"></div>
                        </div>
                        <div class="edit-recette-ingredient-unite-select-options hidden js-edit-recette-ingredient-unite-select-options">
                            <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                <p class="catamaran noir p-20">cL</p>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                <p class="catamaran noir p-20">L</p>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                <p class="catamaran noir p-20">g</p>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                <p class="catamaran noir p-20">Kg</p>
                            </div>
                            <div class="edit-recette-ingredient-unite-select-option-item js-edit-recette-ingredient-unite-select-option-item">
                                <p class="catamaran noir p-20">Aucune</p>
                            </div>
                        </div>
                        <select name="ingredientUnite[]" id="ingredientUnite[]" class="hidden">
                            <option value="" class="js-edit-recette-ingredient-unite-select-option-value"></option>
                        </select>
                    </div>

                    <div class="position-relative js-div-ingredient">
                        <input type="text" placeholder="Ingrédients" class="js-input-recherche-ingredients edit-recette-ingredient-select p-20 noir catamaran">
                        <div class="hidden js-edit-recette-ingredient-select-id"></div>
                        <div class="js-input-recherche-ingredients-div-suggestions edit-recette-ingredient-select-options hidden"></div>
                        <select name="ingredient[]" class="hidden">
                            <option value="" class="js-edit-recette-ingredient-select-option-value"></option>
                        </select>
                    </div>

                    <div class="js-table-row-supp edit-recette-img-poubelle-poss-hover margin-left-10"></div>

                `;
                const divListeIngredients = document.querySelector('.js-liste-ingredients');
                divListeIngredients.appendChild(newdiv);
            
            // Je permets le bon fonctionnement de tous les éléments de cette ligne
                MettreAJourBoutons();

                // Bouton Poubelle
                    const boutonPoubelle = newdiv.querySelector('.js-table-row-supp');
                    boutonPoubelle.addEventListener('click', ()=>{
                        const divListeIngredients = document.querySelector('.js-liste-ingredients');
                        const ligneIngredients = Array.from(divListeIngredients.querySelectorAll('.js-ingredient-ligne'));
                        if (ligneIngredients.length > 1){
                            const ligne = boutonPoubelle.parentElement.remove();
                            MettreAJourBoutons();
                        }
                    })

                // Select Unité
                    ingredientUniteSelect = newdiv.querySelector('.js-edit-recette-ingredient-unite-select');
                    ingredientUniteSelect.addEventListener('click', ()=>{
                        if(ingredientUniteSelect.classList.contains('edit-recette-ingredient-unite-select-ouvert')) {
                            ingredientUniteSelect.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                            ingredientUniteSelect.nextElementSibling.classList.add('hidden');
                        } else {
                            Array.from(document.querySelectorAll('.js-edit-recette-ingredient-unite-select')).forEach((selectTous)=>{
                                selectTous.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                                selectTous.nextElementSibling.classList.add('hidden');
                            })
                            ingredientUniteSelect.classList.add('edit-recette-ingredient-unite-select-ouvert')
                            ingredientUniteSelect.nextElementSibling.classList.remove('hidden');
                        }
                    })
                    const ingredientUniteSelectOptionItem = Array.from(newdiv.querySelectorAll('.js-edit-recette-ingredient-unite-select-option-item'));
                    ingredientUniteSelectOptionItem.forEach((item)=>{
                        item.addEventListener('click', ()=>{
                            let divIngredient = item.closest('.position-relative')
                            let ingredientSelect = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select');
                            let ingredientSelectP = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select-p');
                            let ingredientSelectOptions = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select-options');
                            let ingredientSelectValue = divIngredient.querySelector('.js-edit-recette-ingredient-unite-select-option-value');

                            ingredientSelect.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                            ingredientSelectP.classList.remove('opacity-20');
                            if(item.querySelector('p').innerHTML !== 'Aucune') {
                                ingredientSelectP.innerHTML = item.querySelector('p').innerHTML;
                            } else {
                                ingredientSelectP.innerHTML = '';
                            }
                            ingredientSelectOptions.classList.add('hidden');
                            ingredientSelectValue.value = ingredientSelectP.innerHTML;
                        })
                    })
                
                // Select Ingrédient
                    const inputRechercheIngredients = Array.from(document.querySelectorAll('.js-input-recherche-ingredients')); 
                    const inputRechercheIngredientsId = Array.from(document.querySelectorAll('.js-edit-recette-ingredient-select-id'));
                    const divSuggestions = Array.from(document.querySelectorAll('.js-input-recherche-ingredients-div-suggestions'));
                    let ingredientName = '';
                    let inputValue = '';
                    let listeIngredientRecherche = [];
                    console.log(inputRechercheIngredients)

                    for(let i=0; i<inputRechercheIngredients.length; i++) {
                        inputRechercheIngredients[i].addEventListener('input', ()=>{
                            listeIngredientRecherche = [];
                            <?php foreach($ingredients as $ingredient): ?>
                                ingredientName = ("<?=$ingredient['name']?>".normalize('NFD').replace(/[\u0300-\u036f]/g, "")).replace(/[^a-zA-Z0-9]/g, '');
                                ingredientName = ingredientName.toLowerCase();
                                inputValue = (inputRechercheIngredients[i].value).normalize('NFD').replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9]/g, '');
                                inputValue = inputValue.toLowerCase();

                                if(ingredientName.includes(inputValue) && inputValue !== '') {
                                    listeIngredientRecherche.push({name: "<?=$ingredient['name']?>", id: "<?=$ingredient['id']?>"});
                                }
                            <?php endforeach ?>
                            
                            divSuggestions[i].innerHTML = listeIngredientRecherche.map((ingredient)=>{
                                return `
                                    <div class="edit-recette-ingredient-select-option-item js-edit-recette-ingredient-select-option-item">
                                        <p class="catamaran noir p-20 js-edit-recette-ingredient-select-option-item-p">${ingredient['name']}</p>
                                        <div class="hidden js-edit-recette-ingredient-select-option-item-id">${ingredient['id']}</div>
                                    </div>
                                `;
                            }).join('');

                            if(divSuggestions[i].children.length >= 1) {
                                inputRechercheIngredients[i].classList.add('edit-recette-ingredient-select-ouvert');
                                divSuggestions[i].classList.remove('hidden');
                            } else {
                                inputRechercheIngredients[i].classList.remove('edit-recette-ingredient-select-ouvert');
                                divSuggestions[i].classList.add('hidden');
                            }

                            if(inputRechercheIngredients[i].value == "") {
                                let div = divSuggestions[i].closest('.js-div-ingredient');
                                div.querySelector('.js-edit-recette-ingredient-select-option-value').value = "";
                            }

                            divSuggestions[i].querySelectorAll('.js-edit-recette-ingredient-select-option-item').forEach((option)=>{
                                option.addEventListener('click', ()=>{
                                    inputRechercheIngredients[i].classList.remove('edit-recette-ingredient-select-ouvert');
                                    divSuggestions[i].classList.add('hidden');
                                    inputRechercheIngredients[i].value = option.querySelector('.js-edit-recette-ingredient-select-option-item-p').innerHTML;
                                    inputRechercheIngredientsId[i].innerHTML = option.querySelector('.js-edit-recette-ingredient-select-option-item-id').innerHTML;
                                    let div = divSuggestions[i].closest('.js-div-ingredient');
                                    div.querySelector('.js-edit-recette-ingredient-select-option-value').value = inputRechercheIngredientsId[i].innerHTML;
                                })
                            })
                            
                        })
                    }
                })

    /// Modification de la taille du contenu de la recette :
        const recetteTextArea = document.querySelector('.edit-recette-contenu-div');

        // La taille du textarea s'ajuste en fonction du contenu
        function resizeRecetteTextArea () {
            recetteTextArea.style.height = 'auto';
            recetteTextArea.style.height = recetteTextArea.scrollHeight + 'px';
        }
        resizeRecetteTextArea();
        recetteTextArea.addEventListener('input', resizeRecetteTextArea);
    
    /// Lorsque je clique n'importe où le select Unité se referme
        document.addEventListener('click', (event)=>{
            
            let element = event.target;
            
            // Lorsque je clique n'importe où le select Unité se referme
            let elementIsSelectUnite = false;
            while (element){
                if (element.classList.contains('js-div-unite')) {
                    elementIsSelectUnite = true;
                    break;
                } else {
                    element = element.parentElement;
                }
            }
            if (elementIsSelectUnite == false) {
                Array.from(document.querySelectorAll('.js-edit-recette-ingredient-unite-select')).forEach((selectTous)=>{
                    selectTous.classList.remove('edit-recette-ingredient-unite-select-ouvert');
                    selectTous.nextElementSibling.classList.add('hidden');
                })
            }

            // Lorsque je clique n'importe où le select Ingredients se referme
            element = event.target;
            let elementIsSelectIngredient = false;
            while (element){
                if (element.classList.contains('js-div-ingredient')) {
                    elementIsSelectIngredient = true;
                    break;
                } else {
                    element = element.parentElement;
                }
            }
            if (elementIsSelectIngredient == false) {
                Array.from(document.querySelectorAll('.js-edit-recette-ingredient-select')).forEach((selectTous)=>{
                    selectTous.classList.remove('edit-recette-ingredient-select-ouvert');
                    selectTous.nextElementSibling.classList.add('hidden');
                })
            }
            
        })

</script>