# Bootgly PHP Framework (v0.0.1-pre-alpha)

**Full Stack PHP Framework for Multi Projects (WIP)**

🚧 Do not use it in production environments. The alpha version hasn't even been released yet. 🚧

*Repository created for Github Accelerator.*

First beta release is planned for mid-year 2023.

Documentation and Website will be released alongside the beta.

Servers | Database | Back-end | Front-end
--- | --- | --- | ---
[TCP Server (WIP)](/interfaces/Web/TCP/Server.php) | ORM (TODO) | [Router (WIP)](/nodes/Web/HTTP/Server/Router.php) | [Templating Engine (WIP)](/core/Template.php)
[HTTP Server (WIP)](/nodes/Web/HTTP/Server.php) | _ | [Router/Route (WIP)](/nodes/Web/HTTP/Server/Router/Route.php) | _
_ | _ | [Request (WIP)](/nodes/Web/HTTP/Server/Request.php) | _
_ | _ | [Response (WIP)](/nodes/Web/HTTP/Server/Response.php) | _

---

## ⚙️ Dependencies

- PHP 8.2+ `[Required]`
- Apache Rewrite enabled `[Required for Non-CLI SAPI only]`
- Opcache with JIT enabled (+50% performance) `[Optional]`
- Linux OS (Debian based OS is recommended: Debian, Ubuntu...) `[Required]`

---

## 🔧 Usage

### **Non-CLI SAPI (Apache, LiteSpeed, Nginx, etc)**

1) Clone this repository
2) Rename `projects/web.constructor.php.example` to `projects/web.constructor.php`
3) Enable Rewrite

### **CLI SAPI**

in Linux OS:

- Check `server.http.php` file;
- Rename `projects\sapi.constructor.php.example` to `projects\sapi.constructor.php`;
- Run the server:

`php server.http.php`

--

or with Docker *(-10% performance)*:

- Build local image:

`docker build --pull --rm -f "bootgly-cli-server.http.dockerfile" -t bootglyphpframework:latest "."`

- Run the Docker image:

`docker run -it --network host bootglyphpframework`

---

## 🖼 Screenshots

| ![HTTP Server CLI - My Benchmark results using Ryzen 9 3900X (24 CPUs) on WSL2](https://github.com/bootgly/.github/raw/main/screenshots/bootgly-php-framework/Server-CLI-HTTP-Benchmark-Ryzen-9-3900X-WSL2.png "HTTP Server CLI - My Benchmark results using Ryzen 9 3900X (24 CPUs) on WSL2") |
|:--:| 
| *HTTP Server CLI - My Benchmark results using Ryzen 9 3900X (24 CPUs) on WSL2* |

| ![HTTP Server CLI started - Initial output](https://github.com/bootgly/.github/raw/main/screenshots/bootgly-php-framework/Server-CLI-HTTP-started.png "HTTP Server CLI started - Initial output") |
|:--:| 
| *HTTP Server CLI started - Initial output* |

## 💖 Sponsorship

A lot of time and energy is devoted to this project. To accelerate your growth, if you like this project or depend on it for your stack to work, consider sponsoring it.

Your sponsorship will keep this project always **up to date** with **new features** and **improvements** / **bug fixes**.

## 📃 License

The Bootgly PHP Framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
