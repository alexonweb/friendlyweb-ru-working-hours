<?

/*
 * @todo Прикрепить производственный календарь
 * @todo добавить пространтсов имен
*/

class TimeTable extends DateTime {

  private $timeTableFile = "data/timetable.json";  // Файл с режимом работы

  private $calendFile = ""; // Файл производственного календаря (будет попозже)

  private $daysOfTheWeek = array("monday", "tuesday", "wednesday", "thursday", "friday", "saturday", "sunday");
  
  private $daysOfTheWeek_ru = array( 
    "monday"      => "понедельник", 
    "tuesday"     => "вторник", 
    "wednesday"   => "среда", 
    "thursday"    => "четверг", 
    "friday"      => "пятница", 
    "saturday"    => "суббота", 
    "sunday"      => "воскресенье" );


  /*
  * Метод для быстрого получения объекта из json-файла с данными
  * Возвращает объект
  */ 
  private function fileGetJson( $file )
  {

    if ( file_exists( $file ) ) 
    {

      $contents = file_get_contents($file);

      $obj = json_decode($contents);

    }

    return $obj;

  }

  /*
  * Метод преобразует время (например, 10:00) в объект DateTime 
  * Возвращает объект DateTime
  */
  private function timeToDateTime($time) 
  {

    $format = $this->format("Y-m-d ") . $time . ":00";

    $datetime = new DateTime( $this->format($format) );

    return $datetime;

  }

  /*
  * Метод преобразует объект DateTime в название дня недели
  * Возвращает день недели в нижнем регистре
  */
  private function getDayOfTheWeek ($dateNow = "now")
  {
    $dateTimeNow = new DateTime($dateNow);

    $nowdayofweek = strtolower ( $dateTimeNow->format('l') );

    return $nowdayofweek;

  }

  /*
  * Метод проверяет выходной ли день
  * Возращает логический тип
  */
  private function isDayoff($timeTable, $dateComparison = "now") 
  {

    $isDayoff = false;

    $nowdayoftheweek = $this->getDayOfTheWeek( $dateComparison );

    foreach ($timeTable->dayoff as $dayoff) 
    {

      $dayoff = strtolower ( $dayoff );

      if ( $nowdayoftheweek == $dayoff ) {

        $isDayoff = true;

        break;

      }

    }

    return $isDayoff;

  }


  /*
  * Метод опрелеяет прошло ли время 
  * $datetime в формате "H:i"
  * Возращает логический тип
  */
  private function timeInterval($datetime) 
  {

    $datetime = $this->timeToDateTime($datetime); # Преобразуем в объект DateTime

    $interval = $this->diff($datetime); # Получаем разницу с текущем временем

    # $H = $interval->format("%R%H"); $I = $interval->format("%R%I"); return array($H, $I);

    $theTimeHasGone = $interval->format("%R");

    $theTimeHasGone = ($theTimeHasGone == "-" ? true : false); 

    return $theTimeHasGone;

  }


  /*
  * Метод возвращает время открытия и закрытия с поправкой на различия расписания на разные дни недели
  * Возвращает объект вида $timesMode->open
  */
  private function timesMode ($timeTable, $nowDayOfTheWeek) 
  {

    $specialtimetable = property_exists($timeTable, $nowDayOfTheWeek); // Специальное расписание на сегодняшний день

    if ( $specialtimetable ) 
    {

      $alreadyOpenTime = $timeTable->$nowDayOfTheWeek->from;

      $alredyClosedTime = $timeTable->$nowDayOfTheWeek->to;

    }
      else
    {

      $alreadyOpenTime = $timeTable->from;

      $alredyClosedTime = $timeTable->to;

    }

    $timesMode = (object) array(
      "open" => $alreadyOpenTime, 
      "close" => $alredyClosedTime 
    );

    return $timesMode;

  }


