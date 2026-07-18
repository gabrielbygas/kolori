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
| 4 | Catalogue produits multi-unités/emballages | ✅ Fait |
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
| 4.1 | Migrations : `units`, `packaging_types`, `product_categories`, `products`, `product_variants` | ✅ Fait |
| 4.2 | Seeder des 28 catégories de départ avec leurs unités/emballages | ✅ Fait (27/28, voir journal) |
| 4.3 | Modèles Eloquent (`Product`, `ProductVariant`, `Unit`, `PackagingType`, `ProductCategory`) héritant de `BaseModel`, relations | ✅ Fait |
| 4.4 | CRUD minimal Inertia/Vue (liste + création/édition catalogue, accès admin/logisticien) | ✅ Fait |
| 4.5 | Tests : création produit avec variantes, cohérence unité/emballage | ✅ Fait |

### Journal (rempli au fur et à mesure)

**4.1 — Migrations catalogue** (2026-07-18)

5 migrations créées dans `database/migrations/` (toutes PK `uuid`, cohérent avec `BaseModel`/`HasUuids`) :
- `2026_07_18_004045_create_units_table.php` — `units` : `code` (unique), `name_fr`, `name_en`.
- `2026_07_18_004046_create_packaging_types_table.php` — `packaging_types` : `code` (unique), `name_fr`, `name_en`.
- `2026_07_18_004047_create_product_categories_table.php` — `product_categories` : `name_fr`, `name_en`, `slug` (unique).
- `2026_07_18_004048_create_products_table.php` — `products` : FK `product_category_id` (restrict), `name_fr`, `name_en`, `origin` (enum `local`/`imported`), `description` nullable, `is_active`, soft deletes.
- `2026_07_18_004049_create_product_variants_table.php` — `product_variants` : FK `product_id` (cascade), `unit_id` (restrict), `packaging_type_id` (nullable, restrict), `sku` (nullable, unique), `retail_price`/`wholesale_price` (decimal 12,2), `wholesale_min_qty`, `is_active`, contrainte unique `(product_id, unit_id, packaging_type_id)`.

Pas de colonne devise sur les prix : stockage en USD (devise de base), la conversion CDF se fera à l'affichage via le taux de config (point 7).

Vérifié : `php artisan migrate` OK, `migrate:rollback --step=5` puis re-`migrate` OK (down() propres), suite de tests existante toujours verte (25 passed / 61 assertions).

**4.2 — Seeder catégories/unités/emballages** (2026-07-18)

3 seeders créés dans `database/seeders/` (idempotents : `exists()` avant insert, relançables sans doublon) :
- `UnitSeeder.php` — 3 unités : `kg`, `l` (litre), `pcs` (pièce).
- `PackagingTypeSeeder.php` — 6 emballages : `sac`, `bidon`, `boite`, `carton`, `rouleau`, `paquet`.
- `ProductCategorySeeder.php` — 27 catégories transcrites depuis les notes manuscrites du client (photos fournies dans la conversation) : Calcium, Oxydes, Pétrole, Chaux, Titane, Tylose, Brosse, Brosse à chaux, Ambro, Palette, Penta, Cobalt, Naphtol, Ammoniac, Huile de peinture, Résine, Tige en bois, Câble, Rouleau, Roulette, Colle, Seaux, Bande collante, Peinture, Mastic, Paillette d'or, Pinceaux.

Confirmé par l'utilisateur (2026-07-18) : il s'agit bien de 27 catégories, pas 28 — le n°28 vide sur la photo n'est pas une catégorie manquante. `CLAUDE.md` §6 corrigé en conséquence.

`DatabaseSeeder.php` mis à jour pour appeler les 3 nouveaux seeders après `RoleSeeder`.

Vérifié : seed + re-seed (idempotence confirmée, pas de doublons : toujours 3/6/27 lignes), suite de tests existante toujours verte (25 passed / 61 assertions).

**4.3 — Modèles Eloquent** (2026-07-18)

