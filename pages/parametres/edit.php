<?php

use App\ConnectionBD;

$title = 'Éditer les ingrédients';
$selected = 'none';

$pdo = ConnectionBD::getPDO();

// Je récupère tous les ingrédients
$requete = $pdo->query('
    SELECT * FROM ingredient
    ORDER BY name ASC
    ');
$ingredients = $requete->fetchAll();

// Je récupère toutes les catégories d'ingrédients (Fuits et Légumes, Protéines, ...)
$requete = $pdo->query('SELECT * FROM iCat');
$iCats = $requete->fetchAll();

// Je récupère toutes les catégories (Végétarien, Au four, ...)
$requete = $pdo->query('SELECT * FROM categorie');
$categories = $requete->fetchAll();

$errors = [];

if (isset($_POST['newIngredient'])) {
    // Je verifie si il y a des erreurs dans les données rentrées pour les ingredients
    $requete = $pdo->query("SELECT name FROM ingredient");
    $allIngredients = $requete->fetchAll();
    $allIngredientsListe = [];
    foreach ($allIngredients as $allIngredient) {
        $allIngredientsListe[]= strtolower(trim($allIngredient['name']));
    }
    if(in_array(strtolower(trim($_POST['newIngredient'])), $allIngredientsListe)){
        $errors['ingredientnotunique'] = 'Cet ingrédient existe déjà';
    }
    if($_POST['newIngredient'] === ' ' || $_POST['newIngredient'] === ''){
        $errors['ingredientinvalid'] = "La valeur de l'ingrédient n'est pas valide";
    }
    if(!isset($_POST['iCat']) || count($_POST['iCat']) > 1){
        $errors['ingredientnocat'] = 'Une catégorie doit être ajoutée';
    }

    // Si il n'y a pas d'erreurs je modifie la base de données
    if (empty($errors)) {
        $requete = $pdo->prepare('
        INSERT INTO ingredient (name) 
        VALUES (:name)
        ');
        $requete->execute(['name' => strtolower(trim($_POST['newIngredient']))]);

        $requete = $pdo->prepare('
            SELECT id 
            FROM ingredient
            WHERE name = :name
        ');
        $requete->execute(['name' => $_POST['newIngredient']]);
        $id = $requete->fetch();

        $requete = $pdo->prepare('
            INSERT INTO iCat_ingredient (iCat_id, ingredient_id)
            VALUES (:iCat_id, :ingredient_id)
        ');
        $requete->execute(['iCat_id' => $_POST['iCat'][0], 'ingredient_id' => $id['id']]);

        header('Location: ' . '/parametres/edit' . '?ingredient=1');
        exit();
    }
}

if (isset($_POST['newCategorie'])) {
    // Je verifie si il y a des erreurs dans les données rentrées pour les catégories
    $requete = $pdo->query("SELECT name FROM categorie");
    $allCategories = $requete->fetchAll();
    $allCategoriesListe = [];
    foreach ($allCategories as $allCategorie) {
        $allCategoriesListe[]= strtolower(trim($allCategorie['name']));
    }
    if(in_array(strtolower(trim($_POST['newCategorie'])), $allCategoriesListe)){
        $errors['categorienotunique'] = 'Cette catégorie existe déjà';
    }
    if($_POST['newCategorie'] === ' ' || $_POST['newCategorie'] === ''){
        $errors['categorieinvalid'] = 'Veuillez rentrer une valeur valeur valide';
    }

    // Si il n'y a pas d'erreurs je modifie la base de données
    if (empty($errors)) {
        $catName = ucfirst(strtolower(trim($_POST['newCategorie'])));
        $requete = $pdo->prepare('
        INSERT INTO categorie (name) 
        VALUES (:name)
        ');
        $requete->execute(['name' => $catName]);

        header('Location: ' . '/parametres/edit' . '?categorie=1');
        exit();
    }
}

if (isset($_GET['supI'])) {

    //Si il a été demandé de supprimer un ingrédient, je le supprime de la base de données
    $requete = $pdo->prepare ('
        DELETE FROM ingredient
        WHERE id = :id
    ');
    $requete->execute(['id' => $_GET['supI']]);

    $requete = $pdo->prepare('
        DELETE FROM ingredient_recette
        WHERE ingredient_id = :id
    ');
    $requete->execute(['id' => $_GET['supI']]);

    header('Location: ' . '/parametres/edit' . '?supIngredient=1');
    exit();
}

if (isset($_GET['supC'])) {

    //Si il a été demandé de supprimer une categorie, je la supprime de la base de données
    $requete = $pdo->prepare ('
        DELETE FROM categorie
        WHERE id = :id
    ');
    $requete->execute(['id' => $_GET['supC']]);

    $requete = $pdo->prepare('
        DELETE FROM categorie_recette
        WHERE categorie_id = :id
    ');
    $requete->execute(['id' => $_GET['supC']]);

    header('Location: ' . '/parametres/edit' . '?supCategorie=1');
    exit();
}
?>

<div class="width-80p">
    <?php $newRequestURI = str_replace('/edit', '', $_SERVER['REQUEST_URI']) ?>
    <a href="<?= $this->router->generate('livreRecettes')?>" class="display-flex align-items-center recette-lien-retour margin-top-35">
        <div class="recette-lien-retour-fleche"></div>
        <p class="catamaran p-20 noir recette-lien-retour-text">Retour</p>
    </a>
</div>

<?php if (isset($_GET['ingredient']) && $_GET['ingredient'] == 1): ?>
    <div class="page-recette-alerte-succes cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">L'ingrédient a bien été ajouté</h2>
    </div>
<?php endif ?>

<?php if (isset($_GET['supIngredient']) && $_GET['supIngredient'] == 1): ?>
    <div class="page-recette-alerte-succes cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">L'ingrédient a bien été supprimé</h2>
    </div>
<?php endif ?>

<?php if (!empty($errors)): ?>
    <div class="page-recette-alerte-echec cursor-default">
        <h2 class="beige catamaran p-25 bold text-align-center">L'ingrédient n'a pas pu être ajouté</h2>
    </div>
<?php endif ?>

<form action="<?=$this->router->generate('parametres')?>" method="POST">
    <div class="display-flex flex-direction-column align-items-center nouvel-ingredient-encadrement-infos">

        <h1 class="ajouter-ingredient-title cursor-default">Ajouter un ingrédient</h1>

        <div class="display-flex flex-direction-column margin-center edit-recette-input-titre-div margin-top-70 cursor-default">
            <?php if(isset($errors['ingredientnotunique'])): ?>
                <p class="red catamaran p-20"><?= $errors['ingredientnotunique']?></p>
            <?php endif ?>
            <?php if(isset($errors['ingredientinvalid'])): ?>
                <p class="red catamaran p-20"><?= $errors['ingredientinvalid']?></p>
            <?php endif ?>

            <input type="text" name="newIngredient" placeholder="Ingrédient" id="newIngredient" class="catamaran noir p-20 edit-recette-input-titre <?php if(isset($errors['ingredientnotunique']) || isset($errors['ingredientinvalid'])): ?> edit-recette-input-titre-red <?php endif ?>">
        </div>

        <div class="margin-top-50 display-flex flex-direction-column align-items-center">
            <?php if(isset($errors['ingredientnocat'])): ?>
                <p class="red catamaran p-20 margin-bottom-10"><?= $errors['ingredientnocat']?></p>
            <?php endif ?>
            <div class="display-flex justify-content-center">
                <?php foreach($iCats as $iCat): ?>
                    <div class="edit-recette-input-typecat js-edit-recette-input-typecat">
                        <p class="catamaran p-25 js-edit-ingredient-input-icat-p cursor-pointer hover-orange <?php if (isset($errors['ingredientnocat'])): ?>red bold<?php else:?> noir<?php endif?>"><?= $iCat['name']?></p>
                        <input type="radio" id="<?= $iCat['id']?>" name="iCat[]" value="<?= $iCat['id']?>" class="hidden js-edit-ingredient-input-icat-value">
                    </div>
                <?php endforeach ?>
            </div>
        </div>

        <button type="submit" class="bouton-valider margin-top-70">Ajouter</button>

    </div>

    <div class="display-flex flex-direction-column align-items-center margin-top-110">
        <h5 class="ajouter-ingredient-title cursor-default">Liste des ingrédients</h5>
        <table class="margin-top-50 cursor-default">
            <thead>
                <tr class="background-color-beige">
                    <th class="table-liste-ingredient-colonne-1"><div class="catamaran p-20 noir table-recap-liste-titre">Ingrédient</div></th>
                    <th class="table-liste-ingredient-colonne-2"><div class="catamaran p-20 noir table-recap-liste-titre">Catégorie</div></th>
                    <th class="table-liste-ingredient-colonne-3"><div class="catamaran p-20 noir table-recap-liste-titre"></div></th>
                </tr>
            </thead>

            <tbody>
                <?php $color = 'beige'?>
                <?php foreach ($ingredients as $ingredient): ?>
                    <?php 
                        $pdo = ConnectionBD::getPDO();
                        //Je recupère la liste du type
                        $requete = $pdo->prepare('
                            SELECT name 
                            FROM iCat i
                            JOIN iCat_ingredient ii ON ii.iCat_id = i.id
                            WHERE ingredient_id = :id
                        ');
                        $requete->execute(['id' => $ingredient['id']]);
                        $icatName = $requete->fetchAll();

                        if($color == 'none') {
                            $color = 'beige';
                        } else if($color == 'beige') {
                            $color = 'none';
                        }
                    ?>
                    <tr <?php if($color == 'beige'): ?>class="background-color-beige"<?php endif ?>>
                        <td class="table-liste-ingredient-colonne-1"><div class="catamaran noir p-20"><?= ucfirst($ingredient['name']) ?></div></td>
                        <td class="table-liste-ingredient-colonne-2"><div class="catamaran noir p-20"><?= $icatName[0]['name']?></div></td>
                        <td class="table-liste-ingredient-colonne-3"><a href="<?= parse_url($_SERVER['REQUEST_URI'])['path'] . "?supI={$ingredient['id']}"?>"><div class="js-table-row-supp cursor-pointer img-poubelle-poss-hover"></div></a></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

</form>

<script>

    const icatsToutes = Array.from(document.querySelectorAll('.js-edit-ingredient-input-icat-p'));
    
    icatsToutes.forEach((icat)=>{
        icat.addEventListener('click', () =>{
            document.querySelectorAll('.js-edit-ingredient-input-icat-p').forEach((k)=>{
                if(k.classList.contains('orange')) {
                    k.classList.add('noir');
                    k.classList.remove('orange');
                    k.classList.add('cursor-pointer');
                    k.classList.remove('cursor-default');
                    let div = k.closest('.js-edit-recette-input-typecat');
                    div.querySelector('.js-edit-ingredient-input-icat-value').checked = false;
                }
                
            })

            icat.classList.remove('noir');
            icat.classList.add('orange');
            icat.classList.remove('cursor-pointer');
            icat.classList.add('cursor-default');
            let div = icat.closest('.js-edit-recette-input-typecat');
            div.querySelector('.js-edit-ingredient-input-icat-value').checked = true;
        })
    })

    const boutonsSupprimerIngredient = document.querySelectorAll('.js-bouton-supprimer-ingredient');

    boutonsSupprimerIngredient.forEach(bouton => {
        bouton.addEventListener('click', ()=>{
        event.preventDefault();
        let alert = confirm("Voulez-vous vraiment supprimer cet ingredient ?");
        if (alert) {
            window.location.href = bouton.href;
        }
        })
    })

    const boutonsSupprimerCatgeorie = document.querySelectorAll('.js-bouton-supprimer-categorie');

    boutonsSupprimerCatgeorie.forEach(bouton => {
        bouton.addEventListener('click', ()=>{
        event.preventDefault();
        let alert = confirm("Voulez-vous vraiment supprimer cette catégorie ?");
        if (alert) {
            window.location.href = bouton.href;
        }
        })
    })
</script>