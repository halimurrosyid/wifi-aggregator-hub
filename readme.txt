=== WiFi Aggregator Hub ===
Contributors: halimurrosyid
Donate link: https://indahweb.com
Tags: wifi, aggregator, isp, provider, search engine, seo, indonesia
Requires at least: 5.8
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: 1.0.23
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mesin indeks dan agregator pencarian provider internet Indonesia dari berbagai feed website (RSS/Atom/Sitemap) dengan pengelompokan wilayah & provider, deduplikasi otomatis, dan landing page SEO.

== Description ==

WiFi Aggregator Hub adalah plugin WordPress profesional yang dirancang khusus untuk mengagregasi data artikel provider internet (ICONNET, Indosat HiFi, CBN Fiber, Biznet, MyRepublic, LinkNet, Telkomsel One, dll.) dari berbagai situs web tanpa meng-copy konten asli.

= Fitur Utama =
* **Sinkronisasi Otomatis**: Mengambil data melalui RSS Feed / Atom atau XML Sitemap fallback.
* **Provider & Territory Detection**: Pengelompokan otomatis berdasarkan provider ISP dan wilayah/kota Indonesia.
* **Deduplikasi Cerdas**: Memfilter artikel duplikat dari domain berbeda menggunakan strategi Domain Priority, Artikel Terbaru, atau Artikel Terpanjang.
* **Landing Page SEO Otomatis**: Membuat Halaman SEO khusus per kota/kabupaten dan per provider lengkap dengan Schema.org JSON-LD (CollectionPage, ItemList, BreadcrumbList, FAQPage, Organization).
* **Live Search Autocomplete**: Pencarian instan wilayah & provider menggunakan AJAX (`[wifi_search_box]`).
* **Broken Link Checker**: Otomatis mendeteksi status HTTP 404/noindex dan menyembunyikan artikel bermasalah.
* **Update Otomatis GitHub**: Terhubung langsung ke GitHub release untuk pembaruan plugin satu-klik.

== Installation ==

1. Unggah folder `wifi-aggregator-hub` ke direktori `/wp-content/plugins/` atau unggah file `wifi-aggregator-hub.zip` via WordPress Admin.
2. Aktifkan plugin melalui menu 'Plugins' di WordPress.
3. Buka menu **WiFi Aggregator > Feed Sources** untuk memasukkan daftar feed website Anda.
4. Atur struktur Permalink ke `Post name` pada menu Settings > Permalinks.

== Changelog ==

= 1.0.23 =
* Feature: Rich FAQPage Schema.org JSON-LD (Menambahkan struktur data FAQ khusus kota untuk menampilkan cuplikan FAQ accordion pada hasil pencarian Google).

= 1.0.22 =
* Feature: Universal Template Hijacker Filter & Request Overrider.

= 1.0.21 =
* Fix: Auto-Healing Permalinks & Request URI Fallback Router.

= 1.0.20 =
* Feature: Sleek Glassmorphic 5-Column Metric Widgets & Accurate Database Total Article Count.

= 1.0.19 =
* Fix: Perbaikan Event Listener Tombol Sync Sekarang di Dashboard.

= 1.0.18 =
* Fix: Solusi Total Fatal Error WAH_Router::register_query_vars.

= 1.0.17 =
* Fix: Fix Fatal Error Call to Private Method & Fallback Area Array Guarantee.

= 1.0.16 =
* Fix: Solusi Total Critical Error pada Tema WordPress.

= 1.0.15 =
* Feature: Kolom Statistik Posting per Provider di Provider Manager.

= 1.0.14 =
* Feature: Ultra-Fast Chunked Batch Database Insertion & Memory Map Sync.

= 1.0.13 =
* Feature: Dynamic SEO Meta Pattern Integration.

= 1.0.12 =
* Feature: Eksklusif Tombol Custom CTA.

= 1.0.11 =
* Feature: Paginasi Tabel Admin Per Page View & Grafik Analitik Visual.

= 1.0.10 =
* Feature: Universal Sub-Sitemap Index Crawling.

= 1.0.9 =
* Feature: Multi-Page RSS Pagination & Deep Sitemap Index Fetcher.

= 1.0.8 =
* Feature: Cakupan Wilayah Berjenjang Kecamatan & Desa.

= 1.0.7 =
* Fix & Feature: Presisi Deteksi Wilayah Kota (memperbaiki pencocokan kata kota seperti Sintang, Labungkari, Panyabungan, Waikabubak).

= 1.0.6 =
* Feature: Penyaringan Dinamis Landing Pages (Hanya wilayah yang memiliki artikel terindeks yang ditampilkan dalam daftar Landing Pages, Sitemap, dan link terkait).

= 1.0.5 =
* Feature: Penambahan Fallback Artikel Nasional / Umum.

= 1.0.4 =
* Feature: Penambahan Tabel Viewer Artikel Terindeks pada Dashboard Admin untuk melihat daftar artikel yang berhasil di-sync beserta status provider & wilayah.

= 1.0.3 =
* Feature: Penambahan Auto-Discovery Wilayah Baru (secara otomatis mendeteksi dan membuat Halaman SEO wilayah/kota baru jika ditemukan dalam isi feed).

= 1.0.2 =
* Feature: Peningkatan updater otomatis langsung dari branch main GitHub & penambahan tombol paksa perbarui cache.

= 1.0.0 =
* Rilis Perdana WiFi Aggregator Hub oleh Mujaddid Halimurrosyid Ajid WP (https://indahweb.com).
* Fitur Pengelompokan Wilayah Indonesia & Provider ISP.
* Integrasi Pembaruan Otomatis dari GitHub (`halimurrosyid/wifi-aggregator-hub`).
