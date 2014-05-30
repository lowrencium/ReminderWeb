RemindMe Interface Web
=======

Le projet
----------------
Le projet Steria consiste dans le développement d’une application au Service de l’Homme. C’est dans cette optique que le projet “RemindMe” a vu le jour. Ce projet vise à développer une application orientée web et mobile, destinée aux personnes dont la mémoire est défaillante et qui ont besoin d’une aide permanente. Cette application aura pour objectif d’aider ces personnes au quotidien à travers des rappels réguliers. Elle s’adresse également aux personnes qui n’ont pas de problèmes médicaux, mais qui nécessitent tout de même un appui régulier dans leur quotidien.

L'interface web
----------------
L'interface web doit permettre à l'utilisateur d'accéder à l'ensemble des fonctionnalités de gestion de l'application, à savoir : création de compte, gestion des données personnelles, contacts, invités, rappels et informations. L'application web se limitera à des fonctionnalités d'interface et ne gérera pas directement le traitement des données (enregistrement, envoi de rappels...).

Les technologies
----------------
Ce site web sera réalisée en **PHP** et utilisera le framework **Silex**. La gestion des données nécessitera l'emploi de **services web**. Pendant la phase de développement le site sera hébergé sur un serveur web chez **Gandi**.

Liens utiles
----------------

[Silex](http://silex.sensiolabs.org) - Site du framework Silex

[Documentation](http://silex.sensiolabs.org/documentation) - Documentation utilisateur de Silex

[The Cookbook](http://symfony.com/) - Astuces, tutoriels et articles à propos de Symfony

Installation
----------------
En ligne de commande pour installer les dépendances :
    composer.phar install

Pour configurer la connection à la base de données :
    Copier config.yml.dist en config.yml

En ligne de commande donner les droits d'écriture dans les dossier var/cache et var/logs.