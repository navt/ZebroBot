# Zebro Bot
Telegram бот на PHP.<br>
В простом приложении (опросе) решена задача вынести логику  в некоторые слои:<br>
- Слой для работы с API Telegram (Basis),<br>
- Роутинг (Route),<br>
- Контроллеры (TextController, ButtonController),<br>
- Слой для работы с базой данных (SQLiteAccess).<br>
Настройки и константы в library/Fix.php.