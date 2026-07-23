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
| 5 | Stock (quantités + mouvements) | ✅ Fait |
| 6 | Vente / POS (double devise USD/CDF, paiement espèces) | ✅ Fait |
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

## Point 5 — Stock (quantités + mouvements)

Quantité courante par variante (`product_variants.current_stock`) + historique des mouvements (`stock_movements` : entrée/sortie, quantité, motif, utilisateur). Pas de workflow d'achat formel (§4/§5 CLAUDE.md) — mouvements manuels uniquement.

### Sous-points

| # | Sous-point | Statut |
|---|------------|--------|
| 5.1 | Migration : `current_stock` sur `product_variants` + table `stock_movements` | ✅ Fait |
| 5.2 | Modèle Eloquent `StockMovement` + Action qui applique un mouvement et met à jour `current_stock` (transaction, verrou, refus si stock négatif) | ✅ Fait |
| 5.3 | CRUD minimal Inertia/Vue : page mouvements de stock (liste + ajout manuel), accès admin/logisticien | ✅ Fait |
| 5.4 | Alerte stock bas : seuil configurable par variante + indicateur visuel dans le catalogue | ✅ Fait |
| 5.5 | Tests : mouvement met à jour la quantité, refus stock négatif, contrôle d'accès par rôle | ✅ Fait |

### Journal (rempli au fur et à mesure)

**5.1 — Migrations stock** (2026-07-23)

2 migrations créées dans `database/migrations/` :
- `2026_07_23_180912_add_current_stock_to_product_variants_table.php` — ajoute `current_stock` (decimal 12,2, défaut 0) sur `product_variants`.
- `2026_07_23_180913_create_stock_movements_table.php` — table `stock_movements` : PK uuid, FK `product_variant_id` (cascade), `type` (enum `in`/`out`), `quantity` (decimal 12,2 — pas de contrainte `unsigned` en base, `unsignedDecimal()` n'existe plus sur `Blueprint` dans cette version de Laravel ; la non-négativité sera imposée côté validation applicative au sous-point 5.2), `reason` nullable, FK `user_id` (nullable, `nullOnDelete` — l'historique reste si le compte est supprimé), index composite `(product_variant_id, created_at)` pour l'historique par variante.

`CLAUDE.md` — MLD mis à jour (règle transverse) : `PRODUCT_VARIANTS.current_stock` ajouté, nouvelle entité `STOCK_MOVEMENTS` et ses relations avec `PRODUCT_VARIANTS`/`USERS`.

Vérifié : `php artisan migrate` OK (un aller-retour sur `unsignedDecimal` corrigé en cours de route), `migrate:rollback --step=2` puis re-`migrate` OK, suite de tests existante toujours verte (37 passed / 101 assertions).

**5.2 — Modèle StockMovement + Action** (2026-07-23)

- `app/Enums/StockMovementType.php` — enum natif PHP backé (`in`/`out`).
- `app/Models/StockMovement.php` — `#[Fillable(['product_variant_id', 'type', 'quantity', 'reason', 'user_id'])]`, casts `type` → `StockMovementType`, `quantity` → `decimal:2`, relations `belongsTo` vers `ProductVariant` et `User`.
- `app/Models/ProductVariant.php` — ajout du cast `current_stock` → `decimal:2` et de la relation `hasMany(StockMovement::class)` (`stockMovements()`). `current_stock` reste volontairement absent du `#[Fillable]` : ne doit jamais être modifiable en masse (formulaire produit, etc.), seule `RecordStockMovementAction` y touche.
- `app/Actions/Stock/RecordStockMovementAction.php` — dans une transaction avec `lockForUpdate()` sur la variante (évite les courses concurrentes entre deux mouvements simultanés) : calcule le nouveau stock en arithmétique décimale exacte (`bcadd`/`bccomp`, jamais de flottant sur une quantité), refuse le mouvement (`ValidationException`) si le résultat serait négatif, enregistre le `StockMovement`, puis applique `current_stock` via `forceFill()` (contournement volontaire et documenté du `$fillable`, réservé à cette Action).

