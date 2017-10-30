<!doctype html>
<html lang="{{ config('app.locale') }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Заявки ФИАС</title>

    <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <script src="https://unpkg.com/vue/dist/vue.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue-resource/1.3.4/vue-resource.min.js"></script>
    <script type="text/javascript" src="/public/js/application_vue.js" async></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/css/bootstrap.min.css" integrity="sha384-/Y6pD6FV/Vv2HJnA6t+vslU6fwYXjCFtcEpHbNJ0lyAFsXTsjBbfaDjzALeQsN6M" crossorigin="anonymous">
</head>
<body>

<div id="app">
    <form role="form" method="POST" action="#">

        <div class="row marinablerow">
            <div class="col-sm-2"><label class="input-title">Город/село/поселок:</label></div>
            <div class="col-sm-4 clearfix">
                <div class="input-group">
                    <input type="text" class="form-control" aria-label="..." placeholder="Город/село/поселок" @keydown="changeCity"
                           v-model="address.city.FORMALNAME" id="input-city" :disabled="disabled.city">
                    <div class="input-group-btn">
                        <button type="button" id="dropdown-toggle-city" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right scrollable-menu" id="ul-city">
                            <li v-for="city in filteredCities"><a @click="setCity(city)"> @{{ city.FORMALNAME }} </a></li>
                        </ul>
                    </div><!-- /btn-group -->
                </div><!-- /input-group -->
            </div>
        </div>
        <div class="row marinablerow">
            <div class="col-sm-2"><label class="input-title">Улица:</label></div>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control" aria-label="..." placeholder="Улица" @keydown="changeStreet"
                           v-model="address.street.FORMALNAME" id="input-street" :disabled="disabled.street">
                    <div class="input-group-btn">
                        <button type="button" id="dropdown-toggle-street" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right scrollable-menu" id="ul-street">
                            <li v-for="street in filteredStreets"><a @click="setStreet(street)">@{{ street.FORMALNAME }} </a></li>
                        </ul>
                    </div><!-- /btn-group -->
                </div><!-- /input-group -->
            </div>
        </div>
        <div class="row marinablerow">
            <div class="col-sm-2"><label class="input-title">Дом:</label></div>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control" aria-label="..." placeholder="Дом"
                           v-model="address.building.FORMALNAME" id="input-building" @keyup="setAddressString">
                    <div class="input-group-btn">
                        <button type="button" id="dropdown-toggle-building" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="caret"></span></button>
                        <ul class="dropdown-menu dropdown-menu-right scrollable-menu" id="ul-building">
                            <li v-for="building in filteredBuildings"><a @click="setBuilding(building)"> @{{ building.FORMALNAME }} </a></li>
                        </ul>
                    </div><!-- /btn-group -->
                </div><!-- /input-group -->
            </div>
        </div>
        <div class="row marinablerow">
            <div class="col-sm-2"><label class="input-title">Почтовый индекс:</label></div>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control" aria-label="..." placeholder="Почтовый индекс"
                           v-model="address.POSTALCODE" id="input-postalcode">
                </div><!-- /input-group -->
            </div>
        </div>
        <div v-if="application.applicationSendMessage">
            <p style="float: right">@{{ application.applicationMessage }}</p>
        </div>

        <input name="alias" type="hidden" data-alias="address" v-model="address.string">
    </form>

    <hr>

    <h3>Новый адрес</h3>
    <p>@{{ address.POSTALCODE }}, @{{ address.city.FORMALNAME }}, @{{ address.street.FORMALNAME }}, @{{ address.building.FORMALNAME }} </p>
    <button style="color: blue;" v-on:click="addToDataBase">Добавить в базу</button>

    <hr>

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
                <th>Дополнительный email</th>
                <th>Обратный email</th>
                <th>Удалить</th>
                <th>Уведомить</th>
            </tr>
        </thead>
        <tbody v-for="request in allRequests">
            <tr>
                <td>
                    <p> @{{ request.person_id }} </p>
                </td>
                <td>
                    @{{ request.new_region }}
                </td>
                <td>
                    <div> @{{ request.new_district }} </div>
                </td>
                <td>
                    <div>@{{ request.new_city }}</div>
                </td>
                <td> @{{ request.new_street }}</td>
                <td> @{{ request.new_building }}</td>
                <td> @{{ request.message }}</td>
                <td> @{{ request.return_email }}</td>
                <td> @{{ request.verified_email }}</td>
                <td><button v-on:click="deleteRequest(request.id)">Удалить</button></td>
                <td><button v-on:click="notifyUser(request.return_email, request.verified_email)">Уведомить</button></td>
            </tr>

        </tbody>
    </table>


</div>


</body>
</html>
