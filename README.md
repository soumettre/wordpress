# Soumettre.fr : Plugin Wordpress #

## Installation ##
1. Récupérez l'archive du plugin téléchargeable [sur notre plate-forme](https://soumettre.fr/webmasters) 

## Getting started ##
1. Crééz un compte client gratuit sur [https://soumettre.fr/](https://soumettre.fr/).
1. Récupérez vos [identifiants API](https://soumettre.fr/user/api)
1. Allez sur la page d'option pour configurer le plugin

## Accès depuis notre serveur ##

Votre installation doit être accessible depuis notre serveur.

## Fonctionnalités du plugin ##

A l'heure actuelle, ces fonctionnalités sont au nombre de 4 : 

+ __check_added__ : Vérifie si un site est déjà présent sur votre plate-forme
+ __categories__ : Renvoie la liste de vos catégories et sous-catégories, ainsi que leurs liens de parenté
+ __post__ : Ajoute un post sur votre plate-forme
+ __check_version__ : Retourne la version du plugin utilisée

## Installer via Git ##
Il n'y a pas de bénéfice particulier à installer le plugin depuis le repository.
Si vous souhaitez quand même le faire, sachez que celui-ci utilise un submodule, qui est notre [SDK PHP](https://github.com/soumettre/sdk-api-php).

git clone --recursive git@github.com:soumettre/wordpress.git
