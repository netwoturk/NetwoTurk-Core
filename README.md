# NetwoTürk Core

NetwoTürk ürünlerinin merkezi servisleri ve PrestaShop modülleri için ortak entegrasyon kaynağıdır.

## PrestaShop modülü geliştirirken

Her yeni modülün geliştirmesine başlamadan önce aşağıdaki belge okunmalı ve teslim kontrol listesi uygulanmalıdır:

- [NetwoTürk PrestaShop Modül Entegrasyon Standardı](docs/NETWOTURK-PRESTASHOP-MODULE-STANDARD.md)
- [Module Hub örnek yanıtı](docs/module-hub-response.example.json)

Standart şu ortak alanları kapsar:

- merkezi lisans ve güncelleme hakkı;
- NetwoTürk destek merkezi bildirimleri;
- ortak yönetici menüsü ve bildirim rozetleri;
- gösterge paneli altındaki kampanya/duyuru alanı;
- netwoturk.com satış sayfalarına yönlenen modül karuseli;
- güvenlik, veri minimizasyonu, önbellek ve zorunlu testler.

## Güvenlik

Private key, ortak API anahtarı, gerçek lisans anahtarı, müşteri belgesi veya kişisel veri bu depoya eklenmez. Lisans doğrulamanın kesin teknik sözleşmesi, NetwoTürk Tech üzerinde kurulu güncel `ntlicensemanager` kaynak kodundan doğrulanır.