- `app/Enums/ProductOrigin.php` — enum natif PHP backé (`local`/`imported`), remplace la chaîne magique sur `products.origin`.
- `app/Models/Unit.php`, `app/Models/PackagingType.php` — `#[Fillable(['code', 'name_fr', 'name_en'])]`, relation `hasMany(ProductVariant::class)`.
- `app/Models/ProductCategory.php` — `#[Fillable(['name_fr', 'name_en', 'slug'])]`, relation `hasMany(Product::class)`.
- `app/Models/Product.php` — `#[Fillable(['product_category_id', 'name_fr', 'name_en', 'origin', 'description', 'is_active'])]`, `SoftDeletes`, cast `origin` → `ProductOrigin`, `is_active` → `boolean`, relations `belongsTo(ProductCategory::class)` (alias `category()`) et `hasMany(ProductVariant::class)` (alias `variants()`).
- `app/Models/ProductVariant.php` — `#[Fillable([...])]` sur les 8 colonnes éditables, casts `retail_price`/`wholesale_price` → `decimal:2`, `wholesale_min_qty` → `integer`, `is_active` → `boolean`, relations `belongsTo` vers `Product`, `Unit`, `PackagingType` (alias `packagingType()`).

Vérifié en conditions réelles (`php artisan tinker`, transaction annulée après coup) : création d'un `Product` + `ProductVariant` liés à des données seedées (`peinture`/`l`/`bidon`), navigation dans les deux sens de toutes les relations (`product->category`, `product->variants`, `variant->product/unit/packagingType`, `category->products`, `unit->productVariants`), cast enum (`origin` → instance `ProductOrigin`), cast boolean et decimal corrects. Suite de tests existante toujours verte (25 passed / 61 assertions).

**4.4 — CRUD Inertia/Vue catalogue** (2026-07-18)

Backend :
- `bootstrap/app.php` — alias middleware `role` → `Spatie\Permission\Middleware\RoleMiddleware` (pas auto-enregistré en Laravel 13 avec le style `bootstrap/app.php`).
- `app/Http/Middleware/HandleInertiaRequests.php` — partage `auth.roles` (noms de rôles Spatie) et `flash.success` (message flash de session) comme props Inertia.
- `app/Http/Requests/Product/StoreProductRequest.php`, `UpdateProductRequest.php` — validation produit + tableau `variants.*` imbriqué, avec vérification applicative (`withValidator`) qu'une même combinaison unité/emballage n'apparaît pas deux fois dans les variantes soumises.
- `app/Actions/Product/CreateProductAction.php` — création du produit + de ses variantes dans une transaction.
- `app/Actions/Product/UpdateProductAction.php` — mise à jour du produit puis synchronisation des variantes (update des existantes par `id`, création des nouvelles, suppression de celles retirées du formulaire) dans une transaction.
- `app/Http/Resources/ProductResource.php`, `ProductVariantResource.php`, `ProductCategoryResource.php`, `UnitResource.php`, `PackagingTypeResource.php` — couche de sérialisation explicite (whitelist des colonnes exposées), ajoutée après coup pour suivre §7 de `laravel_best_practices.md` (le CRUD initial exposait les modèles Eloquent bruts).
- `app/Providers/AppServiceProvider.php` — `JsonResource::withoutWrapping()` : Inertia consomme les Resources comme props de page, pas comme réponses JSON:API — sans ça, `Resource::collection()` enveloppe dans une clé `data` qui casse le `v-for` côté Vue (bug rencontré et corrigé pendant la vérification).
- `app/Http/Controllers/ProductController.php` — `index` (liste paginée, items transformés via `->through(fn ($p) => new ProductResource($p))` pour garder la forme du paginator), `create`, `store`, `edit` (renvoient `ProductResource`/`*Resource::collection()` au lieu des modèles bruts), `update`.
- `routes/web.php` — `Route::resource('products', ...)->only(['index','create','store','edit','update'])` sous middleware `['auth','verified','role:admin|logisticien']`.

