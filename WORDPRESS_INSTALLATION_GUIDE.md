# RMS Premier Services — WordPress Installation & Consultation Email Guide

This package converts your modern web landing page into a fully functional, installable **WordPress Theme** with an integrated **Consultation Lead Management & Email Delivery System**.

---

## 📦 What Has Been Created

1. **`rms-premier-services.zip`**: The ready-to-upload WordPress theme zip file.
2. **`rms-premier-services/`**: The uncompressed theme directory containing:
   - `style.css` — Modern executive styling + WordPress theme headers
   - `functions.php` — Enqueues Google Fonts (`Inter`, `Outfit`), assets, and theme features
   - `header.php` & `footer.php` — Responsive navigation, logo handling, and semantic footer
   - `front-page.php` — Complete landing page (Hero, About, Filterable Services, Why Us, Target Market, Courses, Consultation Form)
   - `index.php` & `page.php` — Safe WordPress fallback templates for inner pages
   - `screenshot.png` — Theme preview image for the WordPress admin dashboard
   - `inc/consultation-handler.php` — Lead retention database and high-priority HTML email dispatcher
   - `assets/js/main.js` — AJAX form submission, scroll animations, mobile menu, and service tabs

---

## 🚀 How to Install in WordPress (3 Steps)

1. Log into your **WordPress Admin Dashboard** (e.g., `yourdomain.com/wp-admin`).
2. Navigate to **Appearance → Themes** in the left sidebar.
3. Click **Add New Theme** at the top, then click **Upload Theme**.
4. Choose **`rms-premier-services.zip`** and click **Install Now**.
5. Once uploaded, click **Activate**.

Your homepage will immediately display the full RMS Premier Services website!

---

## 📧 How the Consultation Email System Works

### 1. Direct Business Email Delivery
When a prospective client submits the **Request a Consultation** form:
- An executive, dark-navy branded HTML email is dispatched directly to **`sthomas@rmspremierservices.com`** (or whichever email you configure).
- It displays the client's **Name, Email, Organization Type, Service Needed**, and their full **Project Description**.
- **One-Click Reply**: The email header includes `Reply-To: Client Email`. When you click "Reply" in Outlook or Gmail, it replies directly to the client.

### 2. Zero Lost Leads (Built-in WordPress Dashboard Inbox)
- Every single inquiry is permanently stored in your WordPress database under **Consultations → All Inquiries** in your admin sidebar.
- You can review client details, add private team notes, update status (`New Lead`, `Contacted`, `In Progress`, `Closed`), or click **Reply to Client ↗** directly from WordPress.
- Even if your email server experiences temporary downtime, **no lead is ever lost**.

### 3. Consultation Settings & Instant Test Email
Under **Consultations → Settings**:
- Update your notification email or add CC addresses.
- Toggle the automatic confirmation email sent to prospective clients.
- Click **Send Test Email Now** to verify that WordPress is successfully dispatching messages to your inbox.

---

## ⚡ Email Delivery via Web3Forms (Now Active)

The theme is now directly integrated with **Web3Forms API** using your access key (`e25a9b5c-b5e5-435f-83a1-a9292dd1819b`):

- **Zero SMTP / Server Hassle**: Form submissions bypass WordPress `wp_mail` host restrictions entirely and deliver directly to your inbox.
- **Client-Side AJAX**: Includes loading indicator (`"Sending..."`), instant user alerts, anti-spam honeypot & botcheck, and automatic form resets.
- **Works Everywhere**: Submissions work consistently whether run on WordPress, local previews, or static landing pages.
