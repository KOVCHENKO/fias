<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <script src="https://unpkg.com/vue"></script>
    <script src="https://unpkg.com/axios/dist/axios.min.js"></script>
    <script src="/public/js/xml2json.js"></script>

    <title>Laravel</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

</head>
<body>

<div id="app">
    <p>Точка: @{{ point }}</p>
    <p>Нижний угол: @{{ lowerCorner }}</p>
    <p>Верхний угол: @{{ upperCorner }}</p>

    <button type="button" @click="setPoints">Проставить</button>
    <button type="button" @click="downloadIteration(0)">Проставить точки районов</button>
</div>

<script>
    setTimeout(function(){
        window.location.reload(1);
    }, 300000);
</script>
<script src="/public/js/main.js"></script>

</body>
</html>
