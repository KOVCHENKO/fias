new Vue({
    el: '#app',

    mounted() {
        let self = this;

        $('#input-district').on('keydown', function( event ){
            $('#ul-district').show();
            // event.stopPropagation();
        });

        $('#dropdown-toggle-address').on('click', function( event ){
            $('#ul-district').show();
            // event.stopPropagation();
        });

        $('#input-city').on('keydown', function( event ){
            $('#ul-city').show();
            // event.stopPropagation();
        });

        $('#dropdown-toggle-city').on('click', function( event ){
            $('#ul-city').show();
            // event.stopPropagation();
        });

        $('#input-street').on('keydown', function( event ){
            $('#ul-street').show();
            // event.stopPropagation();
        });

        $('#dropdown-toggle-street').on('click', function( event ){
            $('#ul-street').show();
            // event.stopPropagation();
        });

        $('#input-building').on('keydown', function( event ){
            $('#ul-building').show();
            // event.stopPropagation();
        });

        $('#dropdown-toggle-building').on('click', function( event ){
            $('#ul-building').show();
            // event.stopPropagation();
        });
    },

    created() {
        this.getDistricts();
    },

    data: {
            address: {
                region: 'Астраханская область',
                district: {
                    FORMALNAME: '',
                    AOGUID: ''
                },
                city: {
                    FORMALNAME: '',
                    AOGUID: '',
                },
                street: {
                    FORMALNAME: '',
                    AOGUID: '',
                    SHORTNAME: ''
                },
                building: {
                    HOUSENUM: '',
                    HOUSEID: '',
                    STRUCNUM: '',
                    BUILDNUM: '',
                    POSTALCODE: ''
                },
                string: ''
            },

            districts: [],
            cities: [],
            streets: [],
            buildings: [],

            visibility: {
                cityList: false,
                streetList: false,
                buildingList: false
            },

            disabled: {
                district: false,
                city: false,
                street: false
            },

            noHouse: false,

            application: {
                HOUSENUM: '',
                STREETNAME: '',
                noAddressMessage: '',
                applicationSendMessage: false,
                applicationForStreetVisibility: false
            }
    },

    methods: {
        /* Сохранить информацию про адрес */
        save() {

            /* Строка с адресом */
            this.address.string =
                this.address.street.zip + ', '
                + this.address.region + ', '
                + this.address.district.name + ', район; город/село/поселок '
                + this.address.city.name + ', улица '
                + this.address.street.name + ', '
                + this.address.building.name;

        },

        /** Получить все районы города Астрахани
         *  Примечание: Астрахань и Знаменск не входят ни в какие районы,
         *  Поэтому добавить в конце массива
         */
        getDistricts() {
            this.$http.get('http://fias.webart.im/choose_district').then(response => {
                this.districts = response.body;
                console.log(this.districts);
                this.districts.push({"AOGUID": "indiv", "FORMALNAME": "Города не входящие в район"});
            }, response => {});
        },

        /* Выбрать район */
        setDistrict(district) {
            this.address.district = district;
            this.getCities();

            $('#ul-district').hide();

            // this.disabled.district = true;
            this.address.city = {
                FORMALNAME: '',
                AOGUID: '',
            };

            this.address.street = {
                FORMALNAME: '',
                AOGUID: '',
            };

            this.address.building = {
                HOUSEID: '',
                HOUSENUM: '',
            };


            this.visibility.streetList = false;
            this.visibility.buildingList = false;
            this.visibility.cityList = true;
        },

        /* Получить все города района */
        getCities() {
            let self = this;

            if (this.address.district.AOGUID === 'indiv') {
                this.cities.push({"FORMALNAME": "Астрахань", "AOGUID" : "a101dd8b-3aee-4bda-9c61-9df106f145ff"}, {"FORMALNAME": "Знаменск", "AOGUID" : "54ecd5a8-83d9-4a85-ae2c-6fe6976ab716"});
            } else {
                this.$http.get('http://fias.webart.im/choose_city/' + this.address.district.AOGUID).then(response => {
                    this.cities = response.body;
                }, response => {});
            }
        },

        /* Выбрать город */
        setCity(city) {
            this.address.city = city;
            this.getStreets();
            $('#ul-city').hide();

            // this.disabled.city = true;

            this.address.street = {
                FORMALNAME: '',
                AOGUID: '',
            };

            this.address.building = {
                HOUSEID: '',
                HOUSENUM: '',
            };

            this.visibility.buildingList = false;
            this.visibility.streetList = true;
        },

        /* Получить все улицы */
        getStreets() {
            let self = this;

            this.$http.get('http://fias.webart.im/choose_street/' + this.address.city.AOGUID).then(response => {
                if (response.body.length === 0) {
                    self.streets.push({"FORMALNAME": "Улицы отсутствуют", "AOGUID": self.address.city.AOGUID});
                    self.application.applicationForStreetVisibility = true;
                    self.application.noAddressMessage = 'Моей улицы или дома нет в списке. Кликните, чтобы написать заявку';
                    self.noHouse = true;
                } else {
                    let arr = response.data;
                    arr.forEach(function(item, i, arr) {
                        if (item.SHORTNAME !== null) {
                            item.SHORTNAME = item.SHORTNAME + '. ';
                        }
                    });

                    self.streets = response.body;
                }

                // this.streets = response.body;
            }, response => {});
        },

        /* Выбрать улицу */
        setStreet(street) {
            this.address.street = street;
            this.getBuildings();

            $('#ul-street').hide();

            // this.disabled.street = true;
            this.address.building = {
                HOUSEID: '',
                HOUSENUM: '',
            };

            this.visibility.buildingList = true;
        },

        /* Получить все здания */
        getBuildings() {
            let self = this;
            this.$http.get('http://fias.webart.im/choose_building/' + this.address.street.AOGUID).then(response => {
                if (response.body.length === 0) {
                    self.buildings.push({"HOUSENUM": "Нет данных "});
                    self.application.noAddressMessage = 'Моего дома нет в списке. Кликните, чтобы написать заявку';
                    self.noHouse = true;
                } else {
                    let arr = response.data;
                    arr.forEach(function(item, i, arr) {
                        if (item.BUILDNUM !== null) {
                            item.HOUSENUM = item.HOUSENUM + ' корп ' + item.BUILDNUM;
                        }
                    });
                    self.buildings = response.body;
                }
            }, response => {});
        },

        /* Выбрать дом/здание */
        setBuilding(building) {
            $('#ul-building').hide();

            this.address.building = building;

            /* Сформировать строку с адресом */
            this.address.string = this.address.building.POSTALCODE
                + ';Астраханская область;'
                + this.address.district.FORMALNAME
                + ';'
                + this.address.city.FORMALNAME
                + ';'
                + this.address.building.HOUSENUM
        },

        /* Выбрать дом/здание */
        setAddressString() {
            /* Сформировать строку с адресом */
            this.address.string = this.address.building.POSTALCODE
                + ';Астраханская область;'
                + this.address.district.FORMALNAME
                + ';'
                + this.address.city.FORMALNAME
                + ';'
                + this.address.building.HOUSENUM
        },

        findBy(list, value, column) {
            return list.filter(function (item) {
                return (item[column].toLowerCase() || '').includes(value.toLowerCase())
            })
        },

        /*********************** Изменение данных ***********************/
        changeDistrict() {
            this.getDistricts();
            this.cities = [];

            this.visibility.streetList = false;
            this.visibility.buildingList = false;
            this.visibility.cityList = false;
        },

        changeCity() {
            this.cities = [];
            this.streets = [];
            this.getCities();
            this.visibility.streetList = false;
            this.visibility.buildingList = false;
        },

        changeStreet() {
            this.cities = [];
            this.streets = [];

            this.getStreets();
            this.visibility.buildingList = false;
        },

        /************* Отправка заявки на изменение адреса, если нет дома *************/
        sendApplication() {
            let self = this;
            this.$http.post('http://fiasadr/send_application/',
                {
                    fullAddress: 'Астраханская область, ' + this.address.district.FORMALNAME + 'район, ' + this.address.city.FORMALNAME + ', улица ' + this.address.street.FORMALNAME,
                    streetName: this.application.STREETNAME,
                    houseNumber: this.application.HOUSENUM
                }).then(response => {
                    $('#noAddressModal').modal('hide');
                    self.noHouse = false;
                    self.application.applicationSendMessage = true;
                }, response => {});

        }

    },

    computed: {
        filteredDistricts() {
            return this.findBy(this.districts, this.address.district.FORMALNAME, 'FORMALNAME');
        },

        filteredCities() {
            return this.findBy(this.cities, this.address.city.FORMALNAME, 'FORMALNAME');
        },

        filteredStreets() {
            return this.findBy(this.streets, this.address.street.FORMALNAME, 'FORMALNAME');
        },

        filteredBuildings() {
            return this.findBy(this.buildings, this.address.building.HOUSENUM, 'HOUSENUM');
        },
    },

});