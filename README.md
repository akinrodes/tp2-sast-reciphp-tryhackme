---
layout: post
title: TryHackMe DevSecOps Room > Security in the Pipeline > SAST > Remédiation XSS & SQL Injection sur showrecipe.inc.php
date: 2025-05-14
categories: [devsecops]
tags: [tryhackme, devsecops]     # TAG names should always be lowercase
---

author: Claude MONWENAGNI
project: DevSecOps PHP Challenge

source:
  - tryhackme: DevSecOps Room
  - challenge: Personnel

description: |
  Ce document retrace les étapes de sécurisation du fichier PHP [showrecipe.inc.php] récupéré lors de la room TryHackMe DevSecOps. J'ai voulu transformer ce code vulnérable en challenge personnel, en détectant et corrigeant les failles critiques (XSS et injection SQL) à l'aide de Semgrep et de bonnes pratiques DevSecOps.

contexte:
  - Le fichier source a été récupéré pendant la room TryHackMe DevSecOps.
  - L'objectif était de transformer un code volontairement vulnérable en code sécurisé, à des fins d'apprentissage et de démonstration professionnelle.
  - Deux règles Semgrep personnalisées ont été utilisées pour l'analyse :
      - semgrep-rules/echoed-request.yaml (XSS)
      - semgrep-rules/tainted-sql-string.yaml (SQLi)

outils_utilises:
  - Semgrep (via Docker)
  - Commande d'analyse :
      docker run --rm -v "${PWD}/reciphp:/src" returntocorp/semgrep semgrep --config=/src/semgrep-rules /src/showrecipe.inc.php

vulnerabilites_identifiees:
  - XSS (Cross-Site Scripting) :
      description: |
        Les paramètres utilisateurs étaient insérés dans le HTML sans échappement, permettant l'exécution de code malicieux.
      exemples:
        - Lien "Add a comment" et "Print recipe" utilisant directement $recipeid dans l'URL.
        - Pagination générant des liens avec des paramètres utilisateurs non échappés.
  - Injection SQL :
      description: |
        Les paramètres utilisateurs étaient injectés directement dans les requêtes SQL, exposant à des attaques d'injection.
      exemples:
        - Récupération de la recette : SELECT ... WHERE recipeid = $recipeid
        - Récupération des commentaires : SELECT ... WHERE recipeid = $recipeid ORDER BY ... LIMIT $offset, $recordsperpage

remediations_apportees:
  - Protection contre l'injection SQL :
      - Conversion stricte de l'identifiant utilisateur en entier dès réception ($_GET['id']).
      - Migration de toutes les requêtes SQL vers PDO avec requêtes préparées et paramètres typés.
      - Suppression de toute utilisation de l'API mysql_* (obsolète et dangereuse).
  - Protection contre le XSS :
      - Utilisation systématique de htmlentities() sur toutes les variables insérées dans le HTML issues de l'utilisateur (ex : $recipeid, numéro de page).
      - Échappement des liens de pagination et des liens d'action (add comment, print).
      - Revue de tous les echo susceptibles de contenir des données utilisateur.

exemple_code_avant:
  sql_injection: |
    $recipeid = $_GET['id'];
    $query = "SELECT ... WHERE recipeid = $recipeid";
    $result = mysql_query($query);
  xss: |
    echo "<a href=\"index.php?content=newcomment&id=$recipeid\">Add a comment</a>";

exemple_code_apres:
  sql_injection: |
    $recipeid = (int)$_GET['id'];
    $stmt = $pdo->prepare("SELECT ... WHERE recipeid = :recipeid");
    $stmt->bindParam(':recipeid', $recipeid, PDO::PARAM_INT);
    $stmt->execute();
  xss: |
    $safe_recipeid = htmlentities($recipeid, ENT_QUOTES, 'UTF-8');
    echo "<a href=\"index.php?content=newcomment&id=$safe_recipeid\">Add a comment</a>";

bonnes_pratiques:
  - Toujours valider et typer les entrées utilisateur avant usage.
  - Privilégier les requêtes préparées (PDO/MySQLi) pour toute interaction avec la base de données.
  - Échapper systématiquement les sorties HTML avec htmlentities().
  - Utiliser des outils d'analyse statique comme Semgrep pour détecter les failles courantes.

analyse_semgrep:
  - Règles utilisées :
      - semgrep-rules/echoed-request.yaml
      - semgrep-rules/tainted-sql-string.yaml
  - Commande d'analyse :
      docker run --rm -v "${PWD}/reciphp:/src" returntocorp/semgrep semgrep --config=/src/semgrep-rules /src/showrecipe.inc.php
  - Résultat attendu :
      - Plus aucune alerte critique sur XSS ou SQLi dans le fichier showrecipe.inc.php

conclusion: |
  Ce challenge m'a permis de mettre en pratique la remédiation de failles OWASP Top 10 sur du code PHP legacy, en utilisant des outils modernes (Semgrep, Docker) et des techniques professionnelles. Le fichier est désormais conforme aux bonnes pratiques DevSecOps et prêt à être utilisé comme exemple pédagogique ou démonstration technique.

---
