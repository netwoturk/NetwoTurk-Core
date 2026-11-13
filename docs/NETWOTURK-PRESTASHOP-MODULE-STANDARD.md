# NetwoTürk PrestaShop Modül Entegrasyon Standardı

Sürüm: 1.0.0  
Hedef: PrestaShop 9.2.x ve sonraki uyumlu sürümler

Bu belge, tüm NetwoTürk PrestaShop modüllerinin lisans, destek bildirimi, ortak yönetim menüsü, merkezi duyuru alanı ve ürün karuseli için uyması gereken sözleşmedir. Yeni bir modüle başlanmadan önce bu belgenin güncel sürümü okunmalıdır.

## 1. Değişmez mimari

Merkezi sorumluluklar birbirinden ayrıdır:

| Bileşen | Sorumluluk | Adres |
|---|---|---|
| `ntlicensemanager` | Lisans, ürün, paket, özellik ve güncelleme hakkı | `https://netwoturktech.com/module/ntlicensemanager/validate` |
| `ntsupportcenter` | Teknik destek kaydı, mesaj ve okunmamış destek bildirimi | NetwoTürk Tech üzerindeki imzalı istemci API'si |
| `ntmodulehub` | Duyuru, kampanya, reklam ve modül kataloğu | NetwoTürk Tech üzerindeki imzalı içerik API'si |

Bir modül bu görevler için kendi bağımsız merkezini kuramaz. Merkezi servis kullanılamadığında modülün ana işlevleri ve yerel müşteri verileri çalışmaya devam eder; yalnız merkezi içerik alanı uygun bir durum mesajı gösterir.

## 2. Güvenlik sınırı

- Private key, ortak API anahtarı, lisans anahtarı veya yönetici parolası Git deposuna ve dağıtım ZIP'ine konmaz.
- İstemci modüllerde yalnız mevcut lisans sisteminin public key'i bulunabilir.
- Lisans anahtarı arayüz ve loglarda maskelenir.
- Hasar belgesi, kimlik, ruhsat, fotoğraf, araç, müşteri veya iş emri verisi destek ve tanıtım API'lerine gönderilmez.
- Merkeze yalnız ürün kodu, normalize domain, PrestaShop/PHP/modül sürümü, imzalı lisans kanıtı ve gerekli en az teknik tanılama gönderilir.
- Uzak bağlantılar HTTPS olmalı; yönlendirme hedefleri yalnız izin verilen NetwoTürk alan adlarına açılmalıdır.

## 3. Her modülün zorunlu kimliği

Her modül aşağıdaki sabitleri tanımlar:

```php
public const PRODUCT_CODE = 'benzersiz_urun_kodu';
public const MODULE_FAMILY = 'netwoturk';
public const INTEGRATION_STANDARD = '1.0.0';
```

`PRODUCT_CODE` merkezdeki ürün kaydıyla bire bir eşleşir ve yayımlandıktan sonra değiştirilmez. Başka ürünün lisansı modülü açamaz.

## 4. Lisans entegrasyonu

Kesin istek, canonical payload, imza ve önbellek biçimi daima kurulu `ntlicensemanager` güncel kaynaklarından doğrulanır. Tahmini endpoint veya JSON alanı üretilmez.

Asgari ilkeler:

- İstek `product_code`, lisans anahtarı, güvenilir mağaza yapılandırmasından üretilmiş normalize domain ve gerekiyorsa mevcut sözleşmeye uygun `usage_json` içerir.
- Yanıtın RSA/SHA-256 imzası, ürün/domain eşleşmesi, kullanım süresi, durum ve özellik hakları sunucu tarafında doğrulanır.
- Yetki kontrolü yalnız menü gizlemeyle sınırlı değildir; yönetici, ön yüz, AJAX/API ve cron girişlerinde tekrarlanır.
- Kullanım hakkı ile güncelleme hakkı ayrıdır. Güncelleme süresinin bitmesi süresiz kullanımı kapatmaz.
- Yalnız imzalı lisans önbelleği kabul edilir. Ağ hatası lisansın kesin geçersiz olduğu anlamına gelmez.

## 5. Destek bildirimi sözleşmesi

Dağıtılan modüllere ortak bir `X-NT-API-Key` gömülmez. İstemci, geçerli imzalı lisans kanıtıyla kendi domain ve ürün kapsamına erişir. Merkez bu kanıtı doğrulamadan destek verisi döndürmez.

Destek özet yanıtı en az şu yapıyı sağlar:

```json
{
  "schema": "netwoturk.support-summary.v1",
  "unread_count": 2,
  "open_ticket_count": 1,
  "latest": {
    "ticket_reference": "NT-2026-000123",
    "subject": "Kısa başlık",
    "last_message_at": "2026-11-13T12:00:00+03:00"
  },
  "support_url": "https://netwoturktech.com/..."
}
```

Okunmamış sayısı müşterinin domain/lisans kapsamındaki `support` göndericili mesajlardan hesaplanır. Son görülen mesaj kimliği istemci tarafında ürün ve mağaza bazında saklanır. Teknik destek sistemi araç sahibinin servis görüşme kanalı olarak kullanılamaz.

## 6. Merkezi duyuru ve ürün kataloğu

Tüm modüllerin gösterge panelinin altında iki ortak bileşen bulunur:

1. **NetwoTürk Duyuruları:** reklam, kampanya, sürüm, bakım ve önemli bildirim kartları.
2. **NetwoTürk Modülleri:** ürün görseli, adı, kısa açıklaması ve `netwoturk.com` satış sayfasına giden karusel.

`ntmodulehub` yanıtı:

```json
{
  "schema": "netwoturk.module-hub.v1",
  "generated_at": "2026-11-13T12:00:00+03:00",
  "cache_ttl": 21600,
  "announcements": [
    {
      "id": "campaign-2026-11",
      "type": "campaign",
      "title": "Kampanya başlığı",
      "summary": "Kısa açıklama",
      "image_url": "https://netwoturktech.com/media/...",
      "target_url": "https://netwoturk.com/...",
      "priority": 50,
      "starts_at": "2026-11-01T00:00:00+03:00",
      "ends_at": "2026-11-30T23:59:59+03:00",
      "dismissible": true
    }
  ],
  "products": [
    {
      "product_code": "ntautodamage",
      "name": "Kaza / Hasar Tespit",
      "summary": "Kısa ürün açıklaması",
      "image_url": "https://netwoturktech.com/media/...",
      "sales_url": "https://netwoturk.com/...",
      "sort_order": 10,
      "is_owned": true
    }
  ]
}
```

Kurallar:

- İçerikler tarih, ürün, paket, PrestaShop sürümü ve hedef kitleye göre merkezde filtrelenebilir.
- HTML varsayılan olarak kabul edilmez; metin escape edilir. Gerekirse izinli etiket listesi açıkça tanımlanır.
- Görseller tembel yüklenir; alan için sabit en-boy oranı kullanılır.
- Karusel klavye, dokunma ve mobil ekranla uyumludur; otomatik geçiş kapatılabilir.
- Mevcut modül `is_owned` olarak işaretlenebilir ama katalogdan gizlenmek zorunda değildir.
- Dış bağlantı yeni sekmede `noopener noreferrer` ile açılır.
- Başarılı yanıt imzalı ve süreli önbelleğe alınır; bağlantı hatasında son doğrulanmış içerik gösterilebilir.

## 7. Ortak yönetim arayüzü

- PrestaShop sol menüsünde NetwoTürk ürün ailesi için ortak bir üst menü bulunur.
- Her NetwoTürk modül ekranında aynı ortak gezinme görünür.
- Menü öğeleri kurulu ve erişim yetkisi bulunan modüllerden dinamik oluşturulur; modül adına sabitlenmiş kopya listeler kullanılmaz.
- Üst/yan bildirim rozeti `destek okunmamış + modüle özel bekleyen işlem` sayılarını ayrı açıklamalarla gösterir.
- Duyuru ve ürün karuseli ana modül içeriğinin altında yer alır; kritik iş akışını engellemez.

## 8. İstemci bileşenleri

Yeni modül mümkünse ortak paket/sınıfları yeniden kullanır:

```text
src/NetwoTurk/License/LicenseClient.php
src/NetwoTurk/Support/SupportClient.php
src/NetwoTurk/Hub/ModuleHubClient.php
src/NetwoTurk/Ui/AdminNavigation.php
src/NetwoTurk/Ui/DashboardWidgets.php
views/templates/admin/_partials/netwoturk_nav.tpl
views/templates/admin/_partials/netwoturk_notifications.tpl
views/templates/admin/_partials/netwoturk_hub.tpl
```

İstemciler kısa bağlantı/okuma zaman aşımı, kontrollü yeniden deneme, imzalı önbellek ve kişisel veri içermeyen hata kaydı kullanır.

## 9. Yeni modül teslim kontrol listesi

- Kalıcı `product_code` ve merkez ürün kaydı
- Paket/özellik/limit tablosu
- Güncel merkez kaynağına göre lisans istemcisi
- Türkçe lisans ayar ve maskeli durum ekranı
- Bütün giriş noktalarında sunucu tarafı özellik kontrolü
- Ortak NetwoTürk yönetim menüsü
- Destek bildirim rozeti ve son-okunan kaydı
- Duyuru alanı ve erişilebilir ürün karuseli
- NetwoTürk alan adı URL izin listesi
- İmzalı, süreli önbellek ve kesinti davranışı
- PrestaShop 9.2.x kurulum/yükseltme betiği
- Mevcut ayar ve verileri koruyan idempotent yükseltme
- Gizli bilgi ve kişisel veri taraması

## 10. Zorunlu testler

- Doğru/yanlış ürün ve domain lisansı
- Geçersiz/değiştirilmiş imza; askıya alınmış ve süresi dolmuş lisans
- Süresiz kullanım + bitmiş güncelleme hakkı
- Yetkisiz özelliğe doğrudan HTTP/AJAX/cron isteği
- Lisans, destek ve içerik merkezlerinin ayrı ayrı kesintisi
- Başka domainin destek mesajına erişim denemesi
- Okunmamış sayısının okuma sonrası sıfırlanması
- Süresi geçmiş veya hedef dışı duyurunun gösterilmemesi
- Zararlı HTML ve izin verilmeyen yönlendirme adresi
- Mobil/klavye karusel kullanımı
- Yükseltme sonrası lisans anahtarı, ayar ve son-okunan değerlerinin korunması
- Modül kaldırmada kullanıcı verilerinin açık onay olmadan silinmemesi

## 11. Sürümleme

Bu sözleşme semantik sürümlenir. Geriye uyumsuz değişiklik ana sürümü artırır. Modülün desteklediği standart sürümü kodda sabittir; merkez en az bir önceki aktif sözleşmeyi geçiş süresince destekler.

## 12. Kaynak önceliği

Bu belge ortak davranışı tanımlar. Lisans doğrulamanın kesin teknik sözleşmesi her zaman merkezi `ntlicensemanager` modülünün güncel kaynak kodudur. Kaynağı incelemeden yeni endpoint, imza biçimi, canonical payload veya lisans üretme yöntemi uydurulamaz.


---JSON---
{
  "schema": "netwoturk.module-hub.v1",
  "generated_at": "2026-11-13T12:00:00+03:00",
  "cache_ttl": 21600,
  "announcements": [],
  "products": []
}

