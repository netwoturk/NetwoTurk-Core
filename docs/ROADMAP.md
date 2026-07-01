# NetwoTurk Core Roadmap

Bu roadmap, NetwoTurk Core Laravel 13 projesinin V1 kapsaminda hangi sirayla gelistirilecegini ve hangi alanlarin bilerek disarida birakilacagini tanimlar.

## Urun Hedefi

NetwoTurk Core V1, NetwoTurk tarafindan yonetilen musteriler, platformlar, urun/moduller, domainler ve lisanslar icin merkezi admin panel ve iki temel API saglar:

- `POST /api/v1/license/check`
- `GET /api/v1/core/feed`

Core, PrestaShop, OpenCart, WordPress ve Laravel tabanli modullere merkezi lisans, guncelleme, duyuru, kampanya ve onerilen modul feed'i saglayacak sekilde tasarlanir.

V1 bir satis, odeme, marketplace veya musteri self-servis sistemi degildir.

## V1 Fazlari

### Faz 1: Proje Standartlari ve Mimari Hazirlik

- Mimari dokumantasyon
- API sozlesmeleri
- PrestaShop entegrasyon taslagi
- Deployment kurallari
- Coding standards
- Laravel 13 resmi yapisinin korunmasi
- Ortam ve secret kurallarinin netlestirilmesi

Bu fazda kod uretilmez. Sadece proje kurallari, mimari ve dokumanlar hazirlanir.

### Faz 2: Temel Admin Panel

- Admin giris yapisi
- Admin layout
- Yetkili admin rotalari
- Basit dashboard
- Settings ekraninin temel yapisi

Admin panel V1'in operasyon merkezidir. Customer panel bu faza dahil degildir.

### Faz 3: Ana Veri Modulleri

- Organizations / customers
- Platforms
- Products / modules
- Domains
- Licenses

Bu fazda tum veri yapilari migration ile kurulur. Modeller ve iliskiler V1 ihtiyaci kadar sade tutulur.

### Faz 4: License Check API

- `POST /api/v1/license/check`
- Domain normalize etme
- HMAC SHA256 signature dogrulama
- Domain/platform/product dogrulama
- Lisans durum cevabi
- Tutarli JSON response yapisi
- Rate limit
- API loglari

API yalnizca V1 lisans kontrol ihtiyacina odaklanir.

### Faz 5: Core Feed API ve Icerik Modulleri

- Announcements
- Promotions
- Recommended products
- Updates
- Security alerts
- Maintenance notices
- `GET /api/v1/core/feed`
- Modul tarafi cache standardi

Bu fazda urunlerin tek merkezden duyuru, kampanya, onerilen modul, guvenlik uyarisi, guncelleme ve bakim bildirimi almasi saglanir.

### Faz 6: PrestaShop Entegrasyon Standardi

- PrestaShop admin modul ekraninda lisans alani
- Lisans dogrulama akisi
- Core feed gosterim alani
- Cache suresi
- API hata yonetimi
- Hook/servis yaklasimi
- Gelecekte OpenCart/WordPress/Laravel icin ortak SDK mantigi

Bu fazda ortak entegrasyon prensipleri netlestirilir. Kod yazimi ayri bir gelistirme talebiyle ilerler.

### Faz 7: Operasyon Modulleri

- License checks / API logs
- Settings genisletmeleri
- Dashboard metrikleri

Bu moduller admin panel uzerinden yonetilir. Marketplace veya otomatik satis davranisi eklenmez.

### Faz 8: Stabilizasyon

- Form validation kontrolleri
- Yetki kontrolleri
- Temel testler
- `php artisan test`
- Route kontrolu
- Migration kontrolu
- License Check API smoke test
- Core Feed API smoke test
- Hosting uyumluluk kontrolu
- Deployment prova adimlari
- Dokuman guncellemesi

V1 cikmadan once shared/cloud hosting calisma senaryosu mutlaka dogrulanir.

## V1 Kapsami

V1'de yer alacak ozellikler:

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

## V1 Disi Kapsam

V1'de yer almayacak ozellikler:

- Payment
- Marketplace
- ResellerClub automation
- AI credit system
- Customer panel
- Redis
- Queue worker
- WebSocket
- Supervisor
- Heavy queue system
- Heavy reporting

Bu maddeler icin V1 icinde tablo, controller, route, menu veya servis hazirligi yapilmaz.

## Oncelik Kurallari

Gelistirme sirasinda oncelik sirasi:

1. Lisans verisinin dogru modellenmesi
2. License Check API'nin kararli calismasi
3. Core Feed API'nin sade ve kararli calismasi
4. Admin panelden guvenli ve sade yonetim
5. Modul tarafi API cache ve hata toleransi
6. Shared/cloud hosting uyumlulugu
7. VPS'ye kolay tasinabilirlik
8. Gereksiz karmasadan kacinma

## Karar Alma Kurallari

Yeni bir is veya fikir icin asagidaki sorular sorulur:

- V1 kapsamindaki modullerden birine dogrudan hizmet ediyor mu?
- Shared/cloud hosting uyumlulugunu bozuyor mu?
- Laravel'in resmi proje yapisindan sapma gerektiriyor mu?
- Secret veya manuel ortam bagimliligi olusturuyor mu?
- Migration ile tasinabilir mi?
- Daha basit Laravel native cozumle yapilabilir mi?

Bu sorulardan biri riskli cevap veriyorsa ozellik ertelenir veya sadelestirilir.

## Codex Calisma Akisi

Codex gorevlerinde beklenen akis:

1. Ilgili dokumanlari oku.
2. V1 kapsam kontrolu yap.
3. Kod istenmediyse kod uretme.
4. Kullanici istemedigi surece migration, controller, model veya service olusturma.
5. Degisiklik gerekiyorsa en kucuk kapsami sec.
6. Laravel standartlarindan sapma yapma.
7. Buyuk gelistirmelerden sonra `ARCHITECTURE`, `ROADMAP` ve `CHANGELOG` guncelle.
8. Is sonunda yapilan degisiklikleri kisa ve net ozetle.
