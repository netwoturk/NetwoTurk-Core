# NetwoTurk Core Architecture

Bu dokuman NetwoTurk Core Laravel 13 projesinin V1 entegrasyon mimarisini tanimlar. Amac, Laravel'in resmi yapisini bozmadan PrestaShop, OpenCart, WordPress ve Laravel tabanli modullere merkezi lisans, guncelleme, duyuru, kampanya ve onerilen modul feed'i saglamaktir.

## Temel Mimari

NetwoTurk Core, Laravel'in resmi proje yapisini koruyan monolitik bir web uygulamasi olarak gelistirilir. V1 icin mikroservis, Redis, WebSocket, Supervisor, agir queue sistemi veya ozel daemon bagimliligi eklenmez.

Uygulama su ana katmanlardan olusur:

- Admin panel: Musteri, platform, urun, domain, lisans, duyuru, kampanya, onerilen urun ve ayar yonetimi.
- License Check API: Harici modullerin lisans durumunu kontrol ettigi merkezi API.
- Core Feed API: Harici modullere duyuru, kampanya, onerilen urun, guncelleme ve operasyon mesajlari saglayan feed API.
- Domain modeli: Organization/customer, platform, product, domain ve license iliskilerini tasiyan veri yapisi.
- Operasyon katmani: License check loglari, API erisim loglari ve genel settings kayitlari.

Laravel'in varsayilan dizinleri korunur:

- `app/` uygulama siniflari icin kullanilir.
- `routes/web.php` admin panel web rotalari icin kullanilir.
- `routes/api.php` V1 API rotalari icin kullanilir.
- `database/migrations/` tum veritabani semasi icin tek kaynak olur.
- `config/` sadece ortamdan okunan uygulama ayarlari icindir.
- `public/` document root olarak korunur.
- `storage/` log, cache ve dosya alanidir.
- `bootstrap/cache/` Laravel cache dosyalari icindir.

## Resmi Sinirlar

V1 boyunca asagidaki kurallar degismez:

- Laravel resmi yapisi bozulmaz.
- `public/` document root olarak kalir.
- `.env` disinda secret tutulmaz.
- `storage/` ve `bootstrap/cache/` yazilabilir olur.
- Veritabani degisiklikleri migration ile yapilir.
- Shared/cloud hosting uyumu korunur.
- Redis, WebSocket, Supervisor ve agir queue sistemi V1'e eklenmez.
- VPS'ye gecis GitHub, `.env`, database backup, `composer install`, `php artisan migrate` ve `public/` document root ayari ile yapilabilir olur.

## V1 Kapsami

V1 kapsaminda yer alan ana moduller:

- Admin panel
- Organizations / customers
- Platforms
- Products / modules
- Domains
- Licenses
- License Check API
- Core Feed API
- Announcements
- Promotions
- Recommended products
- License checks / API logs
- Settings
- Dashboard

V1 kapsaminda olmayan alanlar:

- Payment
- Marketplace
- ResellerClub automation
- AI credit system
- Customer panel
- Redis
- Queue worker
- WebSocket
- Heavy reporting

Bu konular icin V1 icinde tablo, controller, route, menu veya bos hazirlik dosyasi olusturulmaz.

## Core API Standardi

V1 API kapsami iki endpoint ile sinirlidir:

- `POST /api/v1/license/check`
- `GET /api/v1/core/feed`

Genel API kurallari:

- API `routes/api.php` uzerinden tanimlanir.
- Response formatlari tutarli JSON yapisinda olur.
- API admin panel oturumuna bagimli calismaz.
- Her endpoint versiyonlu rota altinda kalir.
- Hata cevaplari guvenli olur; stack trace, SQL detayi veya secret donmez.
- Laravel rate limit mekanizmasi kullanilir.
- Moduller API hatalarinda admin panel deneyimini bozmayacak sekilde sessiz veya kontrollu uyariyla calisir.

## License Check API

Endpoint:

