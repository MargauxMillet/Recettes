CREATE TABLE recette (

    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL, 
    slug VARCHAR(255) NOT NULL,
    duration INT NOT NULL,
    content TEXT (650000) NOT NULL, 
    created_at DATETIME NOT NULL, 
    PRIMARY KEY (id)

)

CREATE TABLE categorie (

    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)

)

CREATE TABLE ingredient (

    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)

)

CREATE TABLE categorie_recette (

    categorie_id INT UNSIGNED NOT NULL,
    recette_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (categorie_id, recette_id),
    CONSTRAINT fk_recette
        FOREIGN KEY (recette_id) 
        REFERENCES recette (id)
        ON DELETE CASCADE
        ON UPDATE RESTRICT,
    CONSTRAINT fk_categorie
        FOREIGN KEY (categorie_id)
        REFERENCES categorie (id)
        ON DELETE CASCADE
        ON UPDATE RESTRICT

)

CREATE TABLE ingredient_recette (

    quantite INT,
    unite VARCHAR(30),
    ingredient_id INT UNSIGNED NOT NULL,
    recette_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (ingredient_id, recette_id),
    CONSTRAINT fk_recette_
        FOREIGN KEY (recette_id)
        REFERENCES recette (id)
        ON DELETE CASCADE
        ON UPDATE RESTRICT,
    CONSTRAINT fk_ingredient
        FOREIGN KEY (ingredient_id)
        REFERENCES ingredient (id)
        ON DELETE CASCADE
        ON UPDATE RESTRICT

)

CREATE TABLE utilisateur (

    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    username VARCHAR(255) NOT NULL, 
    password VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)

)

CREATE TABLE categorie (

    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)

)

CREATE TABLE iCat (

    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    PRIMARY KEY (id)

)

CREATE TABLE iCat_ingredient (

    iCat_id INT UNSIGNED NOT NULL,
    ingredient_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (iCat_id, ingredient_id),
    CONSTRAINT fk_ingredient
        FOREIGN KEY (ingredient_id) 
        REFERENCES ingredient (id)
        ON DELETE CASCADE
        ON UPDATE RESTRICT,
    CONSTRAINT fk_iCat
        FOREIGN KEY (iCat_id)
        REFERENCES iCat (id)
        ON DELETE CASCADE
        ON UPDATE RESTRICT

)