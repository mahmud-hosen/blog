# Docker: Beginner to Advanced Guide
### Based on a Real Laravel Project (blog)

---

> **Goal:** Understand Docker deeply for real-world Laravel + DevOps development.  
> **Approach:** Every concept is explained using your actual `Dockerfile` and `docker-compose.yml`.

---

## Table of Contents

1. [What is Docker? (The Big Picture)](#1-what-is-docker-the-big-picture)
2. [Why We Use a Dockerfile](#2-why-we-use-a-dockerfile)
3. [Why We Use docker-compose.yml](#3-why-we-use-docker-composeyml)
4. [Dockerfile — Line-by-Line Breakdown](#4-dockerfile--line-by-line-breakdown)
5. [docker-compose.yml — Line-by-Line Breakdown](#5-docker-composeyml--line-by-line-breakdown)
6. [docker build vs docker run vs docker compose](#6-docker-build-vs-docker-run-vs-docker-compose)
7. [Daily-Use Docker Commands](#7-daily-use-docker-commands)
8. [Best Practices](#8-best-practices)

---

## 1. What is Docker? (The Big Picture)

### The Analogy

> Think of Docker like a **shipping container** for software.
>
> Before shipping containers existed, loading cargo onto ships was chaos — every item had a different shape, size, and requirement. Shipping containers standardized everything: one box, any ship, any port.
>
> Docker does the same for software. Your app, its dependencies, its configuration — all packed into one standardized "container" that runs **identically** on any machine.

### The Real Problem Docker Solves

Without Docker, a developer often says:

> *"It works on my machine!"*

The tester replies:

> *"Well, your machine isn't the server."*

This happens because different machines have different:
- PHP versions (`8.1` vs `8.2`)
- MySQL versions (`5.7` vs `8.0`)
- OS libraries (`libpng`, `libxml`, etc.)
- Environment variables

Docker fixes this by bundling everything your app needs into a portable, isolated unit called a **container**.

### Key Concepts (Quick Glossary)

| Term | Real-World Analogy | What it means |
|---|---|---|
| **Image** | A recipe / blueprint | A read-only template that defines what's inside a container |
| **Container** | A running kitchen | A live instance created from an image |
| **Dockerfile** | The recipe instructions | A text file that tells Docker how to build an image |
| **docker-compose.yml** | The restaurant layout plan | Defines and orchestrates multiple containers together |
| **Volume** | An external hard drive | Persistent storage that survives container restarts |
| **Network** | A private WiFi network | Lets containers talk to each other securely |

---

## 2. Why We Use a Dockerfile

### Purpose

A `Dockerfile` is a **script of instructions** that Docker reads to build a custom image. It automates the setup of your application environment so that no human needs to manually install PHP, extensions, Composer, etc.

### Real-Life Use Case (Your Laravel Blog)

Without a Dockerfile, every developer joining your team would need to:

1. Install PHP 8.2
2. Install `pdo`, `pdo_mysql`, `mbstring`, `gd`, `sockets`, `bcmath`... manually
3. Install Composer
4. Set correct permissions on `storage/` and `bootstrap/cache/`
5. Hope they didn't miss a step

**With your Dockerfile**, they just run:

```bash
docker compose up --build
```

And Docker does all of that automatically, identically, every time.

### When to Write a Dockerfile

- When no existing public image fits your needs exactly
- When you need custom PHP extensions (like `pdo_mysql`, `gd`, `sockets`)
- When you need to pre-install tools (Composer, Node.js, npm)
- When you need to set permissions or copy configuration files

---

## 3. Why We Use docker-compose.yml

### Purpose

`docker-compose.yml` is the **orchestration file** — it describes your entire application stack and how all the pieces connect.

Your Laravel blog needs:
- A **PHP-FPM container** to run Laravel (`app`)
- An **Nginx container** to serve HTTP requests (`web`)
- A **MySQL container** for the database (`mysql`)
- A **RabbitMQ container** for message queues (`rabbitmq`)

Without Compose, you'd need to run 4 separate `docker run` commands with long flags, in the right order, with the right network settings. That's error-prone and hard to remember.

### docker run vs docker compose

| Scenario | Use |
|---|---|
| Quickly test a single image | `docker run` |
| Run your full app with multiple services | `docker compose` |
| CI/CD pipeline for a microservice | `docker run` |
| Local development environment | `docker compose` |
| Production with Kubernetes | Neither — use K8s manifests |

**Example: the difference in practice**

Running MySQL with plain `docker run`:
```bash
docker run -d \
  --name blog_mysql_db \
  -e MYSQL_ROOT_PASSWORD=12345678 \
  -e MYSQL_DATABASE=blog \
  -e MYSQL_USER=mahmud \
  -e MYSQL_PASSWORD=12345678 \
  -p 3307:3306 \
  -v mysql_data:/var/lib/mysql \
  --network laravel \
  mysql:8.0
```

With `docker-compose.yml`, you just define it once and run `docker compose up`. Much cleaner.

---

## 4. Dockerfile — Line-by-Line Breakdown

Here is your complete `Dockerfile` with every line explained:

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 1: Comment
# ─────────────────────────────────────────────────────────────
# Dockerfile
```
> Lines starting with `#` are comments. They are ignored by Docker.
> Use them to document your intent for other developers (and future you).

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 2: FROM
# ─────────────────────────────────────────────────────────────
FROM php:8.2-fpm
```
> **`FROM`** Tells Docker -->  Start building my image from this  **base image** 

>     Think like:
>         You are not starting from scratch
>         You are using an existing image as a foundation
> - `php` — This is the image name - Use the official PHP image from Docker Hub
> - `8.2` — This is the version tag - PHP version 8.2
> - `fpm` — PHP-FPM (FastCGI Process Manager), the variant designed to work with Nginx . PHP-FPM is used to run PHP with web servers ( Nginx ,  Apache HTTP Server) . Instead of running PHP directly Handles PHP requests efficiently , Works as a separate service
>
---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Lines 4-17: RUN (System Dependencies)
# ─────────────────────────────────────────────────────────────
# Install system dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    curl \
    git \
    npm \
    nodejs
```
> **`RUN`** Executes commands inside the container while building image. Like running commands in Ubuntu terminal
>
> Let's break down each part:
>
> | Command/Package | Why it's needed |
> |---|---|
> | `apt-get update` | Refreshes the package list from Debian repositories (the base OS is Debian) |
> | `apt-get install -y` | Installs packages. `-y` means "yes to all prompts" (non-interactive) |
> | `build-essential` | C/C++ compiler tools needed to compile some PHP extensions |
> | `libpng-dev` | PNG image library — required for the `gd` PHP extension (image processing) |
> | `libjpeg62-turbo-dev` | JPEG library — also required for `gd` |
> | `libfreetype6-dev` | Font rendering library — required for `gd` (text on images) |
> | `libonig-dev` | Oniguruma regex library — required for `mbstring` PHP extension |
> | `libxml2-dev` | XML parsing library — required for PHP's XML extensions |
> | `zip` / `unzip` | Zip tools — Composer uses these to unzip packages |
> | `curl` | HTTP tool — used by Composer and various scripts |
> | `git` | Version control — Composer uses git to fetch some packages |
> | `npm` / `nodejs` | JavaScript runtime and package manager — for frontend assets (Vite/Mix) |
>
> **Why use `&&` ?**  
>     Run next command ONLY if previous command succeeds
>

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 20: RUN (PHP Extensions)
# ─────────────────────────────────────────────────────────────
# Install PHP extensions including sockets
RUN docker-php-ext-install pdo pdo_mysql mbstring exif pcntl bcmath gd sockets
```
> **`docker-php-ext-install`** is a helper script provided inside the official PHP Docker image. ( Install PHP extensions easily inside Docker )
> Why we use it? 
      Base image (php:8.2-fpm) does NOT include all extensions. 
> | Extension | What it does |
> |---|---|
> | `pdo` | PHP Data Objects (core DB layer) — base interface for database access |
> | `pdo_mysql` | MySQL driver for PDO — lets Laravel talk to MySQL |
> | `mbstring` | Multibyte string support — UTF-8 (Bangla, Unicode text) , Required by Laravel for UTF-8 handling , Laravel validation & string functions|
> | `exif` | Read image metadata — used by Intervention Image, etc. |
> | `pcntl` | Process control (CLI only) - Used for queues, background jobs, workers (Laravel queue worker) |
> | `bcmath` | High precision math — Used for financial calculations , large numbers |
> | `gd` | Image processing library — Used for - resize image , create thumbnails , manipulate images  |
> | `sockets` | Low-level networking  — used for real-time systems , WebSocket servers , advanced networking , required for RabbitMQ (AMQP) connections |
>

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 23: COPY (Composer binary)
# ─────────────────────────────────────────────────────────────
# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
```
> **`COPY --from=`** It copies a file from a different image without pulling the entire image into your final image.
>
> - `COPY` - It copies a file from a different image without pulling the entire image into your final image. Copies files into your image 
> - `--from ` - This is used in multi-stage builds , Copy something from another image or stage
> - `composer:latest` — This is another Docker image from Docker Hub , the official Composer Docker image
> - `/usr/bin/composer` (source) — where Composer's binary lives inside that image
> - `/usr/bin/composer` (destination) — where to place it in your image

    👉 Meaning step-by-step:

            Take the image → composer:latest
            Go inside that image → /usr/bin/composer
            Copy that file
            Paste into your image → /usr/bin/composer

    ❌ Without this ( Not Recommand )
        You would do:  RUN apt-get install composer

        Problems:

              1) Adds many unnecessary dependencies
              2) Bigger image size
              3) Slower build

    ✅ With COPY --from

            ✔ Only copies one file (composer binary)
            ✔ No extra packages
            ✔ Smaller image
            ✔ Faster build

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 26: WORKDIR
# ─────────────────────────────────────────────────────────────
# Set working directory

WORKDIR /var/www

      1) Set the current working directory inside the container, 
      2) Sets the default directory for all subsequent instructions (`COPY`, `RUN`, `CMD`, etc.).
      3) From this point forward, when Docker runs commands, it runs them from `/var/www` inside the container.
      4) It also **creates the directory** if it doesn't exist.
      5) Best Practice:Always set WORKDIR explicitly. Never rely on the default root `/`

