# License Check API

Bu dokuman NetwoTurk Core V1 License Check API sozlesmesini tanimlar. Dokuman sadece mimari ve API kontrati icerir; migration, controller, model veya service uretmez.

## Endpoint

`POST /api/v1/license/check`

## Amac

Harici PrestaShop, OpenCart, WordPress ve Laravel modullerinin lisans durumunu merkezi NetwoTurk Core uzerinden dogrulamasini saglar.

V1 hedefleri:

- Lisans anahtarinin gecerliligini kontrol etmek.
- Domain, platform, product ve modul version bilgisini dogrulamak.
- Lisans kontrol olaylarini operasyonel log olarak kaydetmek.
- Modul admin ekranina sade ve guvenli bir lisans durumu dondurmek.

## Request Headers

Onerilen header'lar:

- `Accept: application/json`
- `Content-Type: application/json`
- `User-Agent: <platform>/<module>/<version>`

## Request Body

Ornek:

```json
{
  "license_key": "NTW-XXXX-XXXX-XXXX",
  "domain": "https://www.example.com/admin",
  "product_code": "prestashop_shipping_plus",
  "platform_code": "prestashop",
  "version": "1.0.0",
  "timestamp": 1782921600,
  "signature": "hex_hmac_sha256_signature"
}
```

Alanlar:

- `license_key`: Modul admin ekranindan girilen lisans anahtari.
- `domain`: Modulun calistigi domain.
- `product_code`: NetwoTurk urun veya modul kodu.
- `platform_code`: Platform kodu. Ornek: `prestashop`, `opencart`, `wordpress`, `laravel`.
- `version`: Modul version bilgisi.
- `timestamp`: Imza zaman kontrolu icin Unix timestamp.
- `signature`: HMAC SHA256 imza.

## Domain Normalize Kurali

API gelen domain'i normalize ederek saklar ve karsilastirir.

Kurallar:

1. Bosluklar temizlenir.
2. `http://` ve `https://` kaldirilir.
3. Baslangictaki `www.` kaldirilir.
4. Path, query ve fragment kaldirilir.
5. Domain lowercase hale getirilir.
6. Sonda `/` varsa kaldirilir.

Ornekler:

- `https://www.example.com/admin` -> `example.com`
- `http://example.com/` -> `example.com`
- `WWW.Example.COM?x=1` -> `example.com`

## Signature Standardi

Imza HMAC SHA256 ile uretilir.

Secret:

- Sadece NetwoTurk Core `.env` icinde tutulur.
- Repository, config, dokuman veya veritabanina yazilmaz.
- Modul paketine gomulmez.

Payload alanlari sabit sirayla imzalanir:

- `license_key`
- normalized `domain`
- `product_code`
- `platform_code`
- `version`
- `timestamp`

Onerilen canonical string:

```text
license_key=<license_key>&domain=<normalized_domain>&product_code=<product_code>&platform_code=<platform_code>&version=<version>&timestamp=<timestamp>
```

Modul ve Core ayni canonical string'i kullanmalidir. Timestamp toleransi V1 implementasyonunda belirlenir ve dokumanda guncellenir.

## Success Response

Ornek aktif lisans cevabi:

```json
{
  "success": true,
  "data": {
    "license_status": "active",
    "license_valid": true,
    "domain": "example.com",
    "product_code": "prestashop_shipping_plus",
    "platform_code": "prestashop",
    "expires_at": "2027-07-01",
    "message": "License is active."
  }
}
```

Olasil `license_status` degerleri:

- `active`
- `inactive`
- `expired`
- `suspended`
- `domain_mismatch`
- `product_mismatch`
- `platform_mismatch`
- `not_found`

## Error Response

Guvenli hata cevabi ornegi:

```json
{
  "success": false,
  "error": {
    "code": "invalid_signature",
    "message": "License check could not be completed."
  }
}
```

Olasil hata kodlari:

- `validation_error`
- `invalid_signature`
- `timestamp_expired`
- `license_not_found`
- `domain_mismatch`
- `product_mismatch`
- `platform_mismatch`
- `rate_limited`
- `server_error`

Hata mesajlari modul admin ekraninda kontrollu gosterilebilecek kadar acik, saldirgana sistem detayi vermeyecek kadar sinirli olmalidir.

## Rate Limit

Laravel'in standart rate limit mekanizmasi kullanilir.

V1 prensipleri:

- Domain, IP ve lisans anahtari kombinasyonu izlenebilir.
- Admin ekraninda her sayfa acilisinda lisans kontrolu yapilmamasi icin modul tarafi cache kullanir.
- Rate limit asiminda modul admin paneli bozulmaz; son bilinen durum veya kontrollu uyari gosterilir.

## Log Standardi

Her lisans kontrol denemesi icin operasyonel log tutulur.

Tutulacak bilgiler:

- IP
- User agent
- Raw domain
- Normalized domain
- Product code
- Platform code
- Version
- License referansi veya hash
- Result status
- Error code
- Checked at

Loglarda su bilgiler tutulmaz:

- API secret
- Tam imza
- Gereksiz kisisel veri
- Stack trace veya SQL detayi

## Modul Tarafi Davranis

Modul lisans kontrol API'sine baglanirken:

- API istegini cache'leyebilir.
- Admin ekraninda gereksiz her acilista API istegi yapmaz.
- API timeout veya 5xx durumunda admin paneli bozmaz.
- Son bilinen lisans durumunu kontrollu sekilde kullanabilir.
- Kritik lisans gecersizligi varsa modul ozelliklerini V1 urun kararina gore sinirlayabilir.

## V1 Disi

Bu API V1'de su davranislari kapsamaz:

- Odeme alma
- Marketplace satin alma
- Customer self-servis paneli
- Redis tabanli zorunlu cache
- Queue worker gerektiren asenkron lisans akisi
