# Demo Application

## Docker (recommended)

Start all services with one command:

```sh
docker compose up --build
```

This starts 3 containers:

| Service | Description |
|---------|-------------|
| **nginx** | Web server (port 3000) |
| **php** | PHP 7.3-fpm (Amazon Linux 2) with Composer, Node.js, Imagick |
| **db** | MariaDB 10.6 |

On first startup, the php container automatically runs:
- `composer install`
- `npm install && npm run build` (frontend assets)
- Directory setup and permissions
- `.env` file creation from `.env.sample`

Open `http://localhost:3000/` — default credentials: `robin@example.com` / `password`

To reset the database:

```sh
docker compose down -v
docker compose up --build
```

### File structure

```
demo/
├── docker-compose.yml
├── docker/
│   ├── Dockerfile        # PHP 7.3-fpm on Amazon Linux 2
│   ├── nginx.conf        # Nginx config (port 3000, CodeIgniter routing)
│   └── init.sql          # DB schema + seed data
├── application/          # CodeIgniter application
├── client/               # Frontend source (webpack)
└── public/               # Document root
```

### Live code editing

Changes to `../src/` are immediately reflected inside the container via volume mount — no rebuild needed.

## Manual setup (without Docker)

1. Install dependencies.
    ```sh
    composer install
    ```
1. Create an `.env` file.
    ```sh
    cp .env.sample .env
    ```
1. Set up permissions.
    ```sh
    sudo chmod -R 755 public/upload application/{logs,cache,session}
    sudo chown -R nginx:nginx public/upload application/{logs,cache,session}
    ```
1. Set up Nginx.
    Copy [nginx.sample.conf](../nginx.sample.conf) to `/etc/nginx/conf.d/sample.conf` and restart:
    ```sh
    sudo systemctl restart nginx
    ```
1. Import the database (MySQL or MariaDB).
    ```sh
    mysql -u root -p < ../skeleton/init.sql
    ```
1. Build frontend assets.
    ```sh
    cd client && npm install && npm run build
    ```
1. Open `http://{server IP}:3000/`

## Screenshots

Default credentials: `robin@example.com` / `password`

<p align="left">
  <img alt="Sign In" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/sign-in.png" width="45%">
  <img alt="User List" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/list-of-users.png" width="45%">
</p>
<p align="left">
  <img alt="Update User" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/update-user.png" width="45%">
  <img alt="Personal Settings" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/personal-settings.png" width="45%">
</p>
<p align="left">
  <img alt="Page Not Found" src="https://raw.githubusercontent.com/shumatsumonobu/codeigniter-extension/master/screencaps/page-not-found.png" width="45%">
</p>
