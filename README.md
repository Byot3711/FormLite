<div align="center">

# 📬 Professional Contact Forms

**Un plugin WordPress curat, sigur și ușor de folosit pentru formulare de contact — cu stocare în baza de date și notificări automate pe email.**

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/Byot3711/-Forms)
[![License: GPL v2+](https://img.shields.io/badge/license-GPL--2.0%2B-green.svg)](LICENSE)
[![WordPress](https://img.shields.io/badge/WordPress-%3E%3D5.0-21759b.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D7.4-777bb4.svg)](https://www.php.net)

</div>

---

## ✨ Despre plugin

**Professional Contact Forms** adaugă un formular de contact modern pe orice pagină sau articol, printr-un simplu shortcode. Toate trimiterile sunt salvate în baza de date WordPress și pot fi vizualizate direct din panoul de administrare, iar administratorul primește o notificare pe email la fiecare mesaj nou.

Fără dependențe externe, fără javascript inutil — un singur fișier, cod curat, respectă standardele WordPress.

## 🚀 Funcționalități

| | |
|---|---|
| 🎨 | Formular stilizat, responsive, gata de utilizare — fără CSS suplimentar |
| 🔒 | Protecție prin `nonce` împotriva atacurilor CSRF |
| 🧹 | Sanitizare completă a datelor (`name`, `email`, `message`) |
| 💾 | Stocare trimiteri într-un tabel dedicat în baza de date |
| 📧 | Notificare automată pe email la fiecare trimitere nouă |
| 🗂️ | Panou de administrare cu listă paginată a mesajelor primite |
| ⚙️ | Pagină de setări — email destinatar configurabil |
| 🧩 | Un singur shortcode, integrare instant: `[professional_form]` |

## 📦 Instalare

1. Descarcă sau clonează acest repository:
   ```bash
   git clone https://github.com/Byot3711/-Forms.git professional-forms
   ```
2. Copiază directorul `professional-forms` în:
   ```
   wp-content/plugins/
   ```
3. Din **WP Admin → Plugins**, activează **Professional Contact Forms**.
4. Gata! La activare, plugin-ul creează automat tabelul necesar în baza de date.

## 🖊️ Utilizare

Adaugă shortcode-ul oriunde într-o pagină sau articol:

```
[professional_form]
```

Formularul va apărea instant, complet stilizat, gata să primească mesaje.

## ⚙️ Setări

Din meniul **Formulare → Setări** din WP Admin poți configura adresa de email la care se trimit notificările (implicit, emailul administratorului site-ului).

## 🗃️ Trimiteri

Toate mesajele primite pot fi vizualizate din meniul **Formulare → Trimiteri**, cu paginare automată.

## 🛠️ Cerințe

- WordPress 5.0+
- PHP 7.4+
- MySQL / MariaDB (standard WordPress)

## 📁 Structură

```
professional-forms/
└── professional-forms.php   # tot plugin-ul, într-un singur fișier
```

## 📄 Licență

Distribuit sub licența **GPL-2.0+**. Vezi fișierul [LICENSE](LICENSE) pentru detalii.

## 👤 Autor

Dezvoltat de **[Byot](https://github.com/Byot3711)**.

---

<div align="center">
Dacă îți este util, lasă un ⭐ pe repository!
</div>
