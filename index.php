<?php
	require("connection.php"); //соединение с БД
	session_start(); //подключение сессионных переменных
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/styles.css">
    <title>Курсы валют</title>
</head>
<body>
    <div class="wrapper">
        <form method="post" action="index.php" class="form-rate">
            <?php
                if (isset($_POST['selectCurrency'])) {
                    $query = "SELECT * FROM `Rates` WHERE `currencyRate`='$_POST[currencySelect]'"; // запрос mysql выбранного курса
                    $res = mysqli_query($link, $query) or die(mysqli_error($link)); // отправление запроса в БД
                    $row = mysqli_fetch_assoc($res); // получение результата в массив
                    if ($row['idRate'] != null) { //проверка на то, существует ли такая запись в БД
                        if ($row['dateRate'] != date("Y-m-d")) { // если курса этой валюты сегодняшнего дня нет, вытаскиваем с json
                            $ch = curl_init('https://www.cbr-xml-daily.ru/latest.js');
                            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                            curl_setopt($ch, CURLOPT_HEADER, false);
                            $json = curl_exec($ch);
                            curl_close($ch);
                            $result = json_decode($json);
                            $inputCurrency = $_POST['currencySelect'];
                            $rate = $result->rates->$inputCurrency;
                            $date = date("Y-m-d");
                            $query = "UPDATE `Rates` SET `dateRate`='$date', `exchangeRate`='$rate' WHERE `currencyRate`='$_POST[currencySelect]'";
                            $res = mysqli_query($link, $query) or die(mysqli_error($link)); // отправление запроса в БД
                            $_POST['exchangeRate'] = $rate;
                            if ($rate != 0) {
                                $exchangeRate = 1 / $rate;
                            } else {
                                $exchangeRate = $rate;
                            }
                            $_POST['currencyRate'] = $row['currencyRate'];
                            $inputCurrency = '1 '.$row['currencyRate'].' - '.$rate.'₽';
                            $inputRub = '1₽ - '.$exchangeRate.' '.$rate;
                        } else { // если курс этой валюты сегодняшнего дня уже есть, вытаскиваем из БД
                            $_POST['exchangeRate'] = $row['exchangeRate'];
                            if ($row['exchangeRate'] != 0) {
                                $exchangeRate = 1 / $row['exchangeRate'];
                            } else {
                                $exchangeRate =  $row['exchangeRate'];
                            }
                            $_POST['currencyRate'] = $row['currencyRate'];
                            $inputCurrency = '1 '.$row['currencyRate'].' - '.$row['exchangeRate'].'₽';
                            $inputRub = '1₽ - '.$exchangeRate.' '.$row['currencyRate'];
                        }
                    }
                }
            ?>
            <div class="currency-part-form-rate">
                <h2>Валюта:</h2>
                <select class="select-currency" name="currencySelect"> 
                    <?php
                        $query = "SELECT `currencyRate`, `descriptionRate` FROM `Rates`";
                        $res = mysqli_query($link, $query) or die(mysqli_error($link));
                        for ($currencies = []; $row = mysqli_fetch_assoc($res); $currencies[] = $row);
                        foreach ($currencies as $currency) { 
                            if ($currency['currencyRate'] != $_POST['currencySelect']) {
                                echo '<option value="'.$currency['currencyRate'].'">'.$currency['currencyRate'].' - '.$currency['descriptionRate'].'</option>';
                            } else {
                                echo '<option value="'.$currency['currencyRate'].'" selected>'.$currency['currencyRate'].' - '.$currency['descriptionRate'].'</option>';
                            }
                        }
                    ?>
                </select>
            </div>
            <div class="rate-part-form-rate">
                <h2>Курс:</h2>
                <div class="input-rate">
                    <input type="text" disabled value="<?php echo $inputCurrency; ?>"> <!-- 1 валюта - столько-то рублей -->
                    <input type="text" disabled value="<?php echo $inputRub; ?>"> <!-- 1 рубль - столько-то валют -->
                </div>
            </div>
            <div class="button-rate">
                <button type="submit" name="selectCurrency">Выбрать</button>
            </div>
        </form>
    </div>
</body>
</html>