```      


---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 29: COPY (Application Code)
# ─────────────────────────────────────────────────────────────
COPY . .
```
> **`COPY`** copies files from your **host machine** (your Mac/PC) into the container image.
>
> - First `.` (source) — current directory on your host machine (your Laravel project root)
> - Second `.` (destination) — current directory **inside the container**, which is `/var/www` (set by `WORKDIR`)
> - So this copies your entire Laravel project into `/var/www` inside the image.
> - প্রথম . (source) and দ্বিতীয় . (destination) , local project-এর সব file → container-এর WORKDIR-এ কপি করো  so COPY (local folder) → /var/www
> - For development, you override this with a **volume mount** in `docker-compose.yml` so live code changes are reflected without rebuilding It happen for volume mount.

```
  Example: File: docker-compose.yml

    services:
    app:
      volumes:
        - .:/var/www

```

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 31: RUN (Composer Install)
# ─────────────────────────────────────────────────────────────
RUN composer install
```
> Runs `composer install` inside the container during the build, which reads `composer.json` and installs all PHP dependencies into the `vendor/` directory.
>
> **This happens after `COPY . .`** because Composer needs your `composer.json` file to be present in `/var/www` first.
>
> **Production tip:** For production images, use:
> ```dockerfile
> RUN composer install --no-dev --optimize-autoloader
> ```
> - `--no-dev` skips development-only packages (like PHPUnit)
> - `--optimize-autoloader` generates a faster class map

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Lines 34-35: RUN (Permissions)
# ─────────────────────────────────────────────────────────────
# Fix permissions for Laravel storage and cache folders
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache
```
> Laravel writes logs, cache, sessions, and compiled views to `storage/` and `bootstrap/cache/`.  
> The PHP-FPM process runs as the `www-data` user inside the container, so these folders must be owned and writable by that user.
>
> | Command | What it does |
> |---|---|
> | `chown -R www-data:www-data` | Recursively sets the owner and group to `www-data` |
> | `chmod -R 775` | Recursively sets permissions: owner=rwx, group=rwx, others=rx |
>
> **Without this:** Laravel throws a "permission denied" error when trying to write logs or cache files.

