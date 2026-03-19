# 🦁 Simba Framework
### *The Modern Starter-Kit for Professional Multi-Tenant Web Applications*

Simba is a modern framework designed for building advanced web applications. It provides a robust starter project for **multi-tenant** software, featuring a **SvelteKit** SPA frontend and an integrated **microservices** system (WebSockets, Redis, etc.).

<p align="center">
  <img alt="Simba Logo" src="https://solenoid.it/cdn/logo/Simba.jpg">
</p>

---

## 🚀 LAMP Stack
Simba is built on a high-performance stack optimized for modern web standards:
* **Web Server:** Apache with `mpm_event` module
* **Process Manager:** PHP-FPM
* **Protocol:** HTTP/2 support
* **DBMS:** MySQL

---

## 🐳 Docker Integration
The application runs as a Docker container based on **Ubuntu Server 24.04**.

*Requires `docker` and `docker-compose` installed on your host system.*

### Management Commands:
| Command | Description |
| :--- | :--- |
| `./start -b` | Build the official image and start the container (run only for the first setup) |
| `./start` | Start the container |
| `./stop` | Stop the container |
| `./restart` | Restart the container |
| `./shell` | Access the container's interactive shell |
| `./shell {cmd}` | Execute a specific command inside the container |

**Shell Examples:**
* Interactive: `./shell` -> `mkdir /tests` -> `exit`
* Direct command: `./shell mkdir /tests`

---