🐛 **Bug trouvé et corrigé pendant la vérification** : la première version utilisait `$variant->update(['current_stock' => $newStock])`, qui échouait *silencieusement* (aucune exception, `current_stock` simplement ignoré) puisque la colonne est hors du `$fillable` — la protection que je venais d'ajouter bloquait ma propre Action. Corrigé en remplaçant par `forceFill()->save()`.

Vérifié en conditions réelles (`php artisan tinker`, transaction annulée après coup) : entrée +50 → stock 50 ; sortie -20 → stock 30 ; tentative de sortie de 1000 → `ValidationException` levée avec le bon message, stock inchangé à 30, mouvement rejeté non enregistré (2 mouvements en base, pas 3). Suite de tests existante toujours verte (37 passed / 101 assertions), `vendor/bin/pint --test` propre.

**5.3 — Page Stock (Inertia/Vue)** (2026-07-23)

Demande explicite de l'utilisateur : soigner particulièrement l'ergonomie (contexte RDC — connexions lentes, usage mobile en magasin, mouvements fréquents tout au long de la journée). Décisions de conception :
- **Une seule page** (`/stock`), pas de navigation séparée liste/création : le formulaire d'ajout est toujours visible en haut, zéro clic de navigation pour enregistrer un mouvement.
- **Journal immuable** : pas de routes edit/delete sur les mouvements (comme une vraie comptabilité — une erreur se corrige par un mouvement inverse, jamais en réécrivant l'historique).
- Sélection rapide : chaque ligne du tableau "Stock actuel" a un bouton "Mouvement" qui pré-remplit la variante dans le formulaire et scrolle/focus automatiquement, pour éviter de rechercher dans une longue liste déroulante à chaque fois.
- Toggle Entrée/Sortie en 2 gros boutons colorés (vert/rouge) plutôt qu'un `<select>`, pour limiter les erreurs de saisie sur un geste répété des dizaines de fois par jour.
- "Derniers mouvements" : les 20 derniers uniquement, sans pagination — reste léger.

Fichiers :
- `app/Http/Requests/Stock/StoreStockMovementRequest.php` — validation (`product_variant_id` existant, `type` parmi l'enum, `quantity` numérique > 0).
- `app/Http/Resources/StockVariantResource.php`, `StockMovementResource.php` — libellé produit (`"Produit — Unité / Emballage"`) calculé côté serveur, pour garder le Vue simple (juste afficher des chaînes, aucune logique de formatage côté client).
- `app/Http/Controllers/StockMovementController.php` — `index` (variantes actives + 20 derniers mouvements), `store` (délègue à `RecordStockMovementAction`, `user_id` = utilisateur connecté).
- `routes/web.php` — `GET/POST /stock` sous `['auth','verified','role:admin|logisticien']` (routes explicites, pas `Route::resource`, puisque seuls index/store existent).
- `resources/js/Pages/Stock/Index.vue` — page unique décrite ci-dessus, mobile-first (gros boutons, `inputmode="decimal"` sur la quantité).
- `resources/js/Layouts/AuthenticatedLayout.vue` — lien nav "Stock" (desktop + mobile), même règle de visibilité que "Catalogue".

⚠️ Le navigateur intégré de l'outil a de nouveau bloqué certains clics (boutons Entrée/Sortie, Enregistrer) sans aucune requête réseau déclenchée — même symptôme qu'au point 4. Contourné en rouvrant un onglet, puis en déclenchant les clics via `element.click()` en JS (équivalent d'un vrai clic utilisateur, déclenche normalement les handlers Vue) pour fiabiliser la vérification.

Vérifié en conditions réelles (navigateur, viewport mobile 375×812, avec les produits de démo) :
- Sélection rapide depuis une ligne du tableau → variante bien pré-remplie dans le formulaire.
- Entrée de 100 → stock affiché passe à 100.00, apparaît dans "Derniers mouvements" avec date/utilisateur/motif.
- Tentative de sortie de 500 (stock insuffisant) → message d'erreur clair sous le champ quantité, stock inchangé, rien enregistré.
- Sortie valide de 30 → stock passe à 70.00.
- Accès `/stock` refusé (403) pour un compte `vendeur`.
- Données de test nettoyées (mouvements supprimés, stocks remis à 0, utilisateur de test supprimé).

Suite de tests complète toujours verte (37 passed / 101 assertions), `vendor/bin/pint --test` propre. Pas de test automatisé dédié à ce CRUD pour l'instant — couverture prévue au sous-point 5.5.

**5.4 — Alerte stock bas** (2026-07-23)

- `database/migrations/2026_07_23_184128_add_low_stock_threshold_to_product_variants_table.php` — colonne `low_stock_threshold` (decimal 12,2, nullable) sur `product_variants`.
- `app/Models/ProductVariant.php` — `low_stock_threshold` ajouté au `#[Fillable]` (contrairement à `current_stock`, c'est une valeur de configuration normale, pas soumise à la même protection) et à son cast `decimal:2`. Nouvelle méthode `isLowStock(): bool` — vrai si un seuil est défini et que `current_stock` est à ou sous ce seuil (comparaison `bccomp`, pas de flottant). Seule source de vérité, réutilisée par les deux Resources ci-dessous.
- `StoreProductRequest`/`UpdateProductRequest` — règle `variants.*.low_stock_threshold` (nullable, numérique, ≥ 0).
- `CreateProductAction`/`UpdateProductAction` — transmettent le seuil à la création/mise à jour des variantes.
- `ProductVariantResource`, `StockVariantResource` — exposent `low_stock_threshold` et `is_low_stock` (calculé côté serveur, comme les libellés — le Vue n'a aucune logique métier à dupliquer).
- `Products/Partials/ProductFormFields.vue` — champ "Alerte stock bas" par variante, à côté du "Seuil gros".
- `Products/Index.vue` — badge orange + ⚠ sur la variante concernée dans la colonne "Variantes".
- `Stock/Index.vue` — ligne surlignée (fond orange clair) + libellé "⚠ Stock bas" dans la liste "Stock actuel" (la page la plus consultée au quotidien, priorité à la visibilité là plutôt que dans le catalogue).

Vérifié : migration + rollback + re-migrate OK. Cas limites de `isLowStock()` testés en tinker (stock < seuil → vrai, stock == seuil → vrai, stock > seuil → faux). Vérifié visuellement en navigateur (seuil 10 / stock 5 sur une variante) : badge ⚠ affiché à la fois dans `/products` et `/stock`. Données de test remises à zéro après coup. Suite de tests complète toujours verte (37 passed / 101 assertions), `vendor/bin/pint --test` propre.

**5.5 — Tests stock** (2026-07-23)

`tests/Feature/StockMovementTest.php` — 8 tests (style PHPUnit classique, cohérent avec le reste du projet) :
- `test_admin_can_record_an_in_movement_and_stock_increases` — POST `/stock` avec `type=in`, vérifie `current_stock` (10 → 35) et la ligne `stock_movements` créée avec le bon `user_id`.
- `test_admin_can_record_an_out_movement_and_stock_decreases` — `type=out`, 30 → 18.
- `test_logisticien_can_record_a_movement` — confirme que le 2e rôle autorisé a bien accès, pas seulement `admin`.
- `test_out_movement_is_rejected_when_it_would_make_stock_negative` — sortie de 50 sur un stock de 10 : erreur de validation sur `quantity`, stock inchangé, aucune ligne `stock_movements` créée (protège l'intégrité même en contournant l'UI).
- `test_vendeur_cannot_access_stock_page` — 403 sur `GET` et `POST /stock` pour un rôle non autorisé.
- `test_guest_is_redirected_to_login`.
- `test_variant_is_flagged_low_stock_at_or_below_threshold` — cas limites de `ProductVariant::isLowStock()` (stock < seuil, stock == seuil, stock > seuil) directement sur le modèle.
- `test_variant_without_threshold_is_never_flagged_low_stock` — pas d'alerte tant qu'aucun seuil n'est configuré.

Les factories `ProductVariantFactory` (créée au point 4.5) et l'astuce `Model::unguarded()` interne aux factories Eloquent permettent de fixer `current_stock`/`low_stock_threshold` directement dans les tests malgré leur absence du `$fillable` — sans affaiblir la protection en dehors des tests.

Vérifié : `php artisan test --filter=StockMovementTest` (8 passed / 25 assertions) puis suite complète (45 passed / 126 assertions). `vendor/bin/pint --test` propre.

**Point 5 (Stock) terminé** — les 5 sous-points sont faits et vérifiés.

---

## Point 6 — Vente / POS (double devise USD/CDF, paiement espèces)

Décisions actées avec l'utilisateur avant de commencer (2026-07-23) :
- **Taux de change** : `config/kolori.php` + `.env` (`EXCHANGE_RATE`) pour l'instant — le point 7 le remplacera par un vrai réglage éditable en base par l'admin.
- **Détail/gros** : piloté par `config('kolori.pricing_mode')` (`automatic`/`manual`, env `PRICING_MODE`) — en mode automatique le seuil de la variante décide seul ; en mode manuel le vendeur choisit, avec une suggestion automatique pré-remplie. Le point 7 devra exposer ce choix comme un vrai réglage (bouton radio) dans la page config.
- **Reçu** : vue HTML imprimable (bouton "Imprimer") **+** téléchargement PDF (bouton "Télécharger en PDF", généré serveur via `barryvdh/laravel-dompdf` — pas de librairie JS lourde côté client, cohérent avec "léger").
- **Accès caisse** : `admin` + `vendeur` uniquement (le `logisticien` gère stock/catalogue, pas les ventes).

### Sous-points

| # | Sous-point | Statut |
|---|------------|--------|
| 6.1 | Migrations (`sales`, `sale_items`, `sale_id` sur `stock_movements`) + `config/kolori.php` + installation `dompdf` | ✅ Fait |
| 6.2 | Modèles Eloquent (`Sale`, `SaleItem`) + Action `CreateSaleAction` (calcul détail/gros, décrément stock via `RecordStockMovementAction` réutilisée, calcul monnaie rendue), transaction atomique | ✅ Fait |
| 6.3 | Interface caisse (POS) Inertia/Vue : panier, double devise en direct, montant reçu/monnaie, accès admin/vendeur | ✅ Fait |
| 6.4 | Reçu : impression navigateur + téléchargement PDF | ✅ Fait |
| 6.5 | Tests : calcul détail/gros (auto+manuel), double devise, décrément stock, refus si insuffisant, contrôle d'accès par rôle | ✅ Fait |

### Journal (rempli au fur et à mesure)

**6.1 — Fondations vente** (2026-07-23)

- `composer require barryvdh/laravel-dompdf` — au passage, `composer update guzzlehttp/guzzle --with-all-dependencies` pour corriger 3 alertes de sécurité moyennes détectées par `composer audit` sur une dépendance transitive de `laravel/framework` (rien à voir avec dompdf) ; `composer audit` propre après coup.
- `config/kolori.php` (nouveau) — `exchange_rate` (défaut 2800, via `EXCHANGE_RATE`) et `pricing_mode` (défaut `automatic`, via `PRICING_MODE`). `.env`/`.env.example` mis à jour. Réglages volontairement temporaires (fichier, pas base de données) — le point 7 les rendra éditables par l'admin sans redéploiement.
- `database/migrations/2026_07_23_191542_create_sales_table.php` — table `sales` : FK `user_id` (restrict), `total_usd`, `exchange_rate` et `total_cdf` figés au moment de la vente (une vente passée ne doit jamais changer de valeur si le taux change ensuite), `payment_currency` (enum `usd`/`cdf`), `amount_tendered`, `change_due`.
- `database/migrations/2026_07_23_191543_create_sale_items_table.php` — table `sale_items` : FK `sale_id` (cascade), `product_variant_id` (restrict), `quantity`, `pricing_tier` (enum `retail`/`wholesale` — traçabilité du prix appliqué), `unit_price` figé, `subtotal`.
- `database/migrations/2026_07_23_191544_add_sale_id_to_stock_movements_table.php` — colonne `sale_id` nullable sur `stock_movements` (nullOnDelete) : chaque mouvement de stock généré par une vente sera tracé jusqu'à elle.

`CLAUDE.md` — MLD mis à jour (règle transverse) : nouvelles entités `SALES`, `SALE_ITEMS`, relations avec `USERS`, `PRODUCT_VARIANTS` et `STOCK_MOVEMENTS` (`sale_id`).

Vérifié : `php artisan migrate` OK, `migrate:rollback --step=3` puis re-`migrate` OK, `config('kolori.exchange_rate')`/`pricing_mode` lus correctement en tinker, suite de tests existante toujours verte (45 passed / 126 assertions), `vendor/bin/pint --test` propre.

**6.2 — Modèles Sale/SaleItem + CreateSaleAction** (2026-07-23)

- `app/Enums/PaymentCurrency.php` (`usd`/`cdf`), `app/Enums/PricingTier.php` (`retail`/`wholesale`) — enums natifs PHP.
- `app/Models/Sale.php` — casts `total_usd`/`exchange_rate`/`total_cdf`/`amount_tendered`/`change_due` en decimal, `payment_currency` en enum, relations `belongsTo(User::class)`, `hasMany(SaleItem::class)` (`items()`), `hasMany(StockMovement::class)`.
- `app/Models/SaleItem.php` — casts `quantity`/`unit_price`/`subtotal` en decimal, `pricing_tier` en enum, relations vers `Sale` et `ProductVariant`.
- `app/Models/StockMovement.php` — `sale_id` ajouté au `#[Fillable]`, relation `belongsTo(Sale::class)`.
- `app/Actions/Stock/RecordStockMovementAction.php` — accepte désormais un `sale_id` optionnel dans le tableau de données, transmis à la création du mouvement (aucun changement de comportement pour les mouvements manuels du point 5, qui n'en passent pas).
- `app/Actions/Sale/CreateSaleAction.php` — `RecordStockMovementAction` injectée par le constructeur (réutilisation directe, pas de duplication de la logique de décrément/refus de stock). Dans une transaction :
  1. Pour chaque ligne : détermine le tarif (`resolveTier()`) — en mode `automatic`, le seuil de la variante décide seul (`suggestedTier()`) ; en mode `manual`, le choix du vendeur est respecté sauf s'il demande le tarif de gros sur une variante qui n'en a pas (repli automatique sur le détail).
  2. Calcule sous-totaux et total en arithmétique décimale exacte (`bcadd`/`bcmul`/`bcsub`, jamais de flottant sur de l'argent) ; le total CDF est arrondi à l'entier (pas de sous-unité utilisée en pratique), contrairement à l'USD qui reste la devise de référence exacte.
  3. Calcule la monnaie à rendre dans la devise de paiement choisie ; refuse (`ValidationException` sur `amount_tendered`) si le montant reçu est insuffisant.
  4. Crée la `Sale`, ses `SaleItem`, puis appelle `RecordStockMovementAction` pour chaque ligne avec `sale_id` renseigné — le refus de stock négatif du point 5 s'applique donc automatiquement à la vente : si une seule ligne dépasse le stock disponible, toute la vente est annulée (rien de partiel).

Vérifié en conditions réelles (`php artisan tinker`, transaction annulée après coup), mode `automatic` : prix détail sous le seuil de gros, prix de gros au seuil atteint, décrément de stock correct cumulatif sur 3 ventes successives, total CDF cohérent avec le taux configuré, refus propre si paiement insuffisant (`ValidationException` sur `amount_tendered`) et si stock insuffisant (réutilisation confirmée du message du point 5), aucune trace en base pour les ventes rejetées. Mode `manual` (testé avec `PRICING_MODE=manual`) : le choix explicite du vendeur est respecté même sous le seuil, la suggestion automatique s'applique si rien n'est précisé, et repli correct sur le détail quand le gros est demandé sur une variante sans prix de gros. Suite de tests complète toujours verte (45 passed / 126 assertions), `vendor/bin/pint --test` propre.

**6.3 — Interface caisse (POS)** (2026-07-23)

Une seule page (`/pos`), même philosophie que la page Stock (5.3) : rien à créer/éditer séparément, tout se passe sur un seul écran pensé mobile-first.

- `app/Http/Requests/Sale/StoreSaleRequest.php` — validation `items` (tableau, min 1, chaque ligne : variante existante, quantité > 0, tarif optionnel parmi l'enum), `payment_currency`, `amount_tendered`.
- `app/Http/Resources/PosVariantResource.php` — expose uniquement ce qu'il faut pour le calcul côté client (libellé, prix détail/gros, seuil, stock courant) — le serveur reste la seule autorité pour la vente réelle, ces données ne servent qu'à l'aperçu live.
- `app/Http/Controllers/PosController.php` — `index` (variantes actives + réglages `exchangeRate`/`pricingMode` en props), `store` (délègue à `CreateSaleAction`, message flash avec le récapitulatif).
- `app/Actions/Sale/CreateSaleAction.php` — erreur de stock insuffisant réindexée vers la bonne ligne (`items.{index}.quantity`) au lieu d'un message générique, pour que le panier affiche l'erreur au bon endroit quand plusieurs lignes sont en cours de vente.
- `routes/web.php` — `GET/POST /pos` sous `['auth','verified','role:admin|vendeur']` (le logisticien n'y a pas accès — cohérent avec la séparation des rôles).
- `resources/js/Pages/Pos/Index.vue` — recherche produit (filtre local, pas de requête serveur par frappe), panier avec stepper +/-, bascule détail/gros automatique en direct (badge "Prix de gros" affiché quand actif) ou boutons manuels si `pricingMode === 'manual'`, total et monnaie à rendre toujours affichés en USD **et** CDF simultanément, gros boutons de choix de devise (mêmes codes couleur que le point 5).
- `resources/js/Layouts/AuthenticatedLayout.vue` — lien nav "Caisse" (admin/vendeur), placé en premier dans le menu — c'est l'écran le plus utilisé au quotidien.

🐛 **Bug réel trouvé et corrigé** : `PosController::index()` faisait `->where('is_active', true)` après une jointure `products`/`product_variants` (les deux tables ayant cette colonne) → `SQLSTATE[42702] Ambiguous column` sous PostgreSQL (le projet tourne désormais sur PostgreSQL, pas MySQL). Corrigé en qualifiant `product_variants.is_active`. `StockMovementController` n'avait pas ce problème (son `where('is_active', true)` reste dans la fermeture `whereHas`, donc scopé à `products` seul, sans ambiguïté).

Vérifié en conditions réelles en navigateur (viewport mobile 375×812, produits de démo avec stock réattribué pour le test) : recherche + ajout au panier, bascule automatique détail/gros en direct (badge affiché) en modifiant la quantité, calcul du total et de la monnaie à rendre en direct, vente validée avec succès (message flash récapitulatif), vérifié en base (`Sale`, `SaleItem` avec le bon tarif, `StockMovement` lié via `sale_id`, stock décrémenté 100→95). Accès confirmé : `vendeur` autorisé (200), `logisticien` refusé (403). Données de test nettoyées après coup (⚠️ un mouvement de stock créé par l'utilisateur lui-même a été supprimé par erreur dans ce nettoyage — signalé et confirmé sans importance). Suite de tests complète toujours verte (45 passed / 126 assertions), `vendor/bin/pint --test` propre.

**6.4 — Reçu (impression + PDF)** (2026-07-23)

- `app/Http/Resources/SaleItemResource.php`, `SaleResource.php` — même principe que le catalogue/stock : libellé et formatage calculés côté serveur, le Vue n'affiche que des chaînes/nombres déjà prêts.
- `app/Http/Controllers/SaleController.php` — `receipt()` (page Inertia) et `receiptPdf()` (téléchargement, via `Barryvdh\DomPDF\Facade\Pdf::loadView(...)->download(...)`).
- `resources/views/receipts/sale.blade.php` — gabarit Blade dédié à dompdf (CSS inline minimal, pas de classes Tailwind — dompdf ne consomme pas le pipeline Vite), même contenu que la page Vue mais rendu indépendamment côté serveur.
- `resources/js/Pages/Sales/Receipt.vue` — page autonome (sans `AuthenticatedLayout`, pas de nav à cacher à l'impression) : reçu centré façon ticket de caisse, boutons "Imprimer" (`window.print()`) et "Télécharger en PDF" (lien direct vers la route PDF), tous deux masqués à l'impression (`print:hidden`, variante Tailwind native).
- `routes/web.php` — `GET /sales/{sale}/receipt` et `GET /sales/{sale}/receipt.pdf` sous `['auth','verified','role:admin|vendeur']` (même accès que la caisse).
- `app/Http/Controllers/PosController.php` — après une vente réussie, redirection directe vers `sales.receipt` (au lieu d'un simple message flash sur `/pos`) : le vendeur voit immédiatement le reçu, prêt à imprimer ou télécharger.
- `composer require barryvdh/laravel-dompdf` + `config/dompdf.php` publié (sous-point 6.1).

Vérifié en conditions réelles en navigateur : vente validée → redirection automatique vers `/sales/{id}/receipt`, contenu correct (produit, quantité, sous-total, totaux USD/CDF, devise reçue, montant reçu, monnaie rendue, taux appliqué). Téléchargement PDF vérifié via `fetch()` direct : statut 200, `Content-Type: application/pdf`, `Content-Disposition` avec le bon nom de fichier, en-tête binaire `%PDF-1.7` confirmé (fichier réellement valide, pas juste un statut 200 vide). Accès aux deux routes refusé (403) pour un `logisticien`. Données de test nettoyées de façon ciblée cette fois (uniquement les mouvements avec `reason = 'Vente'` créés pendant ce test, pas de suppression en bloc). Suite de tests complète toujours verte (45 passed / 126 assertions), `vendor/bin/pint --test` propre.

**6.5 — Tests vente/POS** (2026-07-23)

`tests/Feature/SaleTest.php` — 11 tests (style PHPUnit classique) :
- `test_admin_can_create_a_sale_at_retail_price_below_wholesale_threshold` — qty=2 sous le seuil de 5 → tarif détail, stock décrémenté (50→48), monnaie rendue correcte.
- `test_wholesale_price_applies_automatically_at_the_threshold` — qty=5 (mode `automatic`) → tarif de gros appliqué sans action du vendeur.
- `test_manual_pricing_mode_respects_the_vendors_choice` — mode `manual` (`config(['kolori.pricing_mode' => 'manual'])` au sein du test), qty=2 sous le seuil mais le vendeur force `wholesale` → respecté.
- `test_total_in_cdf_uses_the_configured_exchange_rate` — taux surchargé à 2500, vérifie `exchange_rate`/`total_cdf`/`change_due` figés cohérents (paiement en CDF).
- `test_sale_is_rejected_when_amount_tendered_is_insufficient` — erreur sur `amount_tendered`, aucune vente créée, stock inchangé.
- `test_sale_is_rejected_when_stock_is_insufficient` — erreur sur `items.0.quantity` (bonne ligne indexée), aucune vente ni mouvement créés, stock inchangé.
- `test_vendeur_can_create_a_sale` — confirme l'accès du 2e rôle autorisé.
- `test_logisticien_cannot_access_the_pos` — 403 sur `GET`/`POST /pos`.
- `test_guest_is_redirected_to_login`.
- `test_receipt_is_accessible_to_admin_and_vendeur_but_not_logisticien` — couvre aussi l'accès au reçu (6.4), pas seulement la création de vente.
- `test_receipt_pdf_download_returns_a_pdf` — vérifie le `Content-Type` de la réponse.

🐛 Une erreur de calcul dans ma propre assertion (monnaie rendue attendue à tort à 43.00 au lieu de 20.00) a été détectée et corrigée avant validation finale — pas un bug applicatif, une erreur de calcul manuel dans le test lui-même.

Vérifié : `php artisan test --filter=SaleTest` (11 passed / 36 assertions) puis suite complète (56 passed / 162 assertions). `vendor/bin/pint --test` propre.

**Point 6 (Vente/POS) terminé** — les 5 sous-points sont faits et vérifiés.

---

## Points suivants

Non décomposés pour l'instant (seront détaillés en sous-points quand ils deviendront le point courant), voir §4/§10 de `CLAUDE.md` pour le contenu prévu. Prochain point (après le 6) : **7 — Page config**.

⚠️ **Point à ne pas oublier pour le point 7** (question posée le 2026-07-23) : aucune vue construite jusqu'ici (Catalogue, Utilisateurs, Stock, Caisse, Reçu) n'est bilingue — tout le texte d'interface est en français en dur, sans système de traduction. Seules les données métier (`name_fr`/`name_en`) le sont. Avant de construire le sélecteur de langue dans la page config, le point 7 devra d'abord : (1) mettre en place un système de traduction (léger, pas forcément `vue-i18n` complet — cohérent avec la philosophie du projet), (2) extraire toutes les chaînes en dur des vues existantes **et** des messages serveur (ex. "Stock insuffisant...", "Vente enregistrée...") vers des fichiers de traduction, **avant** de construire le switch lui-même.
