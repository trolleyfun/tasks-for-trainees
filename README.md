# Решение задания (4 неделя)
## Постановка задачи 
+ установить модуль миграций **sprint.migration**
+ установить миграцию из каталога **php_interface/migrations** (создаст инфоблок)  
+ написать парсер, заполняющий инфоблок элементами
+ (дополнительно) парсер должен автоматически заполнять значения свойств типа "список"
+ парсер разместить в каталоге **my_parser**
## Как пользоваться парсером
1. Подключите на сайте класс **IBlockElementLoader** из каталога **my_parser/lib**;
2. Скопируйте на сайт файл **add_elements.php**;
3. Создайте **csv**-файл, содержащий данные элементов, которые необходимо записать в инфоблок. Структура файла:
    + первая строка содержит названия столбцов
    + во второй строке записаны символьные коды соответствующих свойств инфоблока
    + символьный код столбца с названием элемента &ndash; **name**
    + символьные коды регистронезависимы
    + в последующих строках записаны значения свойств элементов
    + если свойство допускает множественные значения, значения разделяются символом "•"
    + в качестве разделителя **csv**-файла используется ","
    + пример заполненного файла &ndash; **my_parser/my_vacancy.csv**
4. Укажите в файле **add_elements.php** следующие значения:
    + путь к корневому каталогу сайта (переменная ```$_SERVER['DOCUMENT_ROOT']```)
    + символьный код инфоблока, в который надо записать элементы (константа ```IBLOCK_CODE```)
    + путь к **csv**-файлу (константа ```FILENAME```)
5. Сделайте файл **add_elements.php** исполняемым;
6. Запустите скрипт:
```
php -f ./add_elements.php
```
