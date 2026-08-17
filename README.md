<div align="center">

# 📬 FormLite

**A lightweight WordPress contact form plugin — database storage and automatic email notifications.**

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/Byot3711/-Forms)
[![License: GPL v2+](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.0-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777bb4.svg)](https://www.php.net)

</div>

---

## About

**FormLite** adds a contact form anywhere on your site through a simple shortcode. Submissions are saved to the WordPress database and can be viewed from the admin panel, and the site admin gets an email notification for every new message.

No external dependencies, no unnecessary javascript — a single file, clean code, follows WordPress standards.

## Features

- Ready-to-use, responsive styled form — no extra CSS needed
- CSRF protection via WordPress nonces
- Full input sanitization (`name`, `email`, `message`)
- Submissions stored in a dedicated database table
- Automatic email notification on every new submission
- Admin dashboard with a paginated list of submissions
- Built-in instructions page showing how to add the form to a page
- Configurable recipient email
- One shortcode, instant integration: `[formlite]`

## Installation

1. Download or clone this repository:
   ```bash
   git clone https://github.com/Byot3711/-Forms.git formlite
   ```
2. Copy the `formlite` directory into:
   ```
   wp-content/plugins/
   ```
3. From **WP Admin → Plugins**, activate **FormLite**.
4. Done! On activation, the plugin automatically creates the required database table.

## Usage

Add the shortcode anywhere in a page or post:

```
[formlite]
```

The form will appear instantly, fully styled and ready to receive messages.

## Settings

From the **FormLite → Settings** menu in WP Admin, you can configure the email address that receives notifications (defaults to the site admin email).

## Submissions

All received messages can be viewed from **FormLite → Submissions**, with automatic pagination.

## Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL / MariaDB (standard WordPress)

## Structure

```
formlite/
└── formlite.php   # the entire plugin, in a single file
```

## License

Distributed under the **GPL-2.0+** license. See [LICENSE](LICENSE) for details.

## Author

Built by **[Byot](https://github.com/Byot3711)**.

---

<div align="center">
If this is useful to you, leave a ⭐ on the repository!
</div>
