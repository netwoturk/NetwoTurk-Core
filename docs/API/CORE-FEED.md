# Core Feed API

Bu dokuman NetwoTurk Core V1 Core Feed API sozlesmesini tanimlar. Dokuman sadece mimari ve API kontrati icerir; migration, controller, model veya service uretmez.

## Endpoint

`GET /api/v1/core/feed`

## Amac

Core Feed API, harici PrestaShop, OpenCart, WordPress ve Laravel modullerine merkezi NetwoTurk icerigi saglar.

Feed kapsamindaki icerikler:

- Duyurular
- Kampanyalar
- Onerilen moduller / urunler
- Guncelleme bildirimleri
- Bakim bildirimleri
- Guvenlik uyarilari

Bu endpoint marketplace veya customer panel davranisi tasimaz. V1'de sadece merkezi yayin ve bilgilendirme ihtiyacini karsilar.

## Request

Ornek:

```http
GET /api/v1/core/feed?domain=example.com&product_code=prestashop_shipping_plus&platform_code=prestashop&version=1.0.0&locale=tr
```

Query alanlari:

- `domain`: Modulun calistigi domain.
- `product_code`: Modul veya urun kodu.
- `platform_code`: Platform kodu.
- `version`: Modul version bilgisi.
- `locale`: Icerik dili. Ornek: `tr`, `en`.

Header onerisi:

- `Accept: application/json`
- `User-Agent: <platform>/<module>/<version>`

## Domain Normalize

License API ile ayni normalize standardi kullanilir:

1. `http://` ve `https://` kaldirilir.
2. Baslangictaki `www.` kaldirilir.
3. Path, query ve fragment atilir.
4. Domain lowercase hale getirilir.
5. Sonda `/` varsa kaldirilir.

## Success Response

Ornek:

```json
{
  "success": true,
  "data": {
    "generated_at": "2026-07-01T20:00:00Z",
    "cache_ttl": 21600,
    "announcements": [
      {
        "id": 1,
        "title": "NetwoTurk Core V1 hazirlaniyor",
        "body": "Merkezi lisans ve feed altyapisi V1 kapsaminda hazirlaniyor.",
        "type": "info",
        "starts_at": "2026-07-01",
        "ends_at": null
      }
    ],
    "promotions": [
      {
        "id": 10,
        "title": "Yeni modul kampanyasi",
        "description": "Secili modullerde tanitim kampanyasi.",
        "cta_label": "Incele",
        "cta_url": "https://netwoturk.com"
      }
    ],
    "recommended_products": [
      {
        "id": 20,
        "product_code": "prestashop_payment_plus",
        "title": "Payment Plus",
        "description": "PrestaShop icin ek odeme modulu.",
        "url": "https://netwoturk.com"
      }
    ],
    "updates": [],
    "maintenance_notices": [],
    "security_alerts": []
  }
}
```

## Error Response

Feed API hatalari modul admin panelini bozmaz.

Ornek:

```json
{
  "success": false,
  "error": {
    "code": "feed_unavailable",
    "message": "Feed could not be loaded."
  }
}
```

Olasil hata kodlari:

- `validation_error`
- `rate_limited`
- `feed_unavailable`
- `server_error`

## Cache Standardi

Feed verisi modul tarafinda cache'lenebilir olmalidir.

Onerilen V1 davranisi:

- Varsayilan cache suresi: 6 saat.
- API cevabindaki `cache_ttl` degeri varsa modul onu dikkate alabilir.
- Admin ekraninda her acilista feed istegi yapilmaz.
- API erisilemezse son basarili feed cache'i kullanilabilir.
- Cache yoksa feed alanlari bos veya sade uyariyla gosterilir.

## Gosterim Standardi

PrestaShop admin modul ekraninda en az iki alan hedeflenir:

- `NetwoTurk Duyurulari`
- `Bu Modulleri de Kesfet`

Gosterim prensipleri:

- Duyuru ve kampanya metinleri admin panelini kaplamaz.
- Onerilen modul kartlari sade ve tiklanabilir olur.
- Dis linkler guvenli sekilde acilir.
- Bos feed durumunda ekran bozulmaz.
- Feed icerigi lisans kontrolunden bagimsiz olarak yuklenebilir, ancak gerekirse domain/product/platform bilgisiyle hedeflenebilir.

## Log Standardi

Feed isteklerinde operasyonel izleme icin su bilgiler tutulabilir:

- IP
- User agent
- Raw domain
- Normalized domain
- Product code
- Platform code
- Version
- Locale
- Result status
- Error code
- Requested at

Secret, signature veya gereksiz kisisel veri loglanmaz.

## V1 Disi

Core Feed API V1'de su alanlari kapsamaz:

- Marketplace sepeti
- Odeme akisina yonlendirme
- Customer panel oturumu
- Modul satin alma otomasyonu
- WebSocket ile canli bildirim
- Redis zorunlu cache
