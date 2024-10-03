<?php

use App\ConnectionBD;
use App\Filtres;

$title = 'Mon livre de recettes';
$selected = 'recettes';

$pdo = ConnectionBD::getPDO();

// Recuperation des données de toutes les recettes
$requete = $pdo->query('SELECT * FROM recette ORDER BY name ASC');
$recettes = $requete->fetchAll();

// Recuperation des données de toutes les typeCats (entrées, plats, desserts)
$requete = $pdo->query('SELECT * FROM typeCat');
$typeCats = $requete->fetchAll();

// Recuperation des données de toutes les catégories (végé, au four, ...)
$requete = $pdo->query('SELECT * FROM categorie');
$categories = $requete->fetchAll();

// Recuperation des données de tous les ingredients
$requete = $pdo->query('SELECT * FROM ingredient ORDER BY name ASC');
$ingredients = $requete->fetchAll();

$errors = [];

// Je supprime les filtres qui ont demandés à être supprimés
$filtres = new Filtres;
$filtres->deleteFiltreArray('categorie');
$filtres->deleteFiltreArray('ingredient');
$filtres->deleteFiltreSimple('durationmin');
$filtres->deleteFiltreSimple('durationmax');
$filtres->deleteFiltreDurationminmax();
?>

<?php if (isset($_GET['sup']) && $_GET['sup'] == 1): ?>
    <div class="page-recette-alerte-succes page-recette-alerte-page-recette cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">La recette a bien été suprimée</h2>
    </div>
<?php endif ?>

<?php // Lorsque j'ajoute ou supprime une recette, je suis redirigé vers cette page avec un message de succès ?>
<?php if (isset($_GET['modif']) && $_GET['modif'] == 1): ?>
    <div class="page-recette-alerte-succes page-recette-alerte-page-recette cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">La recette a bien été créée</h2>
    </div>
<?php endif ?>

<?php // Lorsque j'ajoute une recette à la liste de courses, je suis redirigé vers cette page avec un message de succès ?>
<?php if (isset($_GET['ajout']) && $_GET['ajout'] == 1): ?>
    <div class="page-recette-alerte-succes page-recette-alerte-page-recette cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">La recette a bien été ajoutée à la liste</h2>
    </div>
<?php endif ?>

<div class="display-flex justify-content-center margin-top-70 cursor-default">
    <div class="display-flex flex-direction-column">
        <h1 class="fredoka p-25 semi-bold noir">Mon livre de recettes</h1>
        <div class="courses-menu-underline"></div>
    </div>
</div>

