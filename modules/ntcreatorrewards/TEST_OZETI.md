# Test özeti

- Saf PHP testleri: TikTok handle normalizasyonu, sahte domain reddi, üç hassas puan örneği.
- Referans XLSX testi: 131 satır, 267.7252 USD toplam, 41 pozitif teşvik, 31 adet `Creator ID=0`, 3 eksik Handle.
- PHP sözdizimi: paketteki tüm PHP dosyaları.
- Paket yapısı: ZIP kökünde tek `ntcreatorrewards/` klasörü.
- PrestaShop 8.2.6: yerel mağaza bootstrap dosyası CLI çalıştırmasını işlem kodu 0 ile erken sonlandırdığı için kur/install/uninstall smoke testi tamamlanamadı. Modül yükleyici testi staging mağazada ayrıca yapılmalıdır.

## Gerçek sonuçlar

- PHP lint: 16/16 PHP dosyası başarılı.
- Saf testler: 9/9 assertion başarılı; puan/handle testlerine geçerli ve geçersiz TR IBAN checksum testleri eklendi.
- Referans XLSX: 131 satır, 267.72520000 USD toplam, 41 pozitif teşvik, 31 sıfır Creator ID, 3 eksik Handle, kimliksiz iki satırda 1.32640000 USD — tamamı doğrulandı.
- Test CLI'sında PHP `zip` uzantısı yoktu; XLSX XML'i güvenli, salt-okunur test shim'iyle açılarak aynı üretim parser'ı doğrulandı. Üretimde modül `zip` yoksa kurulumu reddeder.

Canlı lisans doğrulaması için lisans merkezinde `resources/license-product.json` kaydının eklenmesi ve bu ürün koduyla anahtar üretilmesi gerekir. Gerçek üretim anahtarı test paketine konmaz.
