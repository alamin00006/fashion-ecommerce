# Local WordPress preview

This project runs WordPress, MySQL 8.0, and WooCommerce in Docker. The theme folder is bind-mounted, so edits to the theme appear in the local site without rebuilding an image. The setup container upgrades the local WordPress core to 7.1.3 from the bundled official release, installs WooCommerce from its bundled official archive, activates the theme, sets BDT currency, and adds three local preview products with the included banner images. It also enables cash on delivery and a flat Bangladesh preview shipping rate.

## Start

From this project folder:

```powershell
docker compose up -d
```

Wait for `wordpress-setup` to finish, then open <http://localhost:8081>.

Admin: <http://localhost:8081/wp-admin>

- Username: `admin`
- Password: `overview-local-only`

These credentials are for the local-only Docker preview. Change them before exposing this site outside your computer.

## Stop / reset

```powershell
docker compose down
```

This stops the containers and keeps the database and WordPress files. To erase the local WordPress install and start fresh:

```powershell
docker compose down -v
docker compose up -d
```

The Docker database is only for this local preview. When you upload the theme to your hosting account, it uses the WordPress installation and database configured by that host; the theme ZIP contains no database or local credentials.
