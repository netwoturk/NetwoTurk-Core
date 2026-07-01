# NetwoTurk Core Coding Standards

Bu dokuman NetwoTurk Core Laravel 13 projesinde uygulanacak gelistirme standartlarini tanimlar. Standartlarin amaci, Laravel'in resmi yapisindan cikmadan sade, tasinabilir ve bakimi kolay bir V1 uygulamasi gelistirmektir.

## Ana Ilkeler

- Sistem sisirilmeyecek.
- V1 kapsamindan cikilmayacak.
- Laravel'in resmi klasor ve uygulama yapisi korunacak.
- `public/` document root olarak korunacak.
- Shared/cloud hosting uyumlulugu her kararda dikkate alinacak.
- VPS'ye ileride kolay tasinabilirlik korunacak.
- Secret `.env` disinda tutulmayacak.
- Her veritabani degisikligi migration ile tasinabilir olacak.
- Redis, WebSocket, Supervisor veya agir queue sistemi V1'e eklenmeyecek.
- Gereksiz paket, servis veya altyapi bagimliligi eklenmeyecek.

## Laravel Standartlari

Laravel'in resmi pratikleri temel alinir.

Kurallar:

- Controller'lar HTTP akisini yonetir.
- Controller'lar ince tutulur.
- Is mantigi gerekli oldugunda sade service siniflarina tasinir.
- Model'ler Eloquent iliskilerini ve model davranislarini tasir.
- Form validation icin Laravel request validation veya Form Request yapisi kullanilir.
- Yetki kontrolleri icin Laravel policy/gate yaklasimi tercih edilir.
- Route'lar `routes/web.php` ve `routes/api.php` icinde amacina uygun tutulur.
- Config dosyalari secret degil, ortamdan gelen degerleri okuyan ayar noktalaridir.
- Migration ve seeder yapilari Laravel standartlarina uygun kullanilir.
- Blade ve Laravel view yapisi admin panel icin resmi ve sade cozum olarak kabul edilir.

V1 icin Laravel'in disina tasan ozel framework katmanlari, servis calistiricilar veya agir mimari soyutlamalar eklenmez.

## API Standartlari

V1 API kapsami iki endpoint ile sinirlidir:

- `POST /api/v1/license/check`
- `GET /api/v1/core/feed`

API kurallari:

- API rotalari `routes/api.php` icinde tanimlanir.
- JSON response yapilari tutarli olur.
- Basarili ve hatali cevap formatlari onceden belirlenir.
- Validation hatalari Laravel standartlarina uygun doner.
- Gizli sistem bilgileri API cevabina yazilmaz.
- API admin session'a bagimli olmaz.
- Rate limit icin Laravel'in standart mekanizmalari kullanilir.
- Endpoint isimleri sade ve versiyonlamaya uygun secilir.

License API kurallari:

- Endpoint `POST /api/v1/license/check` olarak tanimlanir.
- Domain normalize edilir.
- `http://`, `https://` ve `www.` varyasyonlari kaldirilir.
- Signature icin HMAC SHA256 kullanilir.
- Secret yalnizca `.env` icinde tutulur.
- IP, user agent, domain, product_code, platform_code ve version loglanir.

Core Feed API kurallari:

- Endpoint `GET /api/v1/core/feed` olarak tanimlanir.
- Duyuru, kampanya, onerilen modul, guvenlik uyarisi, guncelleme ve bakim bildirimi icin tek merkezli feed saglar.
- Marketplace veya musteri paneli davranisi icermez.
- Modul tarafi cache davranisini destekleyecek response tasarimi kullanilir.

## Modul Entegrasyon Standartlari

PrestaShop, OpenCart, WordPress ve Laravel modulleri ortak API sozlesmelerini kullanir.

Kurallar:

- Modul admin ekraninda lisans alani bulunur.
- Modul admin ekraninda `NetwoTurk Duyurulari` alani bulunur.
- Modul admin ekraninda `Bu Modulleri de Kesfet` alani bulunur.
- License check API istegi cache'lenebilir olur.
- Core feed API istegi cache'lenebilir olur.
- Her admin ekran acilisinda gereksiz API istegi yapilmaz.
- API timeout veya hata durumunda admin panel bozulmaz.
- Hata mesajlari kontrollu ve sade tutulur.

