# Kolori — Suivi des tâches

Ce fichier est la **source de vérité** de l'avancement du projet, à consulter en début de session.
Il liste tous les points du plan (voir [`CLAUDE.md`](./CLAUDE.md)), l'état de chacun, et pour le point en cours,
la décomposition en sous-points avec un descriptif (façon commit) de ce qui a été fait après exécution de chacun.

Règles de mise à jour (voir `CLAUDE.md` §11) :
- On ne travaille qu'un point à la fois, dans l'ordre.
- Seul le point courant est décomposé en sous-points (max 5) — les points suivants ne sont détaillés qu'une fois atteints.
- Après exécution + tests d'un sous-point, ce fichier est mis à jour immédiatement : statut + descriptif (pages/fonctions créées/modifiées/supprimées).
- Le point suivant n'est proposé (et décomposé) qu'après validation explicite de l'utilisateur.

---

## Vue d'ensemble des points

| # | Point | Statut |
|---|-------|--------|
| 1 | Socle Laravel 13 + Breeze (auth Vue/Inertia/Tailwind) | ✅ Fait |
| 2 | Rôles (Spatie Permission) : `admin`, `vendeur`, `logisticien` | ✅ Fait |
| 3 | Clés UUID sur `users` et les tables de permissions | ✅ Fait |
| 4 | Catalogue produits multi-unités/emballages | 🔵 En cours |
| 5 | Stock (quantités + mouvements) | ⬜ À faire |
| 6 | Vente / POS (double devise USD/CDF, paiement espèces) | ⬜ À faire |
| 7 | Page config (magasin, branding, langue, taux de change, TVA) | ⬜ À faire |
| 8 | Multi-magasin (table `stores` + scoping) | ⬜ À faire |
| 9 | Tableau de bord (ventes du jour, stock bas) | ⬜ À faire |
| 10 | Reçus PDF / impression | ⬜ À faire |
| 11 | PWA + queue offline | ⬜ À faire |
| 12 | Déploiement (PlanetHoster / VPS) | ⬜ À faire |

---

## Point 4 — Catalogue produits multi-unités/emballages

Réutilisation des tables `units`, `packaging_types`, `product_categories` de `painting-erp` (§6 CLAUDE.md),
+ `products` (nom FR/EN, catégorie, origine local/importé) + `product_variants` (unité, emballage, prix détail/gros, seuil gros).

### Sous-points

| # | Sous-point | Statut |
|---|------------|--------|
| 4.1 | Migrations : `units`, `packaging_types`, `product_categories`, `products`, `product_variants` | ⬜ À faire |
| 4.2 | Seeder des 28 catégories de départ avec leurs unités/emballages | ⬜ À faire |
| 4.3 | Modèles Eloquent (`Product`, `ProductVariant`, `Unit`, `PackagingType`, `ProductCategory`) héritant de `BaseModel`, relations | ⬜ À faire |
| 4.4 | CRUD minimal Inertia/Vue (liste + création/édition catalogue, accès admin/logisticien) | ⬜ À faire |
| 4.5 | Tests : création produit avec variantes, cohérence unité/emballage | ⬜ À faire |

### Journal (rempli au fur et à mesure)

_(vide pour l'instant — sera complété après chaque sous-point exécuté et testé)_

---

## Points suivants

Non décomposés pour l'instant (seront détaillés en sous-points quand le point 4 sera terminé), voir §4/§10 de `CLAUDE.md` pour le contenu prévu.
