# Kolori

Application de gestion pour magasins de peinture (RDC) — catalogue multi-unités, stock, vente/POS bi-devise (USD/CDF), multi-magasin. Version 1 solide et livrable rapidement, socle conceptuel vers `painting-erp` (vision long terme).

Le plan complet et les conventions du projet sont dans [`CLAUDE.md`](./CLAUDE.md).

## Stack

Laravel 13 (PHP 8.3) · Inertia · Vue 3 · Tailwind · MySQL/MariaDB · Breeze (auth) · Spatie Permission (rôles)

## Installation

```bash
composer install
npm install

cp .env.example .env
php artisan key:generate
# renseigner DB_DATABASE / DB_USERNAME / DB_PASSWORD dans .env

php artisan migrate
php artisan db:seed   # rôles admin/vendeur/logisticien + un admin de dev + unités/emballages/catégories catalogue

npm run dev            # ou npm run build en prod
php artisan serve
```

## Avancement

- [x] Socle Laravel + Breeze (auth Vue/Inertia/Tailwind)
- [x] Rôles (Spatie Permission) : `admin`, `vendeur`, `logisticien`
- [x] Clés UUID sur `users` et les tables de permissions
- [x] Catalogue produits multi-unités/emballages
- [x] Stock (quantités + mouvements, alerte stock bas)
- [ ] Vente / POS (double devise USD/CDF, paiement espèces)
- [ ] Page config (magasin, branding, langue, taux de change, TVA)
- [ ] Multi-magasin (table `stores` + scoping)
- [ ] Tableau de bord (ventes du jour, stock bas)
- [ ] Reçus PDF / impression
- [ ] PWA + queue offline
- [ ] Déploiement (PlanetHoster / VPS)

Détail des jalons S1/S2/S3 : voir [`CLAUDE.md`](./CLAUDE.md#10-jalons-de-livraison).

## License

MIT — voir [`LICENSE`](./LICENSE).