---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 40: EXPOSE
# ─────────────────────────────────────────────────────────────
EXPOSE 9000
```
> **`EXPOSE`** is documentation — it tells Docker (and other developers) that this container listens on port `9000` internally.
>
> **Important:** `EXPOSE` does NOT actually publish the port to your host machine.

```  
      1) Port `9000` is the default PHP-FPM port. Nginx will connect to this port to forward PHP requests.
      2) PHP-FPM listens on port 9000
      3) To actually publish a port, you use `-p` in `docker run` or `ports:` in `docker-compose.yml`.



What is port 9000?

    👉 In php:8.2-fpm, PHP runs using PHP-FPM

      1) PHP-FPM listens on port 9000
      2) It waits for requests from web servers like:
          Nginx
          Apache HTTP Server

```
---

```dockerfile
# ─────────────────────────────────────────────────────────────
# Line 41: CMD
# ─────────────────────────────────────────────────────────────
CMD ["php-fpm"]
```
> **`CMD`** defines the **default command** that runs when a container starts from this image.
>
> `["php-fpm"]` starts the PHP FastCGI Process Manager, which listens on port `9000` for incoming PHP execution requests from Nginx.
>
> **`CMD` vs `RUN`:**
> | Instruction | When it runs | Purpose |
> |---|---|---|
> | `RUN` | At **build time** | Sets up the image (install packages, etc.) |
> | `CMD` | At **runtime** (when container starts) | The main process your container runs |
>
> **Note:** There should only be **one `CMD`** per Dockerfile. If you list multiple, only the last one takes effect.

---

## 5. docker-compose.yml — Line-by-Line Breakdown

Here is your complete `docker-compose.yml` with every line explained:

```yaml
# ─────────────────────────────────────────────────────────────
# Line 1: Version
# ─────────────────────────────────────────────────────────────
version: '3.8'
```
> Specifies the **Docker Compose file format version**. Version `3.8` is a mature, widely-supported version that works with Docker Engine 19.03+.
>
> This version determines which features and syntax are available (like named volumes, healthchecks, deploy configs, etc.).

---

```yaml
# ─────────────────────────────────────────────────────────────
# Line 3: services
# ─────────────────────────────────────────────────────────────
services:
```
> **`services:`** is the top-level key that lists all the containers in your application. Each item under `services:` becomes one container.
>
> Your project has 4 services: `app`, `web`, `mysql`, `rabbitmq`.

---

### Service 1: `app` (Laravel / PHP-FPM)

```yaml
  app:
    build:
      context: .
      dockerfile: Dockerfile
    container_name: blog_laravel_app
    volumes:
      - .:/var/www
    networks:
      - laravel
