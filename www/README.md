# Documentation API – Doctum

Ce projet utilise **Doctum** pour générer une documentation HTML à partir des commentaires PHPDoc du socle MVC.

La documentation est générée localement, sans impacter les dépendances du projet : Doctum est isolé dans le dossier `tools/`.

---

## Arborescence attendue

```
repo/
├─ tools/
│  └─ doctum/
│     └─ vendor/bin/doctum.php
└─ www/
   ├─ app/
   ├─ composer.json
   ├─ composer.lock
   ├─ doctum.php
   └─ build/docs/        ← documentation générée
```

---

## Prérequis

- PHP 8.x
- Composer
- Doctum installé dans `tools/doctum`

---

## Installation de Doctum (une seule fois, si pas présent)

Depuis la racine du dépôt :

```bash
mkdir -p tools/doctum
cd tools/doctum
composer init -n
composer require code-lts/doctum:^5.5
```

---

## Configuration

Le fichier de configuration Doctum se trouve ici :

```
www/doctum.php
```

Il définit :

- les dossiers analysés (ex: `app/`)
- les exclusions (`vendor`, `views`, `cache`, etc.)
- les dossiers de sortie (`build/docs`, `build/doctum-cache`)

---

## Générer la documentation

Toutes les commandes suivantes doivent être exécutées depuis le dossier `www/`.

### Génération incrémentale (recommandée)

```bash
composer doc
```

### Régénération complète

```bash
composer doc:force
```

---

## Emplacement de la documentation

La documentation HTML est générée dans :

```
www/build/docs/
```

Point d’entrée :

```
www/build/docs/index.html
```

---

## Consulter la documentation

### Option 1 — Ouvrir directement

Ouvrir le fichier `build/docs/index.html` dans un navigateur.

### Option 2 — Serveur local (recommandé)

```bash
php -S 127.0.0.1:8080 -t build/docs
```

Puis ouvrir :

```
http://127.0.0.1:8080
```

---

## Dépannage

### Doctum renvoie un code de sortie non nul

Doctum peut retourner un code de sortie différent de 0 même lorsque la documentation est correctement générée (warnings internes, compatibilité PHP récente).

Si le fichier `build/docs/index.html` existe, la génération est valide.

---

## Scripts Composer utilisés

Dans `www/composer.json` :

```json
"scripts": {
  "doc": "sh -lc \"php ../tools/doctum/vendor/bin/doctum.php update doctum.php; code=\\$?; if [ \\$code -eq 64 ]; then exit 0; else exit \\$code; fi\"",
  "doc:force": "php ../tools/doctum/vendor/bin/doctum.php update doctum.php --force",
  "doc:serve": "php -S 127.0.0.1:8080 -t build/docs"
}
```

pour lancer un des scripts, il faut par exemple faire `composer doc` en ligne de commande.

---