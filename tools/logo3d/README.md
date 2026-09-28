# Logo 3D Sirius-Solar

Source du logo 3D du hero (Three.js). Le fichier servi au site est `public/js/sirius-logo3d.js`,
un bundle qui contient déjà Three.js : pas de build nécessaire sur le serveur.

Pour le régénérer après une modification (depuis un dossier où `three` est installé) :

    npx esbuild tools/logo3d/main.js --bundle --minify --format=esm --loader:.json=json --outfile=public/js/sirius-logo3d.js

`exo2.json` : les lettres S I R U O L A - de la police Exo 2 Black Italic, au format typeface de Three.js.