```

| Line | Key | Explanation |
|---|---|---|
| `app:` | Service name | Service name or container name |
| `build:` | Build config | Tells Compose to build a custom image (instead of pulling from Docker Hub) |
| `context: .` | Build context | The directory Docker sends to the build daemon — `.` means current project root |
| `dockerfile: Dockerfile` | Dockerfile path | Which Dockerfile to use (explicit, even though `Dockerfile` is the default) |
| `container_name: blog_laravel_app` | Container name | Gives the running container a human-readable name (instead of a random hash) |
| `volumes: - .:/var/www` | Bind mount | Maps your local project folder (`.`) to `/var/www` inside the container. **Live sync** — code changes on your Mac immediately reflect inside the container |
| `networks: - laravel` | Network | Connects this container to the custom `laravel` network so it can talk to Nginx and MySQL |

> **Why use a volume here if the Dockerfile already does `COPY . .`?**  
> The `COPY` bakes code into the image at build time — it's a snapshot.  
> The volume mount **overrides** that at runtime with your live code.  
> This is the standard development workflow: `COPY` for production images, volume mounts for development.

---

### Service 2: `web` (Nginx)

```yaml
  web:
    image: nginx:alpine
    container_name: blog_nginx_web
    ports:
      - "8086:80"
    volumes:
      - .:/var/www
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
    depends_on:
      - app
    networks:
      - laravel
