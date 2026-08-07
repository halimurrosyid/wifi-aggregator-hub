# WiFi Aggregator Hub - WordPress Plugin 📡

**WiFi Aggregator Hub** adalah plugin WordPress khusus yang berfungsi sebagai portal mesin pencari dan agregator provider internet Indonesia. Plugin ini mengindeks data artikel dari berbagai situs web melalui RSS/Atom feed atau XML Sitemap, mengelompokkannya berdasarkan wilayah dan provider, serta membentuk halaman landing SEO otomatis tanpa duplikasi konten.

Developed by **[Mujaddid Halimurrosyid Ajid WP](https://indahweb.com)**

---

## ⚡ Fitur Utama

- 🔄 **Multi-Source Indexing**: Mengambil data dari banyak situs web via RSS/Atom Feed dan XML Sitemap fallback.
- 🎯 **Smart Provider Detection**: Deteksi otomatis provider ISP (ICONNET, Indosat HiFi, CBN Fiber, Biznet, MyRepublic, LinkNet, Telkomsel One, XL Satu, dll.).
- 📍 **Indonesian Territory Engine**: Pengelompokan wilayah Indonesia (Provinsi, Kabupaten, Kota, beserta alias seperti *Kab Bandung*, *Bandung Regency*).
- 🛡️ **Duplicate Content Shield**: Menghindari sanksi duplikat konten Google dengan opsi deduplikasi (Domain Priority, Latest, Longest, Manual).
- 🚀 **Dynamic SEO Landing Pages**: Membuat halaman `/wifi-[area]/` dan `/provider/[provider]/` otomatis dengan Schema.org JSON-LD (CollectionPage, ItemList, BreadcrumbList, FAQPage, Organization).
- 🔍 **Live Autocomplete AJAX Search**: Widget pencarian cepat instan menggunakan shortcode `[wifi_search_box]`.
- 🩺 **Broken Link Checker**: Deteksi otomatis status HTTP 404 & noindex untuk menjaga kualitas tautan.
- 🔄 **GitHub Automatic Updates**: Terintegrasi langsung dengan GitHub Releases untuk pembaruan plugin otomatis dari dashboard WordPress.

---

## 🛠️ Instalasi

1. Download atau clone repository ini:
   ```bash
   git clone https://github.com/halimurrosyid/wifi-aggregator-hub.git
   ```
2. Upload folder `wifi-aggregator-hub` ke direktori `/wp-content/plugins/` di WordPress Anda.
3. Aktifkan plugin melalui menu **Plugins > Installed Plugins**.
4. Pastikan struktur Permalink WordPress diatur ke **Post name** (di *Settings > Permalinks*).

---

## 📊 Shortcode Yang Tersedia

- `[wifi_search_box]` - Menampilkan bar pencarian live autocomplete kota / provider.
- `[wifi_area_grid limit="12"]` - Menampilkan grid chip wilayah populer.
- `[wifi_provider_grid]` - Menampilkan grid badge provider ISP.

---

## 🔄 Pembaruan Otomatis (Automatic Updates)

Plugin ini dilengkapi dengan modul **GitHub Auto-Updater**. Setiap kali rilis/versi baru dibuat pada repository GitHub (`halimurrosyid/wifi-aggregator-hub`), pemberitahuan pembaruan akan otomatis muncul di menu **Plugins** WordPress Anda dan dapat diperbarui dengan 1-klik.

---

## 📄 License & Credits

- **Author**: Mujaddid Halimurrosyid Ajid WP
- **Website**: [https://indahweb.com](https://indahweb.com)
- **Repository**: [https://github.com/halimurrosyid/wifi-aggregator-hub](https://github.com/halimurrosyid/wifi-aggregator-hub)
- **License**: GPLv2 or later
