

Рекомендации по безопасности
=====================================================================

- Многие службы веб-хостинга по-прежнему используют программное обеспечение,
  поддержка которого прекращена.
  Например: PHP до версии 7 или MySQL до версии 5.6.
  Это плохо для безопасности и такие сервисы необходимо обновить.
  Узнать больше о поддерживаемых версиях PHP: http://php.net/supported-versions.php
  Ознакомьтесь с политикой поддержки Oracle Lifetime Support,
  чтобы определить жизненный цикл MySQL.
- Получить максимальную защиту от внутренних угроз можно только в том случае,
  если используется собственный сервер в изолированном месте с контролем доступа.
  Если Вы размещаете сервер в центре обработки данных в общей стойке,
  нет гарантии от внешнего доступа к портам или жёстким дискам.
  Рекомендуется использовать услуги хостинга с серверами,
  расположенными в европейских дата-центрах.
- Рекомендуется использовать аппаратные межсетевые экраны на основе открытых
  прошивок (например, NanoBSD и другие). Помните, что некоторые аппаратные
  брандмауэры могут содержать бэкдоры.
- Не рекомендуется покупать SSL-сертификаты с заранее сгенерированными
  закрытыми ключами в Центре сертификации (англ. Certificate Authority). 
  Нет никаких гарантий, что Ваш закрытый ключ не попадёт в третьи руки.
  Вы можете использовать процедуру Запроса на подпись сертификата (англ.
  Certificate Signing Request):
  - сгенерировать открытые и закрытые ключи с идентифицирующей информацией на вашей стороне;
  - отправить открытый ключ с идентифицирующей информацией в Центр сертификации
    для подписания открытого ключа.
- Не рекомендуется использовать общедоступные почтовые сервисы для восстановления
  паролей и отправки важной корреспонденции. Рекомендуется использовать свой собственный сервер
  для почтовых служб и использовать разные серверы для каждой службы (если это возможно).
- Для хранения закрытых ключей рекомендуется использовать защищённый аппаратный криптопроцессор.
- Рекомендуется использовать двухфакторную аутентификацию. 
- Рекомендуется использовать TLS v1.2 или выше.