```

| Line | Key | Explanation |
|---|---|---|
| `image: nginx:alpine` | Image | Pulls the official Nginx image based on Alpine Linux (very small, ~5MB vs ~130MB for the Debian variant) |
| `container_name: blog_nginx_web` | Container name | Human-readable name for the Nginx container |
| `ports: - "8086:80"` | Port mapping | `HOST_PORT:CONTAINER_PORT`. Opens port `8086` on your Mac. Visiting `http://localhost:8086` in your browser sends traffic to Nginx's port `80` inside the container |
| `volumes: - .:/var/www` | Bind mount | Nginx needs access to your Laravel project files to serve static assets (CSS, JS, images) |
| `- ./nginx/default.conf:/etc/nginx/conf.d/default.conf` | Config mount | Replaces Nginx's default config with your custom one (which knows about PHP-FPM on port 9000) |
| `depends_on: - app` | Dependency | Tells Compose to start the `app` container **before** this one. Note: this only controls **start order**, not readiness — Nginx won't wait for PHP-FPM to be fully ready |
| `networks: - laravel` | Network | Same network as `app`, so Nginx can forward PHP requests to `blog_laravel_app:9000` |

> **How does Nginx communicate with PHP?**  
> Your `nginx/default.conf` file contains a `fastcgi_pass app:9000;` directive. Because both `web` and `app` are on the `laravel` network, Nginx can reach the PHP container by its service name `app` on port `9000`.

> **Port mapping analogy:** Think of it like a hotel room. Port `8086` is the room number on the front door (your Mac). Port `80` is the internal room number (inside the container). The hotel receptionist (Docker) redirects traffic from the front door to the right room.

---

### Service 3: `mysql` (Database)

```yaml
  mysql:
    image: mysql:8.0
    container_name: blog_mysql_db
    ports:
      - "3307:3306"
    environment:
      MYSQL_ROOT_PASSWORD: 12345678
      MYSQL_DATABASE: blog
      MYSQL_USER: mahmud
      MYSQL_PASSWORD: 12345678
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - laravel
```

| Line | Key | Explanation |
|---|---|---|
| `image: mysql:8.0` | Image | Pulls MySQL version 8.0 from Docker Hub |
| `container_name: blog_mysql_db` | Container name | Human-readable name |
| `ports: - "3307:3306"` | Port mapping | Maps host port `3307` to container's MySQL port `3306`. Use `3307` (not the default `3306`) to avoid conflicts if you have MySQL installed locally |
| `environment:` | Env vars | Variables injected into the container at runtime. MySQL reads these to configure itself on first startup |
| `MYSQL_ROOT_PASSWORD` | Root password | Sets the password for the `root` MySQL superuser |
| `MYSQL_DATABASE` | Database name | Creates a database named `blog` automatically on first boot |
| `MYSQL_USER` | New user | Creates a new MySQL user named `mahmud` |
| `MYSQL_PASSWORD` | User password | Password for the `mahmud` user |
| `volumes: - mysql_data:/var/lib/mysql` | Named volume | **Critical.** MySQL stores all data in `/var/lib/mysql`. Mounting a named volume here means your database data **persists** even if you stop or remove the container |
| `networks: - laravel` | Network | Connects MySQL to the Laravel network so `app` can connect to it using hostname `mysql` |

> **Named volume vs bind mount:**
> - **Bind mount** (`.:/var/www`) — you control the path on your host; used for code
> - **Named volume** (`mysql_data:/var/lib/mysql`) — Docker manages the storage location; used for databases and persistent data you don't need to access directly

> **In your Laravel `.env`:**
> ```env
> DB_HOST=mysql        # ← service name, not localhost!
> DB_PORT=3306         # ← internal container port, not 3307
> DB_DATABASE=blog
> DB_USERNAME=mahmud
> DB_PASSWORD=12345678
> ```

---

### Service 4: `rabbitmq` (Message Queue)

```yaml
  rabbitmq:
    image: rabbitmq:3-management
    container_name: blog_rabbitmq
    ports:
      - "5672:5672"
      - "15672:15672"
    environment:
      RABBITMQ_DEFAULT_USER: guest
      RABBITMQ_DEFAULT_PASS: guest
    networks:
      - laravel
```

| Line | Key | Explanation |
|---|---|---|
| `image: rabbitmq:3-management` | Image | RabbitMQ 3 with the **Management Plugin** pre-installed (gives you a web UI) |
| `ports: - "5672:5672"` | AMQP port | The main messaging protocol port. Your Laravel app connects here to publish/consume messages |
| `ports: - "15672:15672"` | Management UI port | Visit `http://localhost:15672` to see the RabbitMQ admin dashboard |
| `RABBITMQ_DEFAULT_USER/PASS` | Credentials | Sets the default login for both the AMQP protocol and the web UI |
| `networks: - laravel` | Network | On the same network so `app` can connect to `rabbitmq:5672` |

