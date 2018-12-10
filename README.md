# Zebro Bot
Telegram бот на PHP.<br>
В простом приложении (опросе) решен вопрос о  разделении задач. Каждая такая задача была вынесена в отдельный слой.<br>
В результате получились:<br>
- Слой для работы с API Telegram (Basis),<br>
- Роутинг (Route),<br>
- Контроллеры (TextController, ButtonController),<br>
- Слой для работы с базой данных (SQLiteAccess).<br>
Настройки и константы в library/Fix.php.