<div class="display-flex page-livre-recette-div-flex-guide-filtres">
    <div class="display-flex align-items-center page-livre-recettes-div-affichage-filtres-selcs">
        <?php // Si des categories sont passées en filtres, elles apparaissent ici ?>
        <?php $filtresSel = false ?>
        <?php if(isset($_GET['categories']) && $_GET['categories'] !== ""): ?>
            <?php 
                $categoriesNames = [];
                $filtresSel = true;
            ?>
            <?php foreach($_GET['categories'] as $categorie) {
                $requete = $pdo->prepare('SELECT name, id FROM categorie WHERE id= :id');
                $requete->execute(['id'=> $categorie]);
                $categoriesNames[] = $requete->fetch();
            } ?>
            <?php foreach($categoriesNames as $categorie): ?>
                <div class="display-flex align-items-center justify-content-center page-livre-recette-div-filtres-selectionne">
                    <p class="catamaran p-20 noir opacity-20 margin-left-15 cursor-default"><?= $categorie['name'] ?></p>
                    <?php // Je crée le lien pour supprimer le filtre
                        $link = $_SERVER['REQUEST_URI'] . "&supfiltre=categorie{$categorie['id']}" 
                    ?>
                    <a href="<?= $link ?>" class="page-livre-recette-div-filtres-selectionne-croix"></a>
                </div>
            <?php endforeach ?>
        <?php endif ?>

        <?php // Si des ingrédients sont passés en filtres, ils apparaissent ici ?>
        <?php if(isset($_GET['ingredients']) && $_GET['ingredients'] !== ""): ?>
            <?php 
                $ingredientsNames = [];
                $filtresSel = true;
            ?>
            <?php foreach($_GET['ingredients'] as $ingredient) {
                $requete = $pdo->prepare('SELECT name, id FROM ingredient WHERE id= :id');
                $requete->execute(['id'=> $ingredient]);
                $ingredientsNames[] = $requete->fetch();
            } ?>
            <?php foreach($ingredientsNames as $ingredient): ?>
                <div class="display-flex align-items-center justify-content-center page-livre-recette-div-filtres-selectionne">
                    <p class="catamaran p-20 noir opacity-20 margin-left-15 cursor-default"><?= ucfirst($ingredient['name']) ?></p>
                    <?php // Je crée le lien pour supprimer le filtre
                        $link = $_SERVER['REQUEST_URI'] . "&supfiltre=ingredient{$ingredient['id']}" 
                    ?>
                    <a href="<?= $link ?>" class="page-livre-recette-div-filtres-selectionne-croix"></a>
                </div>
            <?php endforeach ?>
        <?php endif ?>

        <?php // Si des durées sont passées en filtres, elles apparaissent ici ?>
        <?php if(isset($_GET['durationmin']) && isset($_GET['durationmin'])): ?>
            <?php if ($_GET['durationmin'] !== "" && $_GET['durationmax'] !== ""): ?>
                <?php if($_GET['durationmin'] == $_GET['durationmax']): ?>
                    <?php $filtresSel = true ?>
                    <div class="display-flex align-items-center justify-content-center page-livre-recette-div-filtres-selectionne">
                        <p class="catamaran p-20 noir opacity-20 margin-left-15 cursor-default"><?=$_GET['durationmin']?>min</p>
                        <?php // Je crée le lien pour supprimer le filtre
                            $link = $_SERVER['REQUEST_URI'] . "&supfiltre=durationminmax" 
                        ?>
                        <a href="<?= $link ?>" class="page-livre-recette-div-filtres-selectionne-croix"></a>
                    </div>
                <?php else: ?>
                    <?php $filtresSel = true ?>
                    <div class="display-flex align-items-center justify-content-center page-livre-recette-div-filtres-selectionne">
                        <p class="catamaran p-20 noir opacity-20 margin-left-15 cursor-default"><?=$_GET['durationmin']?>min - <?=$_GET['durationmax']?>min</p>
                        <?php // Je crée le lien pour supprimer le filtre
                            $link = $_SERVER['REQUEST_URI'] . "&supfiltre=durationminmax" 
                        ?>
                        <a href="<?= $link ?>" class="page-livre-recette-div-filtres-selectionne-croix"></a>
                    </div>
                <?php endif?>
            <?php endif ?>
            <?php if ($_GET['durationmin'] !== "" && $_GET['durationmax'] === ""): ?>
                <?php $filtresSel = true ?>
                <div class="display-flex align-items-center justify-content-center page-livre-recette-div-filtres-selectionne">
                    <p class="catamaran p-20 noir opacity-20 margin-left-15 cursor-default">Plus de <?=$_GET['durationmin']?> min</p>
                    <?php // Je crée le lien pour supprimer le filtre
                        $link = $_SERVER['REQUEST_URI'] . "&supfiltre=durationmin" 
                    ?>
                    <a href="<?= $link ?>" class="page-livre-recette-div-filtres-selectionne-croix"></a>
                </div>
            <?php endif ?>
            <?php if ($_GET['durationmin'] === "" && $_GET['durationmax'] !== ""): ?>
                <?php $filtresSel = true ?>
                <div class="display-flex align-items-center justify-content-center page-livre-recette-div-filtres-selectionne">
                    <p class="catamaran p-20 noir opacity-20 margin-left-15 cursor-default">Moins de <?=$_GET['durationmax']?> min</p>
                    <?php // Je crée le lien pour supprimer le filtre
                        $link = $_SERVER['REQUEST_URI'] . "&supfiltre=durationmax" 
                    ?>
                    <a href="<?= $link ?>" class="page-livre-recette-div-filtres-selectionne-croix"></a>
                </div>
            <?php endif ?>
        <?php endif ?>

        <?php if ($filtresSel) : ?>
            <a href="<?= $this->router->generate('livreRecettes')?>" class="display-flex justify-content-center align-items-center page-livre-recette-reinitialiser-filtres">
                <p class="catamaran p-20 noir-opacity-20 hover-orange">Réinitialiser les filtres</p>
            </a>
        <?php endif ?>
    </div>

    <div class="display-flex align-items-center margin-left-100">
        <div class="display-flex justify-content-center align-items-center page-livre-recette-div-bouton-filtres cursor-pointer js-page-livre-recette-div-bouton-filtres">
            <p class="catamaran p-20">Filtres</p>
        </div>
        <a href="<?= $this->router->generate('parametres')?>" class="display-flex justify-content-center align-items-center page-livre-recette-div-bouton-editer-ingredients cursor-pointer">
            <p class="catamaran p-20">Éditer ingrédients</p>
        </a>
    </div>