  /*
  * Метод выводт таблицу расписания работы
  * Возвращает HTML таблицу table>tr*7>td*2
  */
  public function table() 
  {

    $timeTable = $this->fileGetJson($this->timeTableFile);

    $daysOfTheWeek = array_merge ($this->daysOfTheWeek, $this->daysOfTheWeek); // @triky для начала списка с сегодняшнего дня

    $todayIsDayOfTheWeek = $this->getDayOfTheWeek();

    $passedToday = false; // @triky сегодняшний день недели пройден в итерации цикла foreach

    $idays = 0;

    echo "<table>";

    foreach ( $daysOfTheWeek as $dayOfTheWeek )
    {

      $dayOfTheWeek = strtolower($dayOfTheWeek);

      $htmlClassToday = "";

      if ( $todayIsDayOfTheWeek == $dayOfTheWeek )
      { 

        $passedToday = true;

        $htmlClassToday = " class=\"today\""; 

      }

      if ( $passedToday && $idays < 7 )
      {

        echo "<tr$htmlClassToday><td>";

        echo !!($htmlClassToday) ? "<strong>" : false; // @triky !!() если не пустое значение

        echo $this->daysOfTheWeek_ru[$dayOfTheWeek];

        echo !!($htmlClassToday) ? "</strong>" : false;

        echo "</td><td>"; 

        $date = new DateTime('now');  // @triky сбрасываем дату

        $date->modify('+' . $idays . ' day'); // @triky прибавляем нужное количество дней
        
        $dayOff = $this->isDayoff($timeTable, $date->format('Y-m-d H:i') ) ;
        
        if ($dayOff) 
        {

          echo "<em>закрыто</em>";

        }

        else 
        
        {

         $timeMode = $this->timesMode ($timeTable, $dayOfTheWeek);

         echo $timeMode->open . " &mdash; " . $timeMode->close;


        }

        echo "</td></tr>";

        $idays++;

      } 

        elseif ( $idays >= 7 )

      {

        break; // зачем зря крутить циклы, не спинер же

      }
      
      // echo $i++; // отладочка

    }

    echo "</table>";

  }


  /*
  * Метод с человеко-понятным объяснением режима работы 
  */
  public function friendly() 
  {

    $timeTable        = $this->fileGetJson($this->timeTableFile);
    $nowDayOfTheWeek  = $this->getDayOfTheWeek();
    $timeMode         = $this->timesMode ($timeTable, $nowDayOfTheWeek);
    $isDayoff         = $this->isDayoff($timeTable);

    if ( !$isDayoff ) 
    {

      $alreadyOpen = $this->timeInterval($timeMode->open);

      $alredyClosed = $this->timeInterval($timeMode->close);

      if ( $alreadyOpen and $alredyClosed ) // время откытия и закрытия уже прошло
      {

        $closed = true; // Уже закрыто на сегодня

      }
      elseif ( !$alreadyOpen and !$alredyClosed )  // время открытия и закрытия еще не наступило
      {

        echo "Откроется в " . $timeMode->open; // Еще закрыто

      }
      elseif ( $alreadyOpen and !$alredyClosed )  // время открытия прошло и время закрытия еще не прошло
      {

        echo "Сегодня работает до " . $timeMode->close; // открыто 

                /* Перерыв на обед @todo вынести в отдельный метод */
        echo " (перерыв ";

        $breakFrom = $timeTable->$nowDayOfTheWeek->break->from;
        $breakTo = $timeTable->$nowDayOfTheWeek->break->to;

        if (!$breakFrom) {
          $breakFrom = $timeTable->break->from;
          $breakTo = $timeTable->break->to;
        } 

        echo "с " . $breakFrom . " до " . $breakTo . ")";
        /* --- */


      };

    }
    else
    {

      $closed = true; // Закрыто потому что выходной

    }

    // Как насчет завтра?

    if ( $closed )
    {

      echo "Закрыто";

      $date = $this->timeToDateTime("12:00");

      $date->modify('+1 day');

      $isDayoff = $this->isDayoff($timeTable, $date->format('Y-m-d H:i')  );
      
      if ( !$isDayoff ) 
      {

        $nowDayOfTheWeek = $this->getDayOfTheWeek( $date->format('Y-m-d H:i') );

        $timeMode = $this->timesMode ($timeTable, $nowDayOfTheWeek);

        echo ". Откроется завтра в " . $timeMode->open;

      }

    }

  }

}


?>