## Veritabani Standartlari

Veritabani migration'lar ile yonetilir.

Kurallar:

- Tablolar ve kolonlar acik, okunabilir ve Laravel uyumlu isimlendirilir.
- Foreign key ve indeksler ihtiyaca gore migration'da tanimlanir.
- Soft delete sadece gercek ihtiyac varsa kullanilir.
- JSON kolonlar, iliskisel modelin yerine gecmek icin kontrolsuz kullanilmaz.
- Status alanlari acik ve belgelenebilir degerler tasir.
- Manuel production SQL degisiklikleri standart disidir.

V1 icin taslak tablo alanlari dokumanda belirtilebilir; ancak migration sadece kullanici acikca istediginde olusturulur.

## Guvenlik Standartlari

Temel guvenlik Laravel'in standart mekanizmalariyla saglanir.

Kurallar:

- Secret `.env` disinda tutulmaz.
- Admin panel yetkisiz erisime kapali olur.
- Form inputlari validate edilir.
- CSRF korumasi web formlarinda aktif kalir.
- API icin uygun rate limit uygulanir.
- Authorization kontrolleri ihmal edilmez.
- Hata mesajlari kullaniciya yeterli, saldirgana yetersiz bilgi verir.
- Production ortamda debug kapali olur.
- API loglarinda secret, tam signature veya hassas veri tutulmaz.

## Log Standartlari

License API ve Core Feed API icin operasyonel loglar sade tutulur.

Tutulabilecek alanlar:

- IP
- User agent
- Raw domain
- Normalized domain
- Product code
- Platform code
- Version
- Result status
- Error code
- Timestamp

Agir audit veya event sourcing sistemi V1 kapsaminda kurulmaz.

## Settings Standartlari

Settings modulu uygulamanin yonetilebilir genel ayarlarini tutar.

Kurallar:

- Secret settings icinde tutulmaz.
- Ortama gore degisen hassas bilgiler `.env` icinde kalir.
- Settings migration ile tasinabilir yapida olur.
- Admin panelden degistirilen ayarlar sade ve belgelenebilir olur.

## Paket ve Bagimlilik Kurallari

Yeni paket eklemeden once zorunluluk degerlendirilir.

Paket ekleme kriterleri:

- Laravel'in native cozumleri yetersiz kalmali.
- Paket aktif bakimli olmali.
- Shared hosting uzerinde sorun cikarmamali.
- V1 kapsamindaki gercek bir ihtiyaci cozmeli.
- Sistemi gereksiz buyutmemeli.

Sadece kolaylik icin agir paket eklenmez.

## Test ve Kalite

Test kapsami risk ile orantili tutulur.

Oncelikli test alanlari:

- License Check API
- Core Feed API
- Lisans durum kurallari
- Domain normalize davranisi
- Signature dogrulama
- Admin panel validation
- Kritik model iliskileri
- Settings davranislari

Kod yazildiktan sonra beklenen kontroller:

- `php artisan test`
- Route kontrolu
- Migration kontrolu

Bu kontroller calistirilamiyorsa sebebi is sonunda acikca yazilir.

## Git Standartlari

Git kullanimi asagidaki kurallara gore yapilir:

- GitHub ana kaynak kabul edilir.
- `main` branch sadece stabil surum icindir.
- Yeni gelistirmeler feature branch uzerinde yapilir.
- Commit mesajlari aciklayici olur.
- Kullanici istemedikce commit veya push yapilmaz.
- Calisma alanindaki kullanici degisiklikleri geri alinmaz.

## Codex Calisma Standartlari

Codex ile calisirken:

- Kullanici sadece dokuman istediyse kod uretilmez.
- Kullanici istemedigi surece migration, controller, model veya route olusturulmaz.
- Once proje dokumanlari ve mevcut yapi okunur.
- V1 kapsam kontrolu yapilir.
- Laravel resmi yapisindan sapma onerilmez.
- Secret uretilmez veya dosyaya yazilmaz.
- Gereksiz paket ve altyapi eklenmez.
- Deneme amacli kod birakilmaz.
- Yapilan is sonunda hangi dosyalarin degistigi acikca belirtilir.
