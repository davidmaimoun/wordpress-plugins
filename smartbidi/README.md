SmartBiDi – WordPress Plugin
Description

SmartBiDi is a professional WordPress plugin that automatically detects and fixes mixed RTL (Right-to-Left) and LTR (Left-to-Right) text.
It intelligently handles content that combines Hebrew, Arabic, and other RTL languages with Latin text, applying proper BiDi formatting for a clean and readable display.

No configuration required — it works out of the box.

Features

✅ Automatic BiDi detection – Detects mixed RTL (Hebrew, Arabic, etc.) and Latin text
✅ Smart text direction – Automatically applies RTL, LTR, or auto direction
✅ Full content coverage – Works with posts, pages, titles, excerpts, comments, widgets, and forms
✅ Shortcode support – Manually apply formatting with [smartbidi]
✅ Admin settings page – Simple and intuitive configuration
✅ Editor compatible – Works with Gutenberg and the Classic Editor
✅ Lightweight & fast – Optimized, minimal performance impact
✅ Responsive ready – Works perfectly on mobile and tablet

Installation
Method 1: Manual Installation

Download the smartbidi plugin folder

Zip the folder (if needed)

In WordPress, go to Plugins > Add New

Click Upload Plugin

Upload the ZIP file

Click Install Now

Activate the plugin

Method 2: FTP Installation

Unzip the smartbidi folder

Upload it to /wp-content/plugins/

Go to Plugins in your WordPress dashboard

Activate SmartBiDi

Usage
Automatic Mode (Default)

Once activated, SmartBiDi works automatically and formats mixed RTL/LTR text in:

Posts and pages

Titles

Excerpts

Comments

Text widgets

No shortcode required.

Shortcode Usage

To force BiDi formatting on a specific block of text:

[smartbidi]
Hello שלום مرحبا World עולם – mixed RTL and Latin text
[/smartbidi]

Examples of Mixed Text

SmartBiDi correctly handles cases like:

Hello שלום – Mixed text مثال

WordPress וורדפרס is awesome رائع

أنا أحب I love לתכנת programming

Configuration

Go to Settings > SmartBiDi to:

✅ Enable or disable automatic BiDi processing

🧪 View live preview examples

📘 Learn how to use the shortcode

How It Works (Technical Overview)

SmartBiDi:

Scans content for RTL Unicode characters

Hebrew (U+0590–U+05FF)

Arabic & related scripts (U+0600–U+08FF)

Detects Latin characters (A–Z, a–z)

Automatically applies:

dir="auto" for mixed RTL + LTR text

dir="rtl" for RTL-only text

Appropriate CSS classes for styling

Browser Support

✔ Chrome / Edge (latest versions)
✔ Firefox (latest versions)
✔ Safari (latest versions)
✔ Opera (latest versions)

WordPress Compatibility

Minimum WordPress version: 5.0

Tested up to: WordPress 6.4+

Required PHP version: 7.0 or higher

Theme Compatibility

Compatible with all standard WordPress themes. Tested with:

Twenty Twenty-Four

Twenty Twenty-Three

Astra

GeneratePress

And more…

FAQ
Does SmartBiDi work with Gutenberg?

Yes. SmartBiDi is fully compatible with the Gutenberg block editor and the Classic Editor.

Can I disable automatic processing?

Yes. You can disable it from Settings > SmartBiDi.

Does SmartBiDi slow down my site?

No. The plugin is lightweight and optimized (less than 20 KB total).

Does it support Arabic and other RTL languages?

Yes. SmartBiDi supports Hebrew, Arabic, Persian, Urdu, and other RTL languages.

File Structure
smartbidi/
├── smartbidi.php            (Main plugin file)
├── css/
│   └── style.css            (RTL / LTR styles)
├── js/
│   └── script.js            (BiDi detection logic)
└── README.md                (Documentation)

Changelog
Version 1.0.0 – 2026-01-30

🎉 Initial release

✅ Automatic RTL/LTR detection

✅ Smart BiDi formatting

✅ Shortcode [smartbidi]

✅ Admin settings page

✅ Gutenberg & Classic Editor support

Support

For help or issues:

Open a ticket on the WordPress support forum

Visit the plugin repository

Check the online documentation

License

This plugin is licensed under the GNU General Public License v2 or later (GPL-2.0+).

Credits

Developed with ❤️ for the multilingual WordPress community.

Contributing

Contributions are welcome!
Feel free to:

Report bugs

Suggest improvements

Submit pull requests

Enjoy perfectly formatted BiDi text — Hebrew, Arabic, Latin & more ✨🌍