window.apiAddress = 'https://fias.webart.im/';
// window.apiAddress = 'http://fiasadr/';

new Vue({
    el: '#app',

    mounted() {
        let self = this;

        $('#input-city').on('keydown', function( event ){
            $('#ul-city').show();
            $('#ul-building').hide();
            // event.stopPropagation();
        });

        $('#dropdown-toggle-city').on('click', function( event ){
            $('#ul-city').show();
            // event.stopPropagation();
        });

        $('#input-street').on('keydown', function( event ){
            $('#ul-street').show();
            $('#ul-building').hide();
            $('#ul-city').hide();
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
        this.getAllRequests();
        this.getAllCities();
    },

    data: {
        /* Изменить данную переменную на true, чтобы пользователь смог сохранять адреса в БД */
        allRequests: [],

        saveOption: false,

        address: {
            region: 'Астраханская область',
            district: {
                FORMALNAME: '',
                AOGUID: ''
            },
            city: {
                FORMALNAME: '',
                AOGUID: '',
                PARENTGUID: ''
            },
            street: {
                FORMALNAME: '',
                AOGUID: '',
                SHORTNAME: ''
            },
            building: {
                FORMALNAME: '',
                HOUSEID: '',
                POSTALCODE: ''
            },
            POSTALCODE: '',
            string: ''
        },

        cities: [],
        streets: [],
        buildings: [],
        backupBuildings: [],

        visibility: {
            cityList: false,
            streetList: false,
            buildingList: false
        },

        disabled: {
            city: false,
            street: false
        },

        noHouse: false,

        application: {
            HOUSENUM: '',
            STREETNAME: '',
            noAddressMessage: '',
            applicationSendMessage: false,
            applicationForStreetVisibility: false,
            applicationMessage: ''
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

        /* Выбрать город */
        setCity(city) {
            this.address.city = city;
            this.getStreets();
            $('#ul-city').hide();

            this.address.street = {
                FORMALNAME: '',
                AOGUID: '',
            };

            this.address.building = {
                HOUSEID: '',
                FORMALNAME: '',
            };

            this.visibility.buildingList = false;
            this.visibility.streetList = true;

            $('#input-street').focus();
        },

        /* Получить все улицы */
        getStreets() {
            let self = this;

            this.$http.get('https://fias.webart.im/choose_street/' + this.address.city.AOGUID).then(response => {
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
            $('#input-building').focus();
            // this.visibility.buildingList = true;
        },

        /* Получить все здания */
        getBuildings() {
            let self = this;
            this.$http.get('https://fias.webart.im/choose_building/' + this.address.street.AOGUID).then(response => {
                if (response.body.length === 0) {
                    self.buildings.push({"FORMALNAME": "Нет данных "});
                    self.application.noAddressMessage = 'Домов нет на улице. Добавьте новый дом.';
                    self.noHouse = true;
                } else {
                    self.address.POSTALCODE = response.body[0].POSTALCODE;
                    self.buildings = response.body;
                    self.backupBuildings = response.body;
                }
            }, response => {});
        },

        /* Выбрать дом/здание */
        setBuilding(building) {
            $('#ul-building').hide();

            this.address.building = building;

            /* Сформировать строку с адресом. Опция, если переменная this.saveOption = false, то сохранить не получится */
            if (this.saveOption === true) {
                this.address.string = this.address.building.POSTALCODE
                    + ';Астраханская область;'
                    + this.address.district.FORMALNAME
                    + ';'
                    + this.address.city.FORMALNAME
                    + ';'
                    + this.address.building.FORMALNAME
            } else {
                this.address.string = '';
            }

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
                + this.address.building.FORMALNAME
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
            this.streets = [];
            this.visibility.streetList = false;
            this.visibility.buildingList = false;
        },

        changeStreet() {
            this.visibility.buildingList = false;
        },

        /************* Добавление нового адреса в БД *************/
        addToDataBase() {
            let self = this;
            this.$http.post(window.apiAddress + 'add_address_to_database/',
                {
                    AOGUID: this.address.street.AOGUID,
                    POSTALCODE: this.address.POSTALCODE,
                    HOUSENUM: this.address.building.FORMALNAME,
                    DISTRICTID: this.address.city.PARENTGUID,
                    CITYID: this.address.city.AOGUID
                }).then(response => {
                    self.getAllCities();
                    self.cleanAllFields();

                    self.application.applicationSendMessage = true;
                    setTimeout(() => { self.application.applicationSendMessage = false; }, 4000);

                    self.application.applicationMessage = 'Адрес добавлени в базу данных';
                    console.log(response.body);
            }, response => {});
        },

        getAllRequests() {
            let self = this;
            this.$http.get(window.apiAddress + 'get_all_applications_spa').then(response => {
                self.allRequests = response.body;
            });
        },

        getAllCities() {
            let self = this;
            this.$http.get(window.apiAddress + 'choose_cities').then(response => {
                self.cities = response.body;
            });
        },

        deleteRequest(requestId) {
            this.$http.get(window.apiAddress + 'application_delete/' + requestId).then(response => {
                let subIndex = this.allRequests.map(function(e) { /* Найти индекса элемента массива */
                    return e.id;
                }).indexOf(requestId);
                this.allRequests.splice(subIndex, 1); /* Убрать элемент из массива */

                this.cleanAllFields();
                this.getAllCities();
            });
        },

        notifyUser(email) {
            let self = this;
            this.$http.get(window.apiAddress + 'notify_user/' + email).then(response => {
                this.application.applicationSendMessage = true;
                setTimeout(() => { self.application.applicationSendMessage = false; }, 4000);
                this.application.applicationMessage = 'Пользователь проинформирован о внесении в базу данных';
            });
        },

        cleanAllFields() {
            $('#ul-cities').hide();
            $('#ul-streets').hide();
            $('#ul-building').hide();
            $('#input-city').focus();

            this.address.city.FORMALNAME = '';
            this.address.street.FORMALNAME = '';
            this.address.building.FORMALNAME = '';
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
            return this.findBy(this.buildings, this.address.building.FORMALNAME, 'FORMALNAME');
        },
    },

});