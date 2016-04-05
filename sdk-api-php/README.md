# Soumettre.fr : API pour Webmasters #

## Installation ##
+ Via __Git__ : git clone git@github.com:soumettre/sdk-api-php.git soumettre-api
+ Via __composer/packagist__ : composer require soumettre/sdk-api-php

## Getting started ##
1. Crééz un compte client gratuit sur [https://soumettre.fr/](https://soumettre.fr/).
1. Installez le package dans un répertoire de votre site accessible depuis l'extérieur.
1. Editez le fichier config.php à la racine du package.
1. Implémentez les services.

## Accès depuis notre serveur ##

Votre installation doit être accessible depuis notre serveur.
Le package propose un type d'utilisation : toutes les requêtes vers le répertoire sont routées (via un .htaccess) vers le fichier index.php, et la classe SoumettreApi se charge de déterminer le service appelé en fonction de la route.

## Implémentation des services ##

Etendez la classe SoumettreApi pour implémenter les 4 dernières méthodes (les "services") à votre façon.
Ces méthodes, telles qu'implémentées dans la classe, sont des exemples des retours attendus.
 
A l'heure actuelle, ces services sont au nombre de 4 : 

+ __check_added__ : Vérifie si un site est déjà présent sur votre plate-forme
+ __categories__ : Renvoie la liste de vos catégories et sous-catégories, ainsi que leurs liens de parenté
+ __post__ : Ajoute un post sur votre plate-forme
+ __delete__ : Efface un post de votre plate-forme

