# Режима работы компании

Класс обрабатывает расписание режима работы компании и выводит дружественное человеко-понятное сообщение о текущем состоянии. Расписание хранится в JSON файле. Например, *"Закрыто. Откроется завтра в 9:00"*.

## Использование

1. Отредактировать JSON-файл расписания (см. Настройка JSON) 

2. Подключить сущеность `include_once('timetable.php');`

3. Определить класс `$timeTable = new TimeTable;`

4. Вывести дружественное сообщение `$timeTable->friendly();`

## Методы

### Режим работы в человеко-понятном виде

`friendly()`

Метод с человеко-понятным объяснением режима работы. Например, 

`Закрыто. Откроется завтра в 10:00`


### Таблица графика работы 

`table()`

Метод выводит табличку с графиком работы в формате HTML. Например,

|               |               |
| ------------- | ------------- |
| понедельник   | 10:00 — 18:00 |
| вторник       | 10:00 — 18:00 |
| среда         | 10:00 — 18:00 |
| четверг       | 10:00 — 18:00 |
| пятница       | 10:00 — 18:00 |
| суббота       | закрыто       |
| воскресенье   | закрыто       |



## Настройка JSON

Общая структура

```
{
    "dayoff": ["sunday", "monday"],
    "from": "10:00",
    "to": "18:00",
    "break": {
        "from": "14:00",
        "to": "15:00"
    },
    "saturday":
    {
        "from": "10:00",
        "to": "14:30",
        "break": {
            "from": "12:00",
            "to": "13:00"
        }
    }
}
```

### Общие правила.

#### Формат
##### Дни недели
Все дни недели обозначаются полным названием дня недели в английском языке (допускается любой регистр).
##### Время
Время записывается в формате hh:mm. Возможна запись без ведущего нуля.

Вначале идут правила применяемые ко всем дням. 

#### Выходные
В строке `dayoff` находится массив списка выходных дней недели.
#### Режим работы
В строке `from` - время открытия, `to` - время закрытия.
#### Обеденный перерыв
В строке `break` отмечены обеденные переры.

### Специальные режимы
Для некоторых дней недели можно установить специальное распиание и обеденные перерывы.



