# Openclassrooms - Projet 6 - Développez de A à Z le site communautaire SnowTricks
[![SymfonyInsight](https://insight.symfony.com/projects/0f9efb16-ce9b-4e05-8375-013763ab4f39/big.svg)](https://insight.symfony.com/projects/0f9efb16-ce9b-4e05-8375-013763ab4f39)

Projet n°6 du parcours Openclassrooms "Développeur d'application PHP/Symfony" :
Concevoir un site communautaire avec Symfony

## Pré-requis
- PHP 8.1 ou supérieur
- Symfony 6.2
- Symfony CLI
- Composer
- Serveur web (Apache, MySQL, PHP)
- Visual Studio Code, PHPStorm, SublimText, ...

## Installation
Pour installer le projet, suivez les étapes suivantes :

1. Cloner le repository
```bash
  git clone https://github.com/Elisa-K/P6_Snowtricks.git
```
2. Accèder au répertoire du projet :
```bash
  cd P6_Snowtricks
```
3. Installer les dépendances :
```bash
  composer install
```
4. Installer les dépendances front-end :
```bash
  npm install
```
5. Générer les assets front-end:
```bash
  npm run build
```
6. Configurer la base de données, le service de messagerie et la clé secrète token dans le fichier `.env` à la racine du projet :
```
 	DATABASE_URL="mysql://USER:PASSWORD@HOST/DATABASE?serverVersion=8&charset=utf8mb4"

	MAILER_DSN=smtp://USERNAME:PASSWORD@HOST:PORT

	JWT_SECRET="SeCrEtKeY1234"
```
7. Créer la base de données :
```bash
  symfony console doctrine:database:create
```
8. Créer les tables de la base de données :
```bash
  symfony console doctrine:migrations:migrate
```
8. Ajouter les données fictives:
```bash
  symfony console doctrine:fixtures:load
```
9. Lancer le projet :
```bash
  npm run dev
  symfony serve
```
## Utilisation
Pour tester le site, vous pouvez vous connecter avec le compte utilisateur suivant :
```
email : user1@snowtricks.com
password : passwordUser1
```