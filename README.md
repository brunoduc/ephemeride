# ephemeride
<u>**Version 0.9.9**</u>

Les calendriers existent pour ne pas oubliez les événements futurs.

Cette application est un sorte de carnet personnel/calendrier  où vous pourrez notez les informations que vous voulez vous souvenir plus tard.

Notez les dates de naissance pour ne pas oublier d'anniversaire ; les dates de révision de voiture ; vos travaux dans votre logements,vos dates de vaccins ....

Vous pouvez ajouter des documents liés à ces moments importants pour vous.

Prévue pour être simple à installer et à maintenir.

## Installation
Copiez le contenu le l'archive dans un dossier accessible d'un serveur web.

Juste vérifier que le répertoire **users** soit accessible en écriture à l'utilisateur sur lequel tourne le serveur web.
(généralement www-data).

## Configuration
Le fichier **config.inc.php** offre quelques possibilités de configuration :

    - `ADD_USER_CODE` est le code utilisable pour créer de nouveaux utilisateurs.
    - `TITRE` pour le titre :-)
    - `$_SESSION['ALLOWED_FILES']` est un tableau qui contient les types de fichiers autorisés en upload. Attention aux type de fichiers qui pourraient être interprétés par le serveur web (ex : php & js).

Le fichier **user.ini** peut permettre de configurer la taille maximale de fichiers en upload pour le serveur web Apache.

Le fichier **.htaccess** sous Apache :

    - Redirige vers le fichier index.php les requêtes vers des emplacements ou fichiers inexistants.
    - Interdit l'accés au fichier user.ini.

Les répertoires **users**, **css**, **htmx** contiennent un fichier index.html qui limite le listage du contenu de ces répertoires.

## Usage
Le premier utilisateur n'a qu'a renter un nom et un mot de passe. Les utilisateurs suivants devront renseigner le code renseigné dans le fichier **config.inc.php** dans `ADD_USER_CODE`. Ce code ne fait qu'autoriser la création d'un nouveau compte et peut donc être changé après chaque création de compte.

### Les catégories :
Les catégories et sous catégories servent au classement des événements.
Une catégorie spéciale **Anniversaires** est crée lors de la création du compte.

Seul deux niveaux sont actuellement possibles.

Vous devez créer la catégorie avant de l'utiliser pour un événement.

### Tags :
Les tags sont des &laquo;étiquettes&raquo; que l'on peut ajouter dynamiquement aux événements en préfixant le mot d'une étoile. Les tags composés de plusieurs mots doivent utiliser un espace insécable pour séparer les mots formant le tags.

### Événements :
Notez ce que vous voulez. Attention, il n'est pas prévu d'effacer ou de modifier un événement.

Au cas où vous souhaitez malgré tout faire des modifications, vous pouvez ouvrir le fichier de la base sqlite après un avoir fait une sauvegarde.

## Sauvegarde et transfert
Vous pouvez récupérer votre base sqlite ou votre compte entier (avec les fichiers uploadés). Attention, toutes les données sont en claires.

Pour restaurer une sauvegarde, il suffit de décompresser
l'archive dans le dossier **users**.

## Mot de passe
Les informations login/mot de passe sont dans le nom du répertoire (sous la forme : fcac5aa5fde14524eb61afd4db898f7670bb382c67639fccf9155854e2713109).

Si vous souhaitez modifier vos identifiants, recréez un nouvel utilisateur, puis transférez le contenu de votre compte actuel dans le compte de ce nouvel utilisateur.
