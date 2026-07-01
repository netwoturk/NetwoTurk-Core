# PrestaShop Modul Entegrasyon Standardi

Bu dokuman NetwoTurk Core V1 ile PrestaShop modullerinin nasil entegre olacagini tanimlar. Dokuman sadece mimari ve entegrasyon taslagi icerir; PHP modul kodu, controller, service veya migration uretmez.

## Amac

PrestaShop modulleri NetwoTurk Core uzerinden:

- Lisans dogrulamasi yapar.
- Duyuru ve kampanya verisi alir.
- Onerilen NetwoTurk modullerini gosterir.
- API hatalarinda PrestaShop admin panelini bozmaz.

## Admin Modul Ekrani

Her NetwoTurk PrestaShop modulunun admin konfigurasyon ekraninda su alanlar bulunur:

- Lisans anahtari giris alani
- Lisans durum bilgisi
- Son lisans kontrol zamani
- `NetwoTurk Duyurulari`
- `Bu Modulleri de Kesfet`

Lisans alani sade tutulur. Admin kullanici lisans anahtarini kaydettiginde modul License Check API'ye baglanarak durumu guncelleyebilir.

## Lisans Dogrulama Akisi

Onerilen akis:

1. Admin modul ayarlarina lisans anahtarini girer.
2. Modul mevcut shop domain'ini alir.
3. Domain normalize standardina uygun domain uretilir.
4. Modul `product_code`, `platform_code=prestashop`, `version` ve `timestamp` alanlarini hazirlar.
5. Modul HMAC SHA256 signature uretir.
6. Modul `POST /api/v1/license/check` endpoint'ine istek yapar.
7. Basarili cevapta lisans durumu PrestaShop configuration/cache alaninda saklanir.
8. Hata durumunda admin panel bozulmaz; kontrollu uyari veya son bilinen durum gosterilir.

## License Check Request

PrestaShop modulunun gonderecegi temel payload:

```json
{
  "license_key": "NTW-XXXX-XXXX-XXXX",
  "domain": "example.com",
  "product_code": "prestashop_shipping_plus",
  "platform_code": "prestashop",
  "version": "1.0.0",
  "timestamp": 1782921600,
  "signature": "hex_hmac_sha256_signature"
}
```

## Core Feed Gosterim Alani

PrestaShop admin ekraninda feed iki ana bolumde gosterilir:

- `NetwoTurk Duyurulari`
- `Bu Modulleri de Kesfet`

Feed request ornegi:

```http
GET /api/v1/core/feed?domain=example.com&product_code=prestashop_shipping_plus&platform_code=prestashop&version=1.0.0&locale=tr
```

Gosterim prensipleri:

- Duyurular kisa ve okunabilir bloklar halinde gosterilir.
- Kampanyalar CTA linkiyle gosterilebilir.
- Onerilen moduller baslik, kisa aciklama ve link ile gosterilir.
- Feed yoksa alan bos veya sade bilgi mesaji ile kalir.
- Feed hatasi PrestaShop konfigurasyon sayfasini durdurmaz.

## Cache Suresi

API yukunu azaltmak icin modul tarafinda cache kullanilir.

Onerilen V1 cache davranisi:

- Lisans kontrol sonucu: 12 saat cache.
- Core feed sonucu: 6 saat cache.
- Admin lisans anahtarini kaydettiginde lisans cache'i hemen yenilenir.
- Feed API cevabinda `cache_ttl` varsa modul bu degeri dikkate alabilir.
- API erisilemezse son basarili cache kullanilir.

Bu yaklasim her admin ekran acilisinda gereksiz API yukunu engeller.

## API Hata Yonetimi

PrestaShop modul tarafinda API hatalari kontrollu ele alinir.

Kurallar:

- Timeout admin paneli bozmaz.
- 4xx veya 5xx cevaplarda sayfa render'i durmaz.
- Feed hatasinda feed bolumu bos kalabilir veya kisa uyari gosterilebilir.
- Lisans kontrol hatasinda son bilinen durum gosterilebilir.
- Hata detayi admin kullaniciya gereksiz teknik ayrinti olarak yansitilmaz.
- Secret, signature veya request payload'i PrestaShop admin ekraninda gosterilmez.

## Hook ve Servis Yaklasimi

PrestaShop tarafinda entegrasyon sade tutulur.

Onerilen yaklasim:

- Modul konfigurasyon ekraninda lisans formu ve feed bloklari bulunur.
- Lisans dogrulama ve feed alma davranisi modul icindeki kucuk servis siniflariyla ayrilabilir.
- PrestaShop Configuration veya uygun cache mekanizmasi son durum ve feed verisini saklamak icin kullanilir.
- Hook kullanimi sadece admin ekraninda gosterim veya gerekli PrestaShop yasam dongusu ihtiyaci varsa tercih edilir.
- Her hook calismasinda API istegi yapilmaz.

V1 icin entegrasyon PrestaShop yapisini agirlastirmayacak ve kurulumu zorlastirmayacak sekilde planlanir.

## Ortak SDK Mantigi

PrestaShop ilk detayli entegrasyon hedefidir. Ancak License API ve Core Feed API sozlesmesi platform bagimsiz tasarlanir.

Gelecekte ortak SDK mantigi:

- Domain normalize helper'i
- HMAC SHA256 signature helper'i
- License check client
- Core feed client
- Cache adapter arayuzu
- Hata yonetimi standardi

OpenCart, WordPress ve Laravel modulleri ayni API sozlesmesini kullanarak farkli platform adapter'lari ile entegre edilebilir.

## Guvenlik Notlari

- Secret sadece NetwoTurk Core `.env` icinde tutulur.
- Modul paketine Core secret gomulmez.
- Modul tarafinda gerekiyorsa module-specific public credential veya license key kullanilir.
- Admin ekraninda secret gosterilmez.
- Domain normalize Core standardi ile ayni olmalidir.
- API timeout degeri kisa tutulur.

## V1 Disi

PrestaShop V1 entegrasyonunda su davranislar yer almaz:

- PrestaShop uzerinden odeme alma
- Marketplace sepeti
- Customer panel oturumu
- WebSocket bildirim
- Queue worker gerektiren is akisi
- Redis zorunlu cache