Frontend :
- `resources/js/Pages/Products/Index.vue` — tableau paginé (produit, catégorie, origine, variantes avec prix, statut), lien "Modifier", bannière de succès.
- `resources/js/Pages/Products/Create.vue`, `Edit.vue` — formulaire produit + variantes dynamiques (ajout/retrait de lignes), `useForm` Inertia (`post`/`put`).
- `resources/js/Pages/Products/Partials/ProductFormFields.vue` — champs partagés entre Create/Edit (catégorie, noms FR/EN, origine, description, actif, liste de variantes éditable).
- `resources/js/Layouts/AuthenticatedLayout.vue` — lien nav "Catalogue" (desktop + mobile), visible uniquement si `auth.roles` contient `admin` ou `logisticien`.

Vérifié en conditions réelles dans le navigateur (build `npm run build` + `php artisan serve`, connecté en admin seedé) :
- Création d'un produit "Peinture email blanche" avec 2 variantes (Pièce à $15, Litre/Bidon à $12 gros $10 dès 5) → liste affiche bien les 2 variantes et leurs prix.
- Édition : suppression de la variante "Pièce" → confirmé en base (`product_variants` passe de 2 à 1 ligne, pas de soft-delete résiduel) — la synchronisation d'`UpdateProductAction` fonctionne.
- Contrôle d'accès : un utilisateur avec le rôle `vendeur` reçoit une 403 ("User does not have the right roles") sur `/products`, et le lien "Catalogue" n'apparaît pas dans sa navigation. Un admin y accède normalement.
- Après ajout des Resources : re-vérifié le cycle create → liste → edit en navigateur (build + serveur relancés), plus un contrôle direct de la forme JSON via `(new ProductResource($product))->response()->getData(true)` en tinker (pas d'enveloppe `data`, `origin` bien en string, `unit`/`packaging_type` imbriqués corrects).
- Données de test nettoyées après chaque vérification (produits + utilisateur vendeur temporaire supprimés).

Suite de tests existante toujours verte (25 passed / 61 assertions) — pas de test automatisé dédié à ce CRUD pour l'instant, couverture prévue au sous-point 4.5.

**4.5 — Tests catalogue** (2026-07-18)

5 factories créées dans `database/factories/` (manquaient pour les modèles catalogue, nécessaires pour des tests indépendants des seeders) : `UnitFactory.php`, `PackagingTypeFactory.php`, `ProductCategoryFactory.php`, `ProductFactory.php` (enum `origin` tiré de `ProductOrigin::cases()`, jamais une valeur inventée), `ProductVariantFactory.php`.

`tests/Feature/ProductManagementTest.php` — 8 tests (style PHPUnit classique, cohérent avec le reste du projet ; Pest n'est pas installé sur ce projet donc pas introduit pour ce seul fichier) :
- `test_admin_can_create_a_product_with_multiple_variants` — flux complet POST `/products` avec 2 variantes (pièce sans emballage, litre + bidon), vérifie les 2 lignes `product_variants` créées avec les bons `unit_id`/`packaging_type_id`.
- `test_logisticien_can_create_a_product` — confirme que le 2e rôle autorisé (§4 CLAUDE.md) a bien accès, pas seulement `admin`.
- `test_vendeur_cannot_access_product_management` — 403 sur `/products` et `/products/create` pour un rôle non autorisé.
- `test_guest_is_redirected_to_login` — accès non authentifié redirigé vers `/login`.
- `test_creating_a_product_requires_at_least_one_variant` — validation rejette un tableau `variants` vide.
- `test_duplicate_unit_and_packaging_combination_is_rejected_on_creation` — la règle applicative dans `StoreProductRequest::withValidator` bloque bien 2 variantes avec la même combinaison unité/emballage dans une même requête.
- `test_database_rejects_duplicate_unit_and_packaging_combination_for_the_same_product` — même cohérence vérifiée au niveau base (contrainte unique de la migration 4.1), en insérant directement via `DB::table()` pour court-circuiter la validation applicative ; `QueryException` attendue.
- `test_updating_a_product_syncs_variants` — PUT `/products/{id}` : la variante conservée voit son prix mis à jour, une nouvelle est créée, celle omise du payload est supprimée (`assertModelMissing`) — couvre la logique de synchronisation d'`UpdateProductAction`.

Vérifié : `php artisan test --filter=ProductManagementTest` (8 passed / 28 assertions) puis suite complète (33 passed / 89 assertions). `vendor/bin/pint --test` propre (un fichier `bootstrap/app.php` corrigé au passage — imports non triés depuis le sous-point 4.4).

**Point 4 (Catalogue produits) terminé** — les 5 sous-points sont faits et vérifiés.

---

## Ajustements hors plan (2026-07-18)

Demandés par l'utilisateur après la démo de fin de point 4, avant de démarrer le point 5.

- **Suppression de la page d'accueil publique** : `resources/js/Pages/Welcome.vue` supprimé. `routes/web.php` : `/` fait désormais `Route::redirect('/', '/login')` — un invité tombe directement sur le login, un utilisateur déjà connecté est renvoyé au dashboard (comportement natif de `RedirectIfAuthenticated`, qui vérifie la route nommée `dashboard`).
- **Suppression de l'inscription publique** : routes `GET/POST /register` retirées de `routes/auth.php`, `app/Http/Controllers/Auth/RegisteredUserController.php` et `resources/js/Pages/Auth/Register.vue` supprimés, `tests/Feature/Auth/RegistrationTest.php` supprimé (n'avait plus de route à tester). `tests/Feature/ExampleTest.php` mis à jour pour refléter la redirection de `/`.
- **Nouvelle fonctionnalité : gestion des utilisateurs (admin uniquement)** — décision utilisateur : page dédiée liste + création, rôles assignables = admin/vendeur/logisticien (les 3).
  - `app/Http/Requests/User/StoreUserRequest.php` — validation (email unique, mot de passe confirmé, rôle parmi les 3 valides).
  - `app/Actions/User/CreateUserAction.php` — crée le compte et assigne le rôle dans une transaction ; ne connecte jamais automatiquement l'admin créateur à la place du nouveau compte (vérifié par test : `assertAuthenticatedAs($admin)`).
  - `app/Http/Resources/UserResource.php` — expose id/name/email/phone/is_active/roles.
  - `app/Http/Controllers/UserController.php` — `index` (liste paginée, rôles eager-loadés), `create`, `store`.
  - `routes/web.php` — `Route::resource('users', ...)->only(['index','create','store'])` sous `['auth','verified','role:admin']` (logisticien exclu, contrairement au catalogue).
  - `resources/js/Pages/Users/Index.vue`, `Create.vue` — mêmes conventions que `Products/*`.
  - `resources/js/Layouts/AuthenticatedLayout.vue` — lien nav "Utilisateurs" visible admin uniquement (`isAdmin` computed).
  - `tests/Feature/UserManagementTest.php` — 6 tests : création avec rôle + admin reste connecté, 403 pour vendeur/logisticien, redirection invité, email dupliqué rejeté, liste affiche les rôles.
- **`database/seeders/DemoProductSeeder.php`** — 5 produits d'exemple avec variantes (répond à "pourquoi je ne vois rien dans /products" : les seeders du point 4.2 ne créaient que les données de référence — unités/emballages/catégories —, jamais de produits). Appelé depuis `DatabaseSeeder` uniquement si `app()->environment('local')` — jamais en production/déploiement client.

Vérifié : suite de tests complète (37 passed / 101 assertions), `vendor/bin/pint --test` propre, `php artisan migrate:fresh --seed` puis parcours manuel en navigateur (redirection `/`, 404 sur `/register`, connexion admin, `/products` affiche les 5 produits de démo, création d'un utilisateur "Jean Vendeur" avec rôle vendeur via `/users/create` — donnée de test nettoyée ensuite).

⚠️ Le navigateur intégré de l'outil s'est bloqué une fois en cours de route (screenshot en timeout) — contournement en ouvrant un nouvel onglet, sans lien avec l'application (confirmé par un test HTTP direct qui passait pendant le blocage).

---

## Points suivants

Non décomposés pour l'instant (seront détaillés en sous-points quand ils deviendront le point courant), voir §4/§10 de `CLAUDE.md` pour le contenu prévu. Prochain point : **5 — Stock (quantités + mouvements)**.
