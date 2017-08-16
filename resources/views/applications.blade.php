<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Заявки ФИАС</title>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

</head>
<body>

<div>
    <h3>Заявки ФИАС</h3>

    <table class="table">
        <thead>
            <tr>
                <th>Person_ID</th>
                <th>Регион</th>
                <th>Район</th>
                <th>Город</th>
                <th>Улица</th>
                <th>Строение</th>
                <th>Комментарий</th>
                <th>Удалить</th>
            </tr>
        </thead>
        <tbody>
        @foreach($requests as $request)
            <tr>
                <td>{{ $request->person_id }}</td>
                <td>{{ $request->new_region }}</td>
                <td>{{ $request->new_district }}</td>
                <td>{{ $request->new_city }}</td>
                <td>{{ $request->new_street }}</td>
                <td>{{ $request->new_building }}</td>
                <td>{{ $request->message }}</td>
                <td><a href="/application_delete/{{ $request->id }}">Удалить</a></td>
            </tr>
        @endforeach
        </tbody>
    </table>


</div>


</body>
</html>
