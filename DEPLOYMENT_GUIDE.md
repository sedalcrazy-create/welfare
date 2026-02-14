# راهنمای Deploy مینی‌اپ بله روی سرور

## مراحل Deploy

### 1. اجرای دستورات روی سرور:

```bash
cd /var/www/welfare
git pull origin main
cd resources/mini-app
npm install
npm run build
cd ../..
sudo chown -R www-data:www-data public/mini-app
sudo chmod -R 755 public/mini-app
php artisan config:clear
php artisan cache:clear
```

### 2. تنظیم nginx:

فایل `/etc/nginx/sites-available/welfare` را ویرایش کنید و این بخش را اضافه کنید:

```nginx
location /welfare/mini-app {
    alias /var/www/welfare/public/mini-app;
    try_files $uri $uri/ /welfare/mini-app/index.html;
    
    add_header Access-Control-Allow-Origin "https://tapp-api.bale.ai" always;
}
```

### 3. Restart nginx:

```bash
sudo nginx -t
sudo systemctl restart nginx
```

### 4. تست:

https://ria.jafamhis.ir/welfare/mini-app/