> **Why RabbitMQ?** Laravel uses Queues to handle background jobs (sending emails, processing payments, etc.) asynchronously. RabbitMQ is a production-grade message broker that can power these queues.

---

### Networks

```yaml
networks:
  laravel:
```

> Defines a **custom Docker network** named `laravel`.
>
> When services share a network, they can reach each other **by service name** (e.g., `app`, `mysql`, `rabbitmq`) as if they were hostnames. Docker handles the internal DNS.
>
> **Without a custom network:** containers can't communicate with each other by name.  
> **With it:** `app` can connect to `mysql:3306` and `rabbitmq:5672` seamlessly.
>
> The empty value (`laravel:` with nothing under it) uses Docker's default bridge network driver, which is fine for most use cases.

---

### Volumes

```yaml
volumes:
  mysql_data:
```

> Declares a **named volume** called `mysql_data` at the top level.
>
> Named volumes are managed by Docker and stored in Docker's internal storage area on your host machine (usually `/var/lib/docker/volumes/` on Linux or in a VM on Mac).
>
> You must declare top-level volumes here for any named volume used in a service.
>
> **Why is this important?**  
> If you run `docker compose down`, your containers are removed — but the `mysql_data` volume remains.  
> Your database data is safe.  
> Only `docker compose down -v` would delete volumes (dangerous — use with care).

---

## 6. `docker build` vs `docker run` vs `docker compose`

### Mental Model

```
Dockerfile  ──(docker build)──▶  Image  ──(docker run)──▶  Container
                                                 ▲
docker-compose.yml  ──(docker compose up)────────┘
                    (builds + runs all services at once)
```

### `docker build` — Creating an Image

```bash
docker build -t blog-app:latest .
```

| Part | Meaning |
|---|---|
| `docker build` | Build command |
| `-t blog-app:latest` | Tag the image with name `blog-app` and tag `latest` |
| `.` | Build context — send current directory to Docker daemon |

> **When to use:** When you want to create or update a custom image. Run this after changing your `Dockerfile`.

---

### `docker run` — Starting a Single Container

```bash
docker run -d -p 8086:80 --name my-nginx nginx:alpine
```

| Part | Meaning |
|---|---|
| `docker run` | Run command |
| `-d` | Detached mode (run in background) |
| `-p 8086:80` | Map host port 8086 to container port 80 |
| `--name my-nginx` | Give the container a name |
| `nginx:alpine` | The image to use |

> **When to use:** Quick one-off testing of a single container. Not suitable for a multi-service app like yours.

---

### `docker compose up` — Starting the Full Stack

```bash
# Start all services (build first if needed)
docker compose up --build

# Start in background (detached)
docker compose up -d --build

# Stop and remove containers
docker compose down

# Stop, remove containers AND delete volumes (⚠️ deletes DB data)
docker compose down -v
```

> **When to use:** Always, for your Laravel project. It starts `app`, `web`, `mysql`, and `rabbitmq` in one command with all the right configuration.

---

## 7. Daily-Use Docker Commands

### `docker build` — Build an Image

```bash
# Build using Dockerfile in current directory
docker build -t blog-app:latest .

# Build with a specific Dockerfile
docker build -f Dockerfile.prod -t blog-app:prod .

# Build without cache (force full rebuild)
docker build --no-cache -t blog-app:latest .
```

---

### `docker run` — Run a Container

```bash
# Run and attach to it (foreground)
docker run php:8.2-fpm

# Run in background (-d = detached)
docker run -d php:8.2-fpm

# Run with port mapping
docker run -d -p 8086:80 nginx:alpine

# Run with environment variables
docker run -d -e MYSQL_ROOT_PASSWORD=secret mysql:8.0

# Run interactively (useful for debugging)
docker run -it php:8.2-fpm bash

# Run and auto-remove when stopped
docker run --rm php:8.2-fpm php -v
```

---

### `docker ps` — List Containers

```bash
# List running containers
docker ps

# List ALL containers (including stopped)
docker ps -a

# Show only container IDs (useful for scripting)
docker ps -q
```

