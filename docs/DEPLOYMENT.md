# NetwoTurk Core Deployment

Bu dokuman NetwoTurk Core Laravel 13 projesinin shared/cloud hosting uzerinde calismasi, GitHub uzerinden guncellenmesi ve ileride VPS ortamlarina kolay tasinabilmesi icin deployment kurallarini tanimlar.

## Hedef Ortam

V1 icin birincil hedef shared/cloud hostingdir. Sistem standart PHP hosting paketlerinde calisabilecek kadar sade tutulur.

Temel gereksinimler:

- Laravel 13 ile uyumlu PHP surumu
- Composer destegi
- MySQL/MariaDB veya Laravel tarafindan desteklenen uyumlu veritabani
- Web server document root ayari
- Yazilabilir `storage/` dizini
- Yazilabilir `bootstrap/cache/` dizini

Redis, WebSocket, queue worker, Supervisor veya ozel daemon V1 icin zorunlu gereksinim degildir.

## GitHub Ana Kaynak Kurali

GitHub proje kodu icin ana kaynaktir.

Kurallar:

- Production sunucu guncellemeleri GitHub uzerinden yapilir.
- Sunucuda standart guncelleme `git pull` ile alinir.
- FTP ile manuel dosya tasima standart deployment yontemi degildir.
- Production uzerinde elle kod degisikligi yapilmaz.
- `.env` GitHub'a eklenmez.
- Veritabani backup'i GitHub'a eklenmez.
- Upload veya runtime dosyalari repository'ye zorunlu veri gibi eklenmez.

## Document Root Kurali

Laravel'in resmi public document root yapisi korunur.

Kurallar:

- Web sunucusunun document root'u `public/` dizinini gostermelidir.
- Laravel proje kok dizini web'den direkt erisilebilir olmamalidir.
- `.env`, `storage/`, `vendor/`, `app/`, `config/` ve `database/` public erisime acilmamalidir.
- `public/index.php` Laravel'in standart giris noktasi olarak kalir.

Shared hosting panelinde document root ayarlanabiliyorsa dogrudan `public/` secilir. Ayarlanamiyorsa hosting saglayicisinin Laravel uyumlu kurulum yontemi tercih edilir; Laravel yapisini bozacak dosya tasimalari standart cozum kabul edilmez.

## Ortam Degiskenleri ve Secret Kurali

Gizli bilgiler yalnizca `.env` icinde tutulur.

`.env` icinde yer alabilecek bilgiler:

- Uygulama anahtari
- Veritabani baglanti bilgileri
- Mail ayarlari
- Uygulama URL bilgisi
- API signature secret degerleri

Kurallar:

- `.env` repository'ye eklenmez.
- Gizli bilgi config dosyalarina yazilmaz.
- Gizli bilgi dokumanlara yazilmaz.
- Production degerleri lokal veya test ortamina kopyalanmaz.
- `.env.example` sadece ornek anahtar isimlerini icerir, gercek deger icermez.

## Shared Hosting Ilk Kurulum

Standart shared hosting kurulum akisi:

1. Hosting uzerinde proje klasoru olusturulur.
2. GitHub repository clone edilir.
3. Production `.env` dosyasi sunucu uzerinde olusturulur.
4. `composer install --no-dev --optimize-autoloader` calistirilir.
5. `APP_KEY` yoksa `php artisan key:generate` calistirilir.
6. Veritabani baglantisi dogrulanir.
7. `php artisan migrate --force` calistirilir.
8. Gerekli cache komutlari calistirilir.
9. `storage/` ve `bootstrap/cache/` izinleri kontrol edilir.
10. Hosting document root `public/` olarak ayarlanir.
11. Admin panel, License Check API ve Core Feed API smoke test yapilir.

Composer sunucuda kullanilamiyorsa hosting saglayicisinin destekledigi Laravel uyumlu build yaklasimi tercih edilir. Buna ragmen manuel FTP ile rastgele dosya tasima standart surec kabul edilmez.

## Shared Hosting Guncelleme

Standart guncelleme akisi:

1. Bakim penceresi gerekiyorsa duyurulur.
2. Veritabani backup'i alinir.
3. Sunucuda proje dizinine girilir.
4. `git pull` ile son stabil kod alinir.
5. `composer install --no-dev --optimize-autoloader` calistirilir.
6. `php artisan migrate --force` calistirilir.
7. `php artisan config:cache`, `php artisan route:cache` ve `php artisan view:cache` calistirilabilir.
8. `storage/` ve `bootstrap/cache/` izinleri kontrol edilir.
9. Admin panel ve API smoke test yapilir.

Guncelleme FTP ile manuel dosya kopyalama uzerine kurulmaz.

## VPS'ye Gecis

VPS'ye gecis temel Laravel tasima adimlariyla yapilabilir olmalidir.

Gerekli varliklar:

- GitHub repository
- Production `.env`
- Veritabani backup'i
- Gerekliyse `storage/` icerigi
- Composer
- Web server document root ayari

VPS gecis adimlari:

1. VPS uzerinde PHP, Composer ve veritabani ortami hazirlanir.
2. GitHub repository clone edilir.
3. Production `.env` dosyasi yerlestirilir.
4. Veritabani backup'i restore edilir.
5. `composer install --no-dev --optimize-autoloader` calistirilir.
6. `php artisan migrate --force` calistirilir.
7. `storage/` ve `bootstrap/cache/` yazilabilir yapilir.
8. Web server document root `public/` olarak ayarlanir.
9. Gerekirse cache komutlari calistirilir.
10. Admin panel, License Check API ve Core Feed API smoke test yapilir.

V1'de Redis, Supervisor, WebSocket veya queue worker VPS gecis on kosulu degildir.

## Migration Kurali

Tum veritabani degisiklikleri migration ile yapilir.

Kurallar:

- Production'da manuel tablo veya kolon ekleme standart disidir.
- Manuel veritabani islemi gerekiyorsa once migration yazilir.
- Migration dosyalari sirali ve acik isimli olur.
- Veri kaybina yol acabilecek degisiklikler ayrica incelenir.
- V1 disi ozellikler icin tablo olusturulmaz.

## Dosya ve Depolama

Laravel'in standart `storage/` yapisi kullanilir.

Kurallar:

- Upload edilen dosyalar kontrolsuz sekilde `public/` dizinine yazilmaz.
- Public dosyalar icin Laravel'in standart storage link yaklasimi kullanilir.
- Hosting tasinmasi icin `storage/` icerigi yedeklenebilir olmalidir.
- Log dosyalari yazilabilir olmalidir.

## Cache ve Performans

V1 performans yaklasimi Laravel'in standart optimizasyonlaridir:

- Config cache
- Route cache
- View cache
- Database indeksleri
- Sade sorgular
- Modul tarafinda API response cache'i

Redis zorunlu hale getirilmez. Cache ihtiyaci Laravel'in dosya veya veritabani tabanli standart secenekleriyle karsilanabilir.

## Deployment Kontrol Listesi

Canliya cikis oncesi kontrol:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL` dogru
- Veritabani baglantisi dogru
- Migration'lar calisti
- `public/` document root aktif
- `.env` public erisime kapali
- `storage/` yazilabilir
- `bootstrap/cache/` yazilabilir
- Admin panel aciliyor
- `POST /api/v1/license/check` beklenen JSON cevabini donuyor
- `GET /api/v1/core/feed` beklenen JSON cevabini donuyor
- Log dosyalari yazilabiliyor

## Codex Deployment Kurallari

Codex deployment veya ortam ayari konusunda calisirken:

- Gercek gizli bilgi istemez ve dosyaya yazmaz.
- `.env.example` disinda ortam dosyasi olusturmaz; kullanici acikca isterse ornek degerlerle calisir.
- Shared hosting uyumlulugunu bozan oneriler yapmaz.
- Laravel public document root yapisini degistirmez.
- V1 disi servisleri zorunlu gereksinim gibi eklemez.
- Deployment dokumanlarini koddan bagimsiz ve uygulanabilir tutar.