- `POST /api/v1/license/check`

Amac:

- Harici modulun NetwoTurk Core uzerindeki lisans durumunu dogrulamak.
- Domain, platform, product ve version bilgilerini merkezi olarak loglamak.
- Modulun admin ekraninda lisans durumunu guvenli sekilde gosterebilmesini saglamak.

Beklenen temel request alanlari:

- `license_key`
- `domain`
- `product_code`
- `platform_code`
- `version`
- `timestamp`
- `signature`

Guvenlik kurallari:

- Domain normalize edilir.
- `http://`, `https://` ve `www.` varyasyonlari kaldirilir.
- HMAC SHA256 signature kullanilir.
- Signature secret sadece `.env` icinde tutulur.
- Rate limit uygulanir.
- API loglari IP, user agent, domain, product_code, platform_code ve version tutar.

## Core Feed API

Endpoint:

- `GET /api/v1/core/feed`

Amac:

- Modullere merkezi duyuru, kampanya, onerilen modul ve guncelleme verisi saglamak.
- PrestaShop admin modul ekraninda "NetwoTurk Duyurulari" ve "Bu Modulleri de Kesfet" alanlarini beslemek.
- Gelecekte OpenCart, WordPress ve Laravel modulleri icin ortak feed davranisi sunmak.

Beklenen query alanlari:

- `domain`
- `product_code`
- `platform_code`
- `version`
- `locale`

Feed icerigi:

- Announcements
- Promotions
- Recommended products
- Updates
- Maintenance notices
- Security alerts

Feed API marketplace veya customer panel davranisi tasimaz. Sadece merkezi yayin ve bilgilendirme ihtiyacini karsilar.

## Modul Entegrasyon Standardi

PrestaShop, OpenCart, WordPress ve Laravel modulleri ortak prensiplerle entegre edilir:

- Lisans kontrolu `POST /api/v1/license/check` ile yapilir.
- Duyuru, kampanya ve onerilen modul verisi `GET /api/v1/core/feed` ile alinir.
- Modul admin ekraninda lisans alani bulunur.
- Modul admin ekraninda "NetwoTurk Duyurulari" alani bulunur.
- Modul admin ekraninda "Bu Modulleri de Kesfet" alani bulunur.
- API cevaplari modul tarafinda cache'lenebilir olur.
- Her admin ekran acilisinda gereksiz API istegi yapilmaz.
- API erisim hatasi admin paneli bozmaz.
- Hata durumunda lisans veya feed bilgisi kontrollu uyariyla ya da sessiz fallback ile yonetilir.

V1 icin ilk detayli entegrasyon hedefi PrestaShop'tur. OpenCart, WordPress ve Laravel modulleri ayni sozlesme uzerinden ileride ortak SDK mantigina tasinabilir.

## Guvenlik Standardi

Domain normalize algoritmasi:

1. Gelen domain trim edilir.
2. `http://` ve `https://` kaldirilir.
3. Baslangictaki `www.` kaldirilir.
4. Path, query ve fragment atilir.
5. Domain lowercase hale getirilir.
6. Sonda `/` varsa kaldirilir.

Signature yaklasimi:

- Imza HMAC SHA256 ile uretilir.
- Secret `.env` icinden okunur.
- Payload alanlari sabit sirayla imzalanir.
- Timestamp toleransi belirlenir.
- Gecersiz imza durumunda genel ve guvenli hata donulur.

Log standardi:

- IP
- User agent
- Normalized domain
- Raw domain
- Product code
- Platform code
- Module version
- License key hash veya referansi
- Result status
- Error code
- Checked at

Loglarda secret, tam signature veya hassas musteri bilgisi tutulmaz.

## Veritabani Taslaklari

Bu bolum sadece mimari taslaktir. Migration olusturulmayacaktir.

### organizations / customers

Musteri veya kurum kaydini temsil eder.

Olasil alanlar:

- `id`
- `name`
- `email`
- `phone`
- `company_name`
- `status`
- `notes`
- `created_at`
- `updated_at`

### platforms

Modulun calistigi platformu temsil eder.

Olasil alanlar:

- `id`
- `code`
- `name`
- `status`
- `created_at`
- `updated_at`

Ornek kodlar:

- `prestashop`
- `opencart`
- `wordpress`
- `laravel`

### products

Lisanslanabilir urun veya modulu temsil eder.

Olasil alanlar:

- `id`
- `code`
- `name`
- `description`
- `current_version`
- `status`
- `created_at`
- `updated_at`

### domains

Musteri domain kaydini temsil eder.

Olasil alanlar:

- `id`
- `organization_id` veya `customer_id`
- `domain`
- `normalized_domain`
- `status`
- `created_at`
- `updated_at`

### licenses

Urun, platform, domain ve musteri iliskisini temsil eder.

Olasil alanlar:

- `id`
- `organization_id` veya `customer_id`
- `product_id`
- `platform_id`
- `domain_id`
- `license_key`
- `status`
- `starts_at`
- `expires_at`
- `created_at`
- `updated_at`

### license_checks

API lisans kontrol kayitlarini temsil eder.

Olasil alanlar:

- `id`
- `license_id`
- `domain`
- `normalized_domain`
- `product_code`
- `platform_code`
- `version`
- `ip_address`
- `user_agent`
- `result`
- `error_code`
- `checked_at`
- `created_at`

### announcements

Merkezi duyuru icerigini temsil eder.

Olasil alanlar:

- `id`
- `title`
- `body`
- `type`
- `status`
- `starts_at`
- `ends_at`
- `created_at`
- `updated_at`

### promotions

Kampanya icerigini temsil eder.

Olasil alanlar:

- `id`
- `title`
- `description`
- `cta_label`
- `cta_url`
- `status`
- `starts_at`
- `ends_at`
- `created_at`
- `updated_at`

### recommended_products

Modul admin ekraninda onerilecek urunleri temsil eder.

Olasil alanlar:

- `id`
- `product_id`
- `target_platform_id`
- `title`
- `description`
- `url`
- `sort_order`
- `status`
- `created_at`
- `updated_at`

### settings

Genel uygulama ayarlarini temsil eder.

Olasil alanlar:

- `id`
- `key`
- `value`
- `type`
- `group`
- `is_public`
- `created_at`
- `updated_at`

Secret degerler settings icinde tutulmaz.

## Hosting ve Tasinabilirlik

V1 hedefi shared/cloud hosting uyumlulugudur.

Kurallar:

- `public/` document root olarak kalir.
- Laravel root dizini web'den direkt erisilebilir olmaz.
- `.env` public dizinine tasinmaz.
- `storage/` ve `bootstrap/cache/` yazilabilir olur.
- Cron ihtiyaci cikarsa Laravel scheduler ile tek cron prensibi kullanilir.
- Redis, Supervisor ve queue worker deployment on kosulu olmaz.

VPS'ye gecis icin gerekli temel varliklar:

- GitHub uzerindeki proje kodu
- Production `.env`
- Veritabani backup'i
- Gerekliyse `storage/` icerigi
- `composer install`
- `php artisan migrate`
- `public/` document root ayari

## Codex Calisma Kurallari

Codex ile gelistirme yapilirken:

- Once mevcut mimari ve dokumanlar okunur.
- Kod istenmediyse kod uretilmez.
- Kullanici istemedigi surece migration, controller, model veya service olusturulmaz.
- V1 disi ozellik uretilmez.
- Laravel standart dizin yapisi korunur.
- Secret uretilmez, tahmin edilmez ve dosyalara yazilmaz.
- Gereksiz paket eklenmez.
- Shared hosting uyumlulugu bozulmaz.
- Buyuk gelistirmeler sonunda `ARCHITECTURE`, `ROADMAP` ve `CHANGELOG` guncellenir.