**Sample output:**
```
CONTAINER ID   IMAGE           COMMAND      PORTS                   NAMES
a1b2c3d4e5f6   php:8.2-fpm    "php-fpm"    9000/tcp                blog_laravel_app
f6e5d4c3b2a1   nginx:alpine   "/docker…"   0.0.0.0:8086->80/tcp    blog_nginx_web
```

---

### `docker exec` — Run Commands Inside a Running Container

```bash
# Open a bash shell inside the app container
docker exec -it blog_laravel_app bash

# Run a single artisan command
docker exec blog_laravel_app php artisan migrate

# Run artisan queue worker
docker exec -d blog_laravel_app php artisan queue:work

# Check PHP version inside container
docker exec blog_laravel_app php -v

# Open MySQL CLI
docker exec -it blog_mysql_db mysql -u mahmud -p
```

> `-it` means interactive + TTY — required when you want a live terminal session.
> Without `-it`, use for one-off commands.

---

### `docker logs` — View Container Output

```bash
# View logs of a container
docker logs blog_laravel_app

# Follow logs in real time (like tail -f)
docker logs -f blog_laravel_app

# Show last 50 lines
docker logs --tail 50 blog_laravel_app

# Show logs with timestamps
docker logs -t blog_laravel_app

# Follow last 100 lines with timestamps
docker logs -f --tail 100 -t blog_nginx_web
```

---

### `docker stop` / `start` / `restart`

```bash
# Gracefully stop a container (sends SIGTERM, waits 10s, then SIGKILL)
docker stop blog_laravel_app

# Start a stopped container
docker start blog_laravel_app

# Restart a container
docker restart blog_laravel_app

# Stop multiple containers at once
docker stop blog_laravel_app blog_nginx_web blog_mysql_db
```

---

### `docker rm` / `docker rmi` — Remove Containers and Images

```bash
# Remove a stopped container
docker rm blog_laravel_app

# Force-remove a running container
docker rm -f blog_laravel_app

# Remove all stopped containers
docker container prune

# Remove an image
docker rmi php:8.2-fpm

# Remove an image by ID
docker rmi a1b2c3d4e5f6

# Remove all unused images
docker image prune -a
```

---

### `docker compose` Commands

```bash
# Build and start all services in foreground
docker compose up --build

# Build and start all services in background
docker compose up -d --build

# Start without rebuilding
docker compose up -d

# Stop and remove containers (keeps volumes)
docker compose down

# Stop, remove containers AND volumes (⚠️ deletes DB data)
docker compose down -v

# View logs of all services
docker compose logs

# Follow logs of a specific service
docker compose logs -f app

# Restart a specific service
docker compose restart app

# Run a command in a service container
docker compose exec app php artisan migrate
docker compose exec app php artisan key:generate
docker compose exec app composer install

# View running services
docker compose ps

# Build images without starting containers
docker compose build

# Pull latest images for all services
docker compose pull
```

---

### `docker volume` Commands

```bash
# List all volumes
docker volume ls

# Inspect a volume (see where data is stored)
docker volume inspect mysql_data

# Create a volume manually
docker volume create my_volume

# Remove a volume (⚠️ permanent data loss)
docker volume rm mysql_data

# Remove all unused volumes
docker volume prune
```

---

### `docker network` Commands

```bash
# List all networks
docker network ls

# Inspect a network (see which containers are connected)
docker network inspect blog_laravel

# Create a network
docker network create my_network

# Connect a running container to a network
docker network connect blog_laravel my_container

# Disconnect a container from a network
docker network disconnect blog_laravel my_container

# Remove a network
docker network rm my_network
```

---

### System Cleanup Commands

```bash
# Remove ALL unused containers, networks, images, and build cache
docker system prune

# Include unused volumes in the cleanup (⚠️ careful)
docker system prune --volumes

# Show disk usage
docker system df
```

---

## 8. Best Practices

### Naming Conventions

```yaml
# ✅ Good — prefix with project name, descriptive suffix
container_name: blog_laravel_app
container_name: blog_nginx_web
container_name: blog_mysql_db

# ❌ Bad — generic, will conflict with other projects
container_name: app
container_name: db
```

Use pattern: `{project}_{service}_{role}` — makes `docker ps` output readable.

---

### Image Size Optimization