</div>

<?php // Je crée les filtres Entrées, Plats, Desserts, Voir tout ?>
<div class="display-flex justify-content-center">
    <?php foreach ($typeCats as $typeCat): ?>
        <?php // Je crée les liens avec le parametre cat
        $urlParts = parse_url($_SERVER['REQUEST_URI']);
        $query = [];

        if(isset($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
        }
        $query['typeCat'] = $typeCat['slug'];
        $newUrlPartsQuery = http_build_query($query);
        $newUrl = $urlParts['path'] . '?' . $newUrlPartsQuery;
        ?>
        <a href="<?= $newUrl ?>" class="<?php if(isset($_GET['typeCat']) && $_GET['typeCat'] === $typeCat['slug']): ?>orange bold <?php else:?>noir <?php endif ?>fredoka p-25 hover-orange margin-leftright-15">
            <?= $typeCat['name'] ?>s
        </a>
    <?php endforeach ?>
    <?php 
        // Je crée le lien pour la categorie Voir Tout sans parametre cat
        $urlParts = parse_url($_SERVER['REQUEST_URI']);
        $query = [];

        if(isset($urlParts['query'])) {
            parse_str($urlParts['query'], $query);
        }
        if (isset($query['typeCat'])) {
            unset($query['typeCat']);
        }
        if(!empty($query)) {
            $newUrlPartsQuery = http_build_query($query);
            $newUrl = $urlParts['path'] . '?' . $newUrlPartsQuery;
        } else {
            $newUrl = $urlParts['path'];
        }
    ?>
    <a href="<?= $newUrl ?>" class="<?php if(!isset($_GET['typeCat'])): ?>orange bold <?php else:?>noir <?php endif ?>fredoka p-25 hover-orange margin-leftright-15">Voir tout</a>
</div>

<?php // J'affiche toutes les recettes ou celles correspondantes au filtres selectionnés ?>
<?php if(isset($_GET['typeCat'])): ?>
    <?php 
    // Preparation des données des recettes filtrées par categorie
    $requete = $pdo->prepare('
    SELECT * 
    FROM typeCat_recette tr
    JOIN recette r ON tr.recette_id = r.id
    WHERE tr.type_id = :id
    ORDER BY name ASC
    ');

    if ($_GET['typeCat'] === 'entrees') {
        // Recuperation des données de toutes les entrées
        $requete->execute(['id' => 1]);
        $items = $requete->fetchAll();
    } elseif($_GET['typeCat'] === 'plats') {
        // Recuperation des données de tous les plats
        $requete->execute(['id' => 2]);
        $items = $requete->fetchAll();
    } elseif($_GET['typeCat'] === 'desserts') {
        $requete->execute(['id' => 3]);
        $items = $requete->fetchAll();
    }
    ?>

    <div class="page-livre-recette-affichage-recettes-grille">
        <?php foreach ($items as $item): ?>
            <?php
            $filtres = new Filtres;
            // si la recette correspond a TOUS les parametres
            if ($filtres->applyFiltres($pdo, $item, $errors)) : ?>
                <a href="<?= $this->router->generate('recette', ['slug' => $item['slug']]) ?>" class="js-lien-recette">
                    <div class="page-livre-recette-affichage-recettes-item display-flex justify-content-center align-items-center position-relative">
                        <p class="catamaran p-20 noir"><?= $item['name'] ?></p>
                        <div class="page-livre-recette-affichage-recettes-item-plus js-page-livre-recette-affichage-recettes-item-plus"></div>
                        <p class="hidden"><?= $item['id'] ?></p>
                    </div>
                </a>
            <?php endif ?>
        <?php endforeach ?>
        <a href="<?=$this->router->generate('new')?>" class="display-flex justify-content-center">
            <div class="page-livre-recette-affichage-recettes-item-new display-flex justify-content-center align-items-center position-relative">
                <div class="page-livre-recette-affichage-recettes-new"></div>
            </div>
        </a>
    </div>

<?php else: ?>
    <div class="page-livre-recette-affichage-recettes-grille">
        <?php foreach ($recettes as $recette) :?>
            <?php $filtres = new Filtres;
            // si la recette correspond a TOUS les parametres
            if ($filtres->applyFiltres($pdo, $recette, $errors)) : ?>
                <a href="<?= $this->router->generate('recette', ['slug' => $recette['slug']]) ?>" class="js-lien-recette">
                    <div class="page-livre-recette-affichage-recettes-item display-flex justify-content-center align-items-center position-relative">
                        <p class="catamaran p-20 noir"><?= $recette['name'] ?></p>
                        <div class="page-livre-recette-affichage-recettes-item-plus js-page-livre-recette-affichage-recettes-item-plus"></div>
                        <p class="hidden"><?= $recette['id'] ?></p>
                    </div>
                </a>
            <?php endif ?>
        <?php endforeach ?>
        <a href="<?=$this->router->generate('new')?>" class="display-flex justify-content-center">
            <div class="page-livre-recette-affichage-recettes-item-new display-flex justify-content-center align-items-center position-relative">
                <div class="page-livre-recette-affichage-recettes-new"></div>
            </div>
        </a>
    </div>

<?php endif ?>

<div class="page-livre-recette-menu-filtres-overlay js-page-livre-recette-ajouter-overlay"></div>
<form action="<?= $this->router->generate('listeCourses') . '?ajout=1' ?>" method="post" class="page-livre-recette-ajouter-liste-form  hidden js-page-livre-recette-ajouter-liste-form">
    <h5 class="text-align-center catamaran p-25 noir js-form-ajouter-titre"></h5>
    <div class="display-flex justify-content-center align-items-center margin-top-50">
        <img src="/img/iconePersonnes.svg" alt="">
        <div class="display-flex align-items-center margin-left-40">
            <img src="/img/moinsPetitNoir.svg" alt="" class="js-ajouter-bouton-moins cursor-pointer">
            <p class="catamaran p-30 noir margin-leftright-20 js-ajouter-text">2</p>
            <img src="/img/plusPetitNoir.svg" alt="" class="js-ajouter-bouton-plus cursor-pointer">
        </div>
    </div>
    <input type="number" name="personnes[]" min="1" value="2" class="hidden js-ajouter-value">
    <input type="number" name="quantite[]" min="1" value="1" class="hidden">
    <input type="text" name="recette[]" value="" class="hidden js-ajouter-recette-id">
    <button type="submit" class="bouton-valider margin-top-50">Ajouter à la liste de courses</button>
</form>

<div class="page-livre-recette-menu-filtres-overlay js-page-livre-recette-menu-filtres-overlay"></div>

<div class="page-livre-recette-menu-filtres-div js-page-livre-recette-menu-filtres-div cursor-default">

    <div class="display-flex justify-content-center align-items-center page-livre-recette-filtres-header">
        <h2 class="fredoka bold p-30 orange">FILTRES</h2>
    </div>

    <div class="page-livre-recette-filtres-croix js-page-livre-recette-filtres-croix"></div>

    <form action="" method="GET" class="page-livre-recette-filtres-form">
        
        <h4 class="page-livre-recette-filtres-item-titre catamaran bold p-20 noir">CATÉGORIES</h4>

        <?php foreach($categories as $categorie): ?>
            <div class="display-flex align-items-center margin-left-20 margin-top-10 js-checkbox-categories">
                <div class="cursor-pointer js-checkbox-categories-checkbox page-recette-filtres-checkbox <?php if(isset($_GET['categories']) && in_array($categorie['id'], $_GET['categories'])):?> page-recette-filtres-checkbox-checked <?php endif?>"></div>
                <p class="catamaran p-20 noir"><?=$categorie['name']?></p>
                <input type="checkbox" name="categories[]" value="<?=$categorie['id']?>" <?php if(isset($_GET['categories']) && in_array($categorie['id'], $_GET['categories'])):?> checked <?php endif?> class="hidden js-checkbox-categories-value">
            </div>
        <?php endforeach ?>

        <div class="page-livre-recette-filtres-separator"></div>

        <h4 class="page-livre-recette-filtres-item-titre catamaran bold p-20 noir">DURÉE</h4>

        <?php
            $requete = $pdo->query('SELECT duration FROM recette ORDER BY duration ASC');
            $durations = $requete->fetchAll();
            $nbDuration = count($durations)-1;
            $durationMax = $durations[$nbDuration]['duration'];
            $sections = (($durations[$nbDuration]['duration'] - $durations[0]['duration']) / 5)+1;
            $widthSection = 350 / $sections;
            $nbParSection = [];
            $compare = $durations[0]['duration'];
            $k = 0;
            for ($i = 0; $i <= $sections; $i++) {
                $nb = 0;
                while (isset($durations[$k]) && $durations[$k]['duration'] == $compare) {
                    $nb += 1;
                    $k += 1;
                }
                $nbParSection[] = $nb;
                $compare += 5;
            }

            $maxNb = $nbParSection[0];
            foreach($nbParSection as $nb) {
                if($nb > $maxNb) {
                    $maxNb = $nb;
                }
            }
            
            $refheight = 100 / $maxNb;
        ?>

        <div class="page-livre-recette-filtres-duree-div margin-top-10">
            <?php for ($i = 0; $i < $sections; $i++): ?>
                <div style="width: <?=$widthSection?>px; height: <?= $nbParSection[$i] * $refheight?>px" class="page-livre-recette-filtres-duree-section-item page-livre-recette-filtres-duree-section-item-selected js-page-livre-recette-filtres-duree-section-item"></div>
            <?php endfor ?>

            <div class="page-livre-recette-filtres-duree-input-range-container">
                <div class="page-livre-recette-filtres-duree-input-range-container-selected js-page-livre-recette-filtres-duree-input-range-container-selected"></div>
                <input type="range" min="<?=$durations[0]['duration']?>" max="<?=$durationMax?>" value="<?php if(isset($_GET['durationmin']) && $_GET['durationmin'] !== ""):?><?= $_GET['durationmin']?><?php else: ?><?=$durations[0]['duration']?><?php endif?>" class="page-livre-recette-filtres-duree-slider-min js-input-range-min">
                <input type="range" min="<?=$durations[0]['duration']?>" max="<?=$durationMax?>" value="<?php if(isset($_GET['durationmax']) && $_GET['durationmax'] !== ""):?><?= $_GET['durationmax']?><?php else:?><?=$durationMax?><?php endif?>" class="page-livre-recette-filtres-duree-slider-max js-input-range-max">
            </div>
        </div>

        <div class="page-livre-recette-filtres-duree-minmax-div">
            <p class="catamaran p-20 noir js-flitres-duree-min-value"></p>
            <input type="text" id="temps-min" name="durationmin" value="" class="js-filtres-duree-input-text-min hidden">
            
            <p class="catamaran p-20 noir js-flitres-duree-max-value"></p>
            <input type="text" id="temps-max" name="durationmax" value="" class="js-filtres-duree-input-text-max hidden">
        </div>

        <div class="page-livre-recette-filtres-separator"></div>

        <h4 class="page-livre-recette-filtres-item-titre catamaran bold p-20 noir">INGRÉDIENTS</h4>

        <div class="page-livre-recette-filtres-ingredients-div js-page-livre-recette-filtres-ingredients-div">
            <?php foreach($ingredients as $ingredient): ?>
                <div class="display-flex align-items-center margin-left-20 margin-top-10 js-checkbox-ingredients">
                    <div class="cursor-pointer js-checkbox-ingredients-checkbox page-recette-filtres-checkbox <?php if(isset($_GET['ingredients']) && in_array($ingredient['id'], $_GET['ingredients'])):?> page-recette-filtres-checkbox-checked <?php endif?>"></div>
                    <p class="catamaran p-20 noir"><?=ucfirst($ingredient['name'])?></p>
                    <input type="checkbox" name="ingredients[]" value="<?=$ingredient['id']?>" <?php if(isset($_GET['ingredients']) && in_array($ingredient['id'], $_GET['ingredients'])):?> checked <?php endif?> class="hidden js-checkbox-ingredients-value">
                </div>
            <?php endforeach ?>
        </div>

        <div class="display-flex justify-content-center align-items-center page-livre-recette-filtres-voir-plus-boutton-div js-page-livre-recette-filtres-voir-plus-boutton-div">
            <p class="catamaran p-20 noir opacity-20 cursor-pointer hover-orange">Voir plus</p>
        </div>

        <div class="display-flex justify-content-center margin-top-50">
            <button type="submit" class="bouton-valider">Valider</button>
        </div>

        <script>
            
        </script>

    </form>

</div>

<script>

    const boutonFiltres = document.querySelector('.js-page-livre-recette-div-bouton-filtres');
    const divFiltres = document.querySelector('.js-page-livre-recette-menu-filtres-div');
    const overlayFiltres = document.querySelector('.js-page-livre-recette-menu-filtres-overlay');
    const croixFiltres = document.querySelector('.js-page-livre-recette-filtres-croix');
    const bodyHtml = document.querySelector('body');

    boutonFiltres.addEventListener('click', ()=>{
        divFiltres.classList.add('page-livre-recette-menu-filtres-div-show');
        overlayFiltres.classList.add('page-livre-recette-menu-filtres-overlay-show');
        bodyHtml.style.overflow = 'hidden';
    })

    overlayFiltres.addEventListener('click', ()=> {
        divFiltres.classList.remove('page-livre-recette-menu-filtres-div-show');
        overlayFiltres.classList.remove('page-livre-recette-menu-filtres-overlay-show');
        bodyHtml.style.overflow = 'visible';
    })

    croixFiltres.addEventListener('click', ()=> {
        divFiltres.classList.remove('page-livre-recette-menu-filtres-div-show');
        overlayFiltres.classList.remove('page-livre-recette-menu-filtres-overlay-show');
        bodyHtml.style.overflow = 'visible';
    })


    const checkboxCategories = Array.from(document.querySelectorAll('.js-checkbox-categories-checkbox'));

    checkboxCategories.forEach((checkbox)=>{
        checkbox.addEventListener('click', ()=>{
            let div = checkbox.closest('.js-checkbox-categories');
            let checkboxValue = div.querySelector('.js-checkbox-categories-value')
            if(checkbox.classList.contains('page-recette-filtres-checkbox-checked')) {
                checkbox.classList.remove('page-recette-filtres-checkbox-checked');
                checkboxValue.checked = false;
            } else {
                checkbox.classList.add('page-recette-filtres-checkbox-checked');
                checkboxValue.checked = true;
            }

        })
    })

    const checkboxIngredients = Array.from(document.querySelectorAll('.js-checkbox-ingredients-checkbox'));

    checkboxIngredients.forEach((checkbox)=>{
        checkbox.addEventListener('click', ()=>{
            let div = checkbox.closest('.js-checkbox-ingredients');
            let checkboxValue = div.querySelector('.js-checkbox-ingredients-value')
            if(checkbox.classList.contains('page-recette-filtres-checkbox-checked')) {
                checkbox.classList.remove('page-recette-filtres-checkbox-checked');
                checkboxValue.checked = false;
            } else {
                checkbox.classList.add('page-recette-filtres-checkbox-checked');
                checkboxValue.checked = true;
            }
        })
    })

    const boutonVoirPlusFiltresIngredients = document.querySelector('.js-page-livre-recette-filtres-voir-plus-boutton-div');
    const listeIngredientsFiltresDiv = document.querySelector('.js-page-livre-recette-filtres-ingredients-div');

    boutonVoirPlusFiltresIngredients.addEventListener('click', ()=>{
        listeIngredientsFiltresDiv.style.height = '100%';
        boutonVoirPlusFiltresIngredients.classList.add('hidden');
    })

    const lienRecettes = Array.from(document.querySelectorAll('.js-lien-recette'));
    const boutonPlusRond = Array.from(document.querySelectorAll('.js-page-livre-recette-affichage-recettes-item-plus'));
    const formAjouterListe = document.querySelector('.js-page-livre-recette-ajouter-liste-form');
    const OverlayFormAjouterListe = document.querySelector('.js-page-livre-recette-ajouter-overlay');
    const formAjouterListeTitre = document.querySelector('.js-form-ajouter-titre');
    const formAjouterListeRecetteID = document.querySelector('.js-ajouter-recette-id');

    lienRecettes.forEach((lien)=>{
        lien.addEventListener('click', (event)=> {
            event.preventDefault();
            if (event.target.classList.contains('page-livre-recette-affichage-recettes-item-plus')) {
                formAjouterListe.classList.remove('hidden');
                formAjouterListe.classList.add('page-livre-recette-ajouter-liste-form-show');
                OverlayFormAjouterListe.classList.add('page-livre-recette-menu-filtres-overlay-show');
                bodyHtml.style.overflow = 'hidden';

                formAjouterListeTitre.innerHTML = event.target.previousElementSibling.innerHTML;
                formAjouterListeRecetteID.value = event.target.nextElementSibling.innerHTML;

                const ajouterBoutonMoins = document.querySelector('.js-ajouter-bouton-moins');
                const ajouterBoutonPlus = document.querySelector('.js-ajouter-bouton-plus');
                const ajouterText = document.querySelector('.js-ajouter-text');
                const ajouterValue = document.querySelector('.js-ajouter-value');

                ajouterBoutonMoins.addEventListener('click', ()=> {
                    if (ajouterText.innerHTML == 2) {
                        ajouterBoutonMoins.classList.add('opacity-20');
                        ajouterBoutonMoins.classList.remove('cursor-pointer');
                        ajouterBoutonMoins.classList.add('cursor-default');
                        ajouterText.innerHTML = (ajouterText.innerHTML * 1) - 1;
                        ajouterValue.value = ajouterText.innerHTML;
                    } else if(ajouterText.innerHTML > 2) {
                        ajouterText.innerHTML = (ajouterText.innerHTML * 1) - 1;
                        ajouterValue.value = ajouterText.innerHTML;
                    }
                    
                })

                ajouterBoutonPlus.addEventListener('click', ()=> {
                    if (ajouterText.innerHTML == 1) {
                        ajouterBoutonMoins.classList.remove('opacity-20');
                        ajouterBoutonMoins.classList.remove('cursor-default');
                        ajouterBoutonMoins.classList.add('cursor-pointer');
                    }
                    ajouterText.innerHTML = (ajouterText.innerHTML * 1) + 1;
                    ajouterValue.value = ajouterText.innerHTML;
                })
            } else {
                window.location.href = lien.href;
            }
        })
    })
    
    OverlayFormAjouterListe.addEventListener('click', ()=> {
        formAjouterListe.classList.remove('page-livre-recette-ajouter-liste-form-show');
        formAjouterListe.classList.add('hidden');
        OverlayFormAjouterListe.classList.remove('page-livre-recette-menu-filtres-overlay-show');
        bodyHtml.style.overflow = 'visible';
    })

    const inputRangeMin = document.querySelector('.js-input-range-min');
    const inputRangeMax = document.querySelector('.js-input-range-max');
    const selected = document.querySelector('.js-page-livre-recette-filtres-duree-input-range-container-selected');
    const inputTextMin = document.querySelector('.js-filtres-duree-input-text-min');
    const inputTextMax = document.querySelector('.js-filtres-duree-input-text-max');
    let minValue = parseInt(inputRangeMin.value);
    let minMin = parseInt(inputRangeMin.min);
    let maxValue = parseInt(inputRangeMax.value);
    let maxMax = parseInt(inputRangeMax.max);
    const sectionsDureeToutes = Array.from(document.querySelectorAll('.js-page-livre-recette-filtres-duree-section-item'));

    function MettreAJourDureeMin () {
        if(minValue >= maxValue) {
            inputRangeMin.value = maxValue;
            minValue = maxValue;
        }
        let percent = ((minValue - minMin) / (maxMax - minMin)) * 100;
        selected.style.left = percent + '%';
        document.querySelector('.js-flitres-duree-min-value').innerHTML = minValue;
        if(minValue !== minMin) {
            inputTextMin.value = minValue;
        } else {
            inputTextMin.value = "";
        }

        let selectedPosition = selected.getBoundingClientRect();
        sectionsDureeToutes.forEach((section)=>{
            let sectionPosition = section.getBoundingClientRect();
            if(sectionPosition.right > selectedPosition.left && sectionPosition.left < selectedPosition.right) {
                section.classList.add('page-livre-recette-filtres-duree-section-item-selected');
            } else {
                section.classList.remove('page-livre-recette-filtres-duree-section-item-selected');
            }
        })
    }

    function MettreAJourDureeMax () {
        if(maxValue <= minValue) {
            inputRangeMax.value = minValue;
            maxValue = minValue;
        }
        let percent = ((maxMax - maxValue) / (maxMax - minMin)) * 100;
        selected.style.right = percent + '%';
        document.querySelector('.js-flitres-duree-max-value').innerHTML = maxValue;
        if(maxValue !== maxMax) {
            inputTextMax.value = maxValue;
        } else {
            inputTextMax.value = "";
        }

        let selectedPosition = selected.getBoundingClientRect();
        sectionsDureeToutes.forEach((section)=>{
            let sectionPosition = section.getBoundingClientRect();
            if(sectionPosition.left < selectedPosition.right && sectionPosition.right > selectedPosition.left) {
                section.classList.add('page-livre-recette-filtres-duree-section-item-selected');
            } else {
                section.classList.remove('page-livre-recette-filtres-duree-section-item-selected');
            }
        })
    }
            
    MettreAJourDureeMin();
    MettreAJourDureeMax();

    inputRangeMin.addEventListener('input', ()=>{
        minValue = parseInt(inputRangeMin.value);
        maxValue = parseInt(inputRangeMax.value);
        let percent = ((minValue - minMin) / (maxMax - minMin)) * 100;
        MettreAJourDureeMin();
    })

    inputRangeMax.addEventListener('input', ()=>{
        minValue = parseInt(inputRangeMin.value);
        maxValue = parseInt(inputRangeMax.value);
        let percent = ((maxMax - maxValue) / (maxMax - minMin)) * 100;
        MettreAJourDureeMax();
    })

</script>