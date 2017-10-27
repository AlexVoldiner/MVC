<!--
1. delete - список идентификаторов, которые пришли в запросе и отсутствуют в БД
2. update - список значений и версий по идентификаторам, где версия в БД стала больше чем версия пришедшая в запросе
3. new - список значений и версий по идентификаторам, которые отсутствуют в пришедшем запросе, но есть в БД-->
<?php
try {
    $pdo = new PDO(
        'mysql:host=home;dbname=testDB',
        'root',
        '',
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]); //выбрасывать исключения
}
catch (PDOException $e) {
    echo "Невозможно установить соединение с базой данных";
}
$query = "SELECT * FROM data";
$con = $pdo->query($query);
/*Получаем данные из БД для дальнейшей работы с ними.*/
while( $res = $con->fetch()){
   $ident[] = $res['ident'];
   $version[] = [$res['ident'] => array('value' => $res['value'],'version' => $res['version'])];
   $ver[] = $res['version'];
}
if (!empty($_GET)) {
    $id_get = $_GET;
    $id_get1 = $_GET;
    $get = array_splice($id_get1, 2, 2);
    /*Начинаем искать и сравнивать элементы массивов, согласно пункта № 2*/
    foreach ($ver as $key => $val) {
        foreach ($get['version'] as $k => $v) {
            if (($ver[$key] > $get['version'][$k]) && ($key == $k)) {
                $update[] = $version[$key];
            }
            if ((($ver[$key] != $get['version'][$k]) && ($key == $k)) || ($key > (count($get['version']) - 1))) {
                if (@!in_array($version[$key], $update1)) {
                    $update1[] = $version[$key];
                }
            }
        }
    }
    /*Добавляем в массив ключ 'update'*/
    if (!empty($update)) {
        foreach ($update as $n => $item) {
            foreach ($item as $n1 => $item1) {
                $up['update'][$n1] = $update[$n][$n1];        //получаем результат в update
            }
        }
    } else {
        $up['update'] = $update;
    }
    /*Начинаем искать и сравнивать элементы массивов, согласно пункта № 3*/
    if (!empty($update1)) {
        foreach ($id_get['value'] as $k_n => $v_n) {
            foreach ($update1 as $k_n1 => $v_n1) {
                foreach ($v_n1 as $k_n2 => $v_n2) {
                    foreach ($v_n2 as $k_n3 => $v_n3) {
                        if (($id_get['value'][$k_n] == $update1[$k_n1][$k_n2]['value']) && ($k_n == ($k_n2 - 1))) {
                            unset($update1[$k_n1]);
                        }
                    }
                }
            }
        }
    }
    /*Добавляем в массив ключ 'new'*/
    if (!empty($update1)) {
        foreach ($update1 as $n2 => $item2) {
            foreach ($item2 as $n3 => $item3) {
                $new['new'][$n3] = $update1[$n2][$n3];        //получаем результат в new
            }
        }
    }else {
        $new['new'] = $update1;
    }
    /*Начинаем искать и сравнивать элементы массивов, согласно пункта № 1*/
    $i = 0;
    foreach ($ident as $key => $value) {
        if (in_array($id_get['ident'][$i], $ident)) {
            unset($id_get['ident'][$i]);
        }
        $i++;
    }

    array_splice($id_get, 1);
    $result['delete'] = $id_get['ident'];          //получаем результат в delete
    /*Совмещаем все данные в результирующий массив*/
    $end_res = $result + $up + $new;
    /*Проводим сериализацию массива*/
    $serialize_res = serialize($end_res);
    echo "<pre>";
    echo "Конечный массив: <br />";
    print_r($end_res);
    echo "</pre>";
    echo "Сериализованный массив: <br />";
    print_r($serialize_res);
} else {
    echo "Необходимо передать данные в GET!";
}