**1. Use Alpine variants when possible**
```dockerfile
# ❌ Full Debian image (~500MB)
FROM nginx:latest

# ✅ Alpine variant (~23MB)
FROM nginx:alpine
```

**2. Combine RUN commands**
```dockerfile
# ❌ Creates 3 separate layers
RUN apt-get update
RUN apt-get install -y curl
RUN apt-get install -y git

# ✅ Creates 1 layer
RUN apt-get update && apt-get install -y \
    curl \
    git \
    && rm -rf /var/lib/apt/lists/*
```

**3. Clean up after apt-get**
```dockerfile
# ✅ Remove the apt cache to reduce image size
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    && rm -rf /var/lib/apt/lists/*
```

**4. Use `.dockerignore`**

Create a `.dockerignore` file to prevent unnecessary files from being copied:
```
node_modules
vendor
.git
.env
*.log
storage/logs
```

**5. Order layers from least to most frequently changed**
```dockerfile
# ✅ System packages change rarely → first
RUN apt-get install -y ...

# ✅ Composer files change occasionally → middle
COPY composer.json composer.lock ./
RUN composer install

# ✅ Application code changes often → last
COPY . .
```
> Docker caches layers. If a layer's content hasn't changed, Docker reuses the cached version. Putting frequently changing code last means more cache hits = faster builds.

---

### Development vs Production

| Concern | Development | Production |
|---|---|---|
| Code sync | Volume mount (live reload) | `COPY` baked into image |
| Dependencies | `composer install` (includes dev) | `composer install --no-dev --optimize-autoloader` |
| Debug tools | Xdebug, verbose logs | Disabled |
| `.env` | `.env` file in repo (local) | Environment variables from secrets manager |
| Image tag | `latest` or `dev` | Specific version tag (`v1.2.3`) |
| Restart policy | None needed | `restart: unless-stopped` |

**Production additions to consider:**

```yaml
# docker-compose.prod.yml
services:
  app:
    restart: unless-stopped   # Auto-restart on crash or reboot
    environment:
      APP_ENV: production
      APP_DEBUG: "false"

  mysql:
    restart: unless-stopped
```

**Separate your Dockerfiles:**
```
Dockerfile          # Development
Dockerfile.prod     # Production (leaner, no dev tools)
```

---

### Security Best Practices

```dockerfile
# ✅ Never run as root in production
RUN adduser --disabled-password --gecos '' appuser
USER appuser

# ✅ Use specific image tags, not 'latest'
FROM php:8.2.15-fpm  # ← pinned version, reproducible builds
# not: FROM php:latest  ← could change unexpectedly

# ✅ Keep secrets out of the image
# ❌ Never do this:
ENV DB_PASSWORD=12345678

# ✅ Use .env files or Docker secrets at runtime
```

---

### Laravel-Specific Tips

```bash
# After docker compose up, run these once to set up Laravel:
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate
docker compose exec app php artisan storage:link

# For queue workers, add a separate service:
# queue:
#   build: { context: ., dockerfile: Dockerfile }
#   command: php artisan queue:work --tries=3
#   depends_on: [app, rabbitmq]
#   networks: [laravel]

# Clear all caches
docker compose exec app php artisan optimize:clear

# View Laravel logs
docker compose exec app tail -f storage/logs/laravel.log
# or
docker compose logs -f app
```

---

## Quick Reference Card

```
Your Stack:
  localhost:8086  →  Nginx (web)  →  PHP-FPM (app:9000)  →  MySQL (mysql:3306)
                                                           →  RabbitMQ (rabbitmq:5672)

Common Workflow:
  1. docker compose up -d --build    # Start everything
  2. docker compose exec app bash    # Enter PHP container
  3. php artisan migrate             # Run migrations
  4. exit                            # Leave container
  5. docker compose logs -f          # Watch all logs
  6. docker compose down             # Stop everything

Port Map (your project):
  http://localhost:8086    →  Your Laravel app (via Nginx)
  localhost:3307           →  MySQL (connect from TablePlus/DBeaver)
  http://localhost:15672   →  RabbitMQ Management UI
  localhost:5672           →  RabbitMQ AMQP (used by Laravel internally)
```

---

*This document is based on your actual `Dockerfile` and `docker-compose.yml` in `/Users/apple/Project/tutorial/blog`.*
