# telegram-bot-api
A very simple PHP [Telegram Bot API](https://core.telegram.org/bots).    

Requirements
---------
* PHP >= 5.3
* Curl extension for PHP5 must be enabled.
* Telegram API key, you can get one simply with [@BotFather](https://core.telegram.org/bots#botfather) with simple commands right after creating your bot.

For the WebHook:
* An VALID SSL certificate (Telegram API requires this). You can use [Cloudflare's Free Flexible SSL](https://www.cloudflare.com/ssl) which crypts the web traffic from end user to their proxies if you're using CloudFlare DNS.    
Since the August 29 update you can use a self-signed ssl certificate.

For the getUpdates(Long Polling):
* Some way to execute the script in order to serve messages (for example cronjob)

Download
---------
#### Using Composer
From your project directory, run:
```
composer require thezahedi/telegram-bot-api
```
or
```
php composer.phar require thezahedi/telegram-bot-api
```
Note: If you don't have Composer you can download it [HERE](https://getcomposer.org/download/).