## 📦 Dependencies
Simba relies on two core pillars:
1.  [**php-x**](https://github.com/Solenoid-IT/php-x): The official backend library for core logic.
2.  [**SvelteKit**](https://kit.svelte.dev): The modern frontend framework for the SPA.

---

## 🛠 Setup Guide
Follow these steps to initialize your environment:

1.  **Initialize:** Run `./start -b` to build and start the container.
2.  **Identity:** Set the admin email: `./shell php x task OnDemand/User.set_email 1 "{your_email}"`
3.  **Access:** Open `https://localhost:8091` in your browser.
4.  **Login:** Use credentials `admin@simba` / `pass`.

---

## 👥 Multi-Tenancy & Users
Simba is natively designed for multi-tenant environments. Every **Tenant** can host multiple **User**.

**Create a user via console:**
`php x task OnDemand/User.insert {email} {password} {user=admin}? {tenant=simba}? {hierarchy_id=1}?`

* *Example:* `php x task OnDemand/User.insert "email@domain.net" "pass" "user_name" "tenant_name" 1`

---

## 🔐 Authentication
Simba supports three distinct authentication modes to ensure the optimal balance between security and user experience:

* **BASIC (Password):** The standard access method based on credentials (**Username and Password**). This is the default method.
* **MFA (Multi-Factor Authentication):** Advanced security requiring a combination of **Password + Email Confirmation**. A temporary code is automatically sent to the user's address to validate the session.
* **IDK (Identity Key):** The most secure method, based on a unique cryptographic key (similar to an **SSH key**) generated and stored locally in the user's **browser**. This eliminates the need to enter a password for every session and provides robust protection against credential theft.

> [!IMPORTANT]  
> For **MFA** mode to function correctly, ensure that the `SMTP_HOST` and `OP_HOST` parameters are properly configured in your `src/.env` file.

---

## 📊 Activities
Simba logs every user action to a dedicated activity table in the database.
* **Clear Logs:** `php x task OnDemand/Activity.delete {tenant_name}`

---

## 📡 APIs
Simba APIs are based on the Solenoid **sRPC** standard and are managed by the `ApiGateway` controller class.
* **Request Format:** `RUN /api?m=My/Custom/Class.method`

---

## 🔑 Token APIs
Simba includes APIs accessible via Personal Tokens.
* **Build Documentation:** `./shell php x apidoc:build` (SXF compatible).
* **Access Docs:** `https://localhost:8091/apidoc`

---

## ⚡ Triggers
Users can define custom triggers that initiate an HTTP request when specific system events occur.

---

## 📧 Operations
Simba features a built-in system for managing operations that require user confirmation via email.
* **Config:** Set your `SMTP_HOST` and `OP_HOST` (FQDN) in the `src/.env` file.

---

## 🏗 Development
The Simba container utilizes two distinct servers for HTTP requests:
1.  **Backend (Port 8091):** Apache server for APIs and server-side content.
2.  **Frontend (Port 8092):** Vite server for real-time SPA development.
    * *Start Dev Session:* `php x spa:dev`

*Note: If using **VS Code**, forward ports **8091** and **8092** to localhost.*

---

## 🏗 Build
Compile frontend SPA components for production:
`php x spa:build`

---

## 🚀 Production
For production environments, Simba uses a dedicated port (**8090**) behind a custom reverse proxy.

**Standard Workflow:**
1.  **Add Site:** `sudo siteman add {fqdn}`
2.  **Clone:** `git clone "https://github.com/Solenoid-IT/simba-app.git"` inside the site directory.
3.  **Deploy:** `./start -b`
4.  **Config:** Update `src/.env` (SMTP/FQDN).
5.  **Proxy:** Enable reverse proxy in site config and restart Apache.

---

## 🛠 Server Components
Generate components (Model, Service, Middleware, Task, or Controller) via CLI:
`php x make:{type} {path}`

---

## 🗄 Database
Manage multiple database projects with integrated migration stacks.
* *Create:* `php x make:migration {project} {migration_id}`
* *Migrate Up:* `php x db:migrate {db_project} up [+1|migration_id]`
* *Migrate Down:* `php x db:migrate {db_project} down [-1|migration_id]`

---

## 🖥 Database Management (phpMyAdmin)
An integrated phpMyAdmin instance is available at `https://localhost:8091/phpmyadmin`.
* **Warning:** In production, restrict this location to local access only using the "Require local" directive in your Apache configuration.

---

## ⚙️ Tasks
Tasks in Simba are CLI-driven executions of class methods. They are designed for background processing, maintenance, and automated workflows.

### Advanced Features:
* **Schedule:** Tasks can be natively scheduled for periodic execution (e.g., every minute, hourly, or via custom intervals).
* **Mutex:** Integrated Mutex support prevents race conditions by ensuring only one instance of a specific task runs at a time.

### Automation & Crontab:
The Simba Docker environment comes with a **pre-configured Crontab** that triggers the internal scheduler **every minute**. This means any task assigned with a `Schedule()` attribute will execute automatically without additional server configuration.

### Usage:
`php x task {path}[.{method}]? ...{args}`

* **Standard execution:** `php x task OnDemand/Test.sum 1 2`
* **Scheduled execution:** The framework automatically handles methods decorated with the `Schedule()` attribute during the system tick.
* **Concurrency control:** Methods using the `Mutex()` attribute will automatically exit if another instance is already active.

---

## 🔄 HTTP / sRPC Flow
**Standard Flow:**
`Request` → `Gate` → `Pipeline` → `Controller` → `Response`

**sRPC Flow (ApiGateway):**
`Request` → `Gate` → `Pipeline` → `GatewayController` → `Action Runner` → `Class` → `Response`

---

## 📡 sRPC
**sRPC** (Solenoid RPC) is Simba's standard protocol. It offers superior scalability compared to REST or SOAP as routing is based on the file system rather than static bindings.
* **Specifications:** Detailed protocol information can be found in the [Official sRPC Repository](https://github.com/Solenoid-IT/sRPC).

---

## 🔒 Certificate (TLS)
Includes a self-signed certificate for development (valid for 1 year).
* **Renew:** `./shell php x cert:generate`

---

## ✉️ Email Service (SMTP)
Simba utilizes an SMTP account to send authorization emails for operations such as MFA, email changes, or other sensitive tasks.
* **Setup:** SMTP parameters are defined within the `src/.env` file.

---

## 🌍 IPGEO (Third-party Integration)
Simba supports IPGEO data detection for activity logging (tracking Country, Browser, OS).
* **Provider:** [ipgeolocation.io](https://ipgeolocation.io/)
* **Config:** Define `IPGEO_API_KEY` in `src/.env`.

---

## 📈 SEO
Manage SEO settings via templates in `/views/dynamic_files/`:
1.  `robots.txt.blade.php`
2.  `sitemap.xml.blade.php`

---

## ⚖️ License & Trademark

### Software License
Simba Framework is open-source software licensed under the [Apache License, Version 2.0](LICENSE). 

**What this means:**
* **Commercial Use:** You can use Simba to build and sell your own commercial applications or SaaS.
* **Modification:** You can modify the source code, but you must include the original copyright notice.
* **Patent Protection:** This license includes an explicit grant of patent rights from contributors to users, providing a robust legal shield for professional and enterprise environments.

### Trademark Policy
**Solenoid-IT™**, **Simba™**, and **sRPC™** are trademarks of **Solenoid-IT**. 

While the source code is free to use and modify, the names, logos, and brand identity are not part of the Apache license. 
* You **may** say your project is "Built with Simba" or "Powered by Solenoid-IT".
* You **may not** use these names or logos in a way that implies an official endorsement, partnership, or ownership by Solenoid-IT without prior written consent.

---

<p align="center">
  <b>Solenoid-IT</b> &copy; 2026 — <a href="https://solenoid.it">solenoid.it</a><br>
  <i>Crafting scalable architecture for modern developers.</i>
</p>