# Portfolio V1

Portfolio bilingue administrable, construit avec Symfony 8.1 et EasyAdmin 5. L’administration centralise les contenus, le profil, les paramètres SEO et les messages reçus.

## Fonctionnalités

- portfolio français/anglais avec navigation au clavier, à la molette et au tactile.
- projets, parcours, compétences et présentation administrables.
- formulaire de contact guidé, protégé par CSRF, reCAPTCHA v3 et limitation de débit.
- notifications par e-mail et réponses aux visiteurs.
- administration réservée au rôle `ROLE_ADMIN`, avec limitation des tentatives de connexion.
- contenus HTML riches nettoyés avant affichage.
- téléversements WEBP et PDF validés et stockés dans `public/uploads/`.
- actions dépendantes guidées ou bloquées avec un message clair.

## Prérequis

- PHP 8.4 ou plus récent, avec `ctype`, `fileinfo`, `iconv`, `intl`, `mbstring` et `pdo_mysql`.
- Composer 2.8 ou plus récent.
- MySQL 8.0+ ou MariaDB 10.11+.
- un serveur SMTP.
- des clés Google reCAPTCHA v3.

## Installation locale

```bash
git clone URL_DU_DEPOT.git
cd PortfolioV1
composer install
cp .env .env.local
```

Renseignez ensuite les valeurs locales dans `.env.local`. Ce fichier ne doit jamais être ajouté à Git :

```dotenv
APP_ENV=dev
APP_SECRET=une_valeur_aleatoire_longue
APP_URL="http://127.0.0.1:8000"
DATABASE_URL="mysql://utilisateur:mot_de_passe@127.0.0.1:3306/portfolio?serverVersion=8.4.0&charset=utf8mb4"
MAILER_DSN="smtp://utilisateur:mot_de_passe@serveur:587"
MAILER_FROM="Portfolio <portfolio@example.com>"
RECAPTCHA3_KEY="cle_publique"
RECAPTCHA3_SECRET="cle_privee"
```

Adaptez `serverVersion` à la version exacte de MySQL ou MariaDB utilisée, puis initialisez l’application :

```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:user:manage
```

Lancez le serveur avec Symfony CLI :

```bash
symfony server:start
```

Le portfolio est disponible sur `/fr` et l’administration sur `/connexion`.

## Environnement Docker local

Le projet fournit un conteneur MySQL dédié sur `127.0.0.1:3307` et Mailpit sur `127.0.0.1:8025`. Ils n’interfèrent pas avec un éventuel MySQL déjà présent sur le port 3306.

```bash
docker compose up -d
```

Créez `.env.dev.local`, ignoré par Git, avec la configuration locale :

```dotenv
DATABASE_URL="mysql://portfolio_app:portfolio_dev@127.0.0.1:3307/portfoliov1?serverVersion=8.0.45&charset=utf8mb4"
MAILER_DSN="smtp://127.0.0.1:1025"
```

Initialisez ensuite la base :

```bash
php bin/console doctrine:migrations:migrate --no-interaction
php bin/console app:user:manage
```

Les e-mails locaux sont consultables dans Mailpit à l’adresse `http://127.0.0.1:8025`. Pour arrêter les conteneurs sans supprimer les données :

```bash
docker compose stop
```

## Architecture du projet

```text
PortfolioV1/
├── bin/
│   ├── console
│   └── phpunit
├── config/
│   ├── packages/
│   ├── routes/
│   ├── bundles.php
│   ├── routes.yaml
│   └── services.yaml
├── migrations/
├── public/
│   ├── js/
│   ├── styles/
│   │   ├── fonts/
│   │   └── img/
│   ├── uploads/
│   └── index.php
├── src/
│   ├── Command/
│   ├── Controller/
│   │   └── Admin/
│   ├── Entity/
│   ├── Enum/
│   ├── EventListener/
│   ├── EventSubscriber/
│   ├── Form/
│   ├── Repository/
│   └── Service/
├── templates/
│   ├── admin/
│   ├── email/
│   ├── security/
│   └── site/
├── tests/
├── translations/
├── compose.yaml
├── composer.json
└── README.md
```

Le code PHP se trouve dans `src/`. Les vues Twig sont regroupées dans `templates/`. Les fichiers CSS, JavaScript, images et polices sont servis directement depuis `public/`. Les fichiers envoyés depuis l’administration sont stockés dans `public/uploads/`.

## Première configuration guidée

Dans l’administration, suivez cet ordre :

1. complétez **Mon utilisateur** avec l’identité, la photo, le CV et les liens.
2. renseignez **Paramètres du site** pour le titre, le SEO et l’adresse publique.
3. créez au moins une **Compétence**.
4. ajoutez les **Projets**, qui exigent au moins une compétence.
5. ajoutez le **Parcours** et la **Présentation**.

Un projet ne peut pas être créé sans compétence. Une compétence utilisée par un projet ne peut pas être supprimée avant le retrait de cette association. Les comptes administrateur et paramètres du site sont traités comme des fiches uniques. La présentation est limitée à trois blocs afin de préserver la mise en page publique.

## Fichiers téléversés

Les fichiers envoyés depuis l’administration sont écrits sous `public/uploads/`. Leur contenu est ignoré par Git, mais les répertoires sont conservés avec des fichiers `.gitkeep`.

## Sécurité

- ne versionnez jamais `.env.local`, les clés reCAPTCHA, les identifiants SMTP ou un export de base de données.
- utilisez un mot de passe administrateur unique d’au moins 12 caractères avec majuscule, minuscule et chiffre.

## Licence

Projet privé, sans licence de redistribution.
