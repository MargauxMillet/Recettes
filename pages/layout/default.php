<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?? "Recette Basket" ?></title>
    <link rel="stylesheet" href="/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Catamaran:wght@100..900&family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
</head>
<body>

    <header class="header display-flex">
        <a href="<?= $this->router->generate('choixRecettes')?>" class="header-title orange">RECETTEBASKET</a>

        <?php if ($selected === 'courses'): ?>
            <div class="display-flex">
                <div class="lien-menu-header-item display-flex margin-right-35">
                    <img src="/img/coursesSelected.svg" alt="">
                    <div class="lien-menu-header-item-active"></div>
                    <p class="fredoka p-16 noir cursor-default">Courses</p>
                </div>
                <a href="<?= $this->router->generate('livreRecettes')?>" class="lien-menu-header-item display-flex hover-orange js-recettes-hover">
                    <div class="js-img-recettes-hover img-recettes"></div>
                    <div class="lien-menu-header-item-null"></div>
                    <p class="fredoka p-16 noir">Recettes</p>
                </a>
            </div>

        <?php elseif($selected === 'recettes'): ?>
            <div class="display-flex">
                <a href="<?= $this->router->generate('choixRecettes')?>" class="lien-menu-header-item display-flex margin-right-35 hover-orange js-courses-hover">
                    <div class="js-img-courses-hover img-courses"></div>
                    <div class="lien-menu-header-item-null"></div>
                    <p class="fredoka p-16 noir">Courses</p>
                </a>
                <div class="lien-menu-header-item display-flex">
                    <img src="/img/recettesSelected.svg" alt="">
                    <div class="lien-menu-header-item-active"></div>
                    <p class="fredoka p-16 noir cursor-default">Recettes</p>
                </div>
            </div>

        <?php elseif($selected === 'none'): ?>
            <div class="display-flex">
                <a href="<?= $this->router->generate('choixRecettes')?>" class="lien-menu-header-item display-flex margin-right-35 hover-orange js-courses-hover">
                    <div class="js-img-courses-hover img-courses"></div>
                    <div class="lien-menu-header-item-null"></div>
                    <p class="fredoka p-16 noir">Courses</p>
                </a>
                <a href="<?= $this->router->generate('livreRecettes')?>" class="lien-menu-header-item display-flex hover-orange js-recettes-hover">
                    <div class="js-img-recettes-hover img-recettes"></div>
                    <div class="lien-menu-header-item-null"></div>
                    <p class="fredoka p-16 noir">Recettes</p>
                </a>
            </div>
        <?php endif ?>
    </header>

    <div class="container">
        <?= $content ?>
    </div>

    <footer>
    </footer>
    
</body>
</html>