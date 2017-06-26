new Vue({
    el: '#app',
    data: {
        message: 'Hello Vue!',
        jsonObject: '',

        point: '',
        lowerCorner: '',
        upperCorner: ''
    },

    created() {
        this.downloadIteration(0);
        // this.setPoints();
    },

    methods: {
        downloadIteration(i) {
            axios.get('/district_all/46c0e38f-d339-4149-acfa-0d6ae968d2b6/Черноярский/' + i).then(response => {
                console.log(response.data);
                if (i < 105) {
                    let j = i + 1;
                    this.downloadIteration(j);
                }
            });
        },
// Ахтубинский //
        // 201c5a5d-5af2-47bb-b538-c42ffd5927dc // Камызякский // 53 +
       // 6d400dc9-cf9a-4dfd-920d-b83261345fd2 / Икрянинский +
        // 04b18b87-2fb9-49eb-bee4-c660f18f7ea4 // Лиманский +
            // 2b714aee-b462-4243-9b3d-6581b44202da // Наримановский +
            // 67788aa7-bf40-4dba-9574-9b271ccb845c // Енотаевский +
        // 3543d36d-4bf6-4caa-a267-b2bad69ccd6c // Володарский'). // 77 - Еще раз пройтись +
        // a0b67c5c-0250-47cf-94a5-4c2ca29fe183 // Приволжский // 43 +
            // dbb24e53-47a5-4a43-aa2e-bdfee433ab00 // Красноярский +

            // 46c0e38f-d339-4149-acfa-0d6ae968d2b6 // Черноярский ++

            // bc36238f-e341-41b3-81aa-700c30845de8 // Харабалинский +


        // },

        setPoint() {
            let i;
            for(i = 122000; i < 125000; i = i + 1000) {
                let end = i + 1000;
                axios.get('/houses/' + i + '/' + end)
                    .then(response => {
                        console.log(response.data);
                    });
            }
        },

        /* Проставить адреса (точки). Начинать отс */
        setPoints() {
                axios.get('/astra/0/200').
                        then(response => {
                        console.log(response.data);
                            axios.get('/astra/200/400').
                                then(response => {
                                console.log(response.data);
                                    axios.get('/astra/400/600').
                                        then(response => {
                                        console.log(response.data);
                                            axios.get('/astra/600/800').
                                            then(response => {
                                                console.log(response.data);
                                                axios.get('/astra/800/1000').
                                                then(response => {
                                                    console.log(response.data);
                                                    axios.get('/astra/1000/1200').
                                                    then(response => {
                                                        console.log(response.data);
                                                        axios.get('/astra/1200/1400').
                                                        then(response => {
                                                            console.log(response.data);
                                                            axios.get('/astra/1400/1600').
                                                            then(response => {
                                                                console.log(response.data);
                                                                axios.get('/astra/1600/1800').
                                                                then(response => {
                                                                    console.log(response.data);
                                                                    axios.get('/astra/1800/2000').
                                                                    then(response => {
                                                                        console.log(response.data);
                                                                        axios.get('/astra/2000/2200').
                                                                        then(response => {
                                                                            console.log(response.data);
                                                                            axios.get('/astra/2200/2400').
                                                                            then(response => {
                                                                                console.log(response.data);
                                                                                axios.get('/astra/2400/2600').
                                                                                then(response => {
                                                                                    console.log(response.data);
                                                                                    axios.get('/astra/2600/2800').
                                                                                    then(response => {
                                                                                        console.log(response.data);
                                                                                        axios.get('/astra/2800/3000').
                                                                                        then(response => {
                                                                                            console.log(response.data);
                                                                                            axios.get('/astra/3000/3200').
                                                                                            then(response => {
                                                                                                console.log(response.data);
                                                                                                axios.get('/astra/3200/3400').
                                                                                                then(response => {
                                                                                                    console.log(response.data);
                                                                                                    axios.get('/astra/3400/3600').
                                                                                                    then(response => {
                                                                                                        console.log(response.data);
                                                                                                        axios.get('/astra/3600/3800').
                                                                                                        then(response => {
                                                                                                            console.log(response.data);
                                                                                                            axios.get('/astra/3800/4000').
                                                                                                            then(response => {
                                                                                                                console.log(response.data);
                                                                                                                axios.get('/astra/4000/4200').
                                                                                                                then(response => {
                                                                                                                    console.log(response.data);
                                                                                                                    axios.get('/astra/4200/4400').
                                                                                                                    then(response => {
                                                                                                                        console.log(response.data);
                                                                                                                        axios.get('/astra/4400/4600').
                                                                                                                        then(response => {
                                                                                                                            console.log(response.data);
                                                                                                                            axios.get('/astra/4600/4800').
                                                                                                                            then(response => {
                                                                                                                                console.log(response.data);
                                                                                                                                axios.get('/astra/4800/5000').
                                                                                                                                then(response => {
                                                                                                                                    console.log(response.data);
                                                                                                                                    axios.get('/astra/5000/5600').
                                                                                                                                    then(response => {
                                                                                                                                        console.log(response.data);
                                                                                                                                        axios.get('/astra/5600/5800').
                                                                                                                                        then(response => {
                                                                                                                                            console.log(response.data);
                                                                                                                                            axios.get('/astra/5800/6000').
                                                                                                                                            then(response => {
                                                                                                                                                console.log(response.data);
                                                                                                                                                axios.get('/astra/6000/6200').
                                                                                                                                                then(response => {
                                                                                                                                                    console.log(response.data);
                                                                                                                                                    axios.get('/astra/6200/6400').
                                                                                                                                                    then(response => {
                                                                                                                                                        console.log(response.data);
                                                                                                                                                        axios.get('/astra/6400/6600').
                                                                                                                                                        then(response => {
                                                                                                                                                            console.log(response.data);
                                                                                                                                                            axios.get('/astra/6600/6800').
                                                                                                                                                            then(response => {
                                                                                                                                                                console.log(response.data);
                                                                                                                                                                axios.get('/astra/6800/7000').
                                                                                                                                                                then(response => {
                                                                                                                                                                    console.log(response.data);
                                                                                                                                                                    axios.get('/astra/7000/7200').
                                                                                                                                                                    then(response => {
                                                                                                                                                                        console.log(response.data);
                                                                                                                                                                        axios.get('/astra/7200/7400').
                                                                                                                                                                        then(response => {
                                                                                                                                                                            console.log(response.data);
                                                                                                                                                                            axios.get('/astra/7400/18000').
                                                                                                                                                                            then(response => {
                                                                                                                                                                                console.log(response.data);
                                                                                                                                                                                axios.get('/astra/18000/18500').
                                                                                                                                                                                then(response => {
                                                                                                                                                                                    console.log(response.data);
                                                                                                                                                                                    axios.get('/astra/18500/19000').
                                                                                                                                                                                    then(response => {
                                                                                                                                                                                        console.log(response.data);
                                                                                                                                                                                        axios.get('/astra/19000/20000').
                                                                                                                                                                                        then(response => {
                                                                                                                                                                                            console.log(response.data);
                                                                                                                                                                                            axios.get('/astra/20000/21000').
                                                                                                                                                                                            then(response => {
                                                                                                                                                                                                console.log(response.data);
                                                                                                                                                                                                axios.get('/astra/21000/22000').
                                                                                                                                                                                                then(response => {
                                                                                                                                                                                                    console.log(response.data);
                                                                                                                                                                                                    axios.get('/astra/22000/23000').
                                                                                                                                                                                                    then(response => {
                                                                                                                                                                                                        console.log(response.data);
                                                                                                                                                                                                        axios.get('/astra/23000/24000').
                                                                                                                                                                                                        then(response => {
                                                                                                                                                                                                            console.log(response.data);
                                                                                                                                                                                                            axios.get('/astra/24000/25000').
                                                                                                                                                                                                            then(response => {
                                                                                                                                                                                                                console.log(response.data);
                                                                                                                                                                                                                axios.get('/astra/25000/26000').
                                                                                                                                                                                                                then(response => {
                                                                                                                                                                                                                    console.log(response.data);
                                                                                                                                                                                                                    axios.get('/astra/26000/27000').
                                                                                                                                                                                                                    then(response => {
                                                                                                                                                                                                                        console.log(response.data);
                                                                                                                                                                                                                        axios.get('/astra/27000/28000').
                                                                                                                                                                                                                        then(response => {
                                                                                                                                                                                                                            console.log(response.data);
                                                                                                                                                                                                                        });
                                                                                                                                                                                                                    });
                                                                                                                                                                                                                });
                                                                                                                                                                                                            });
                                                                                                                                                                                                        });
                                                                                                                                                                                                    });
                                                                                                                                                                                                });
                                                                                                                                                                                            });
                                                                                                                                                                                        });
                                                                                                                                                                                    });
                                                                                                                                                                                });
                                                                                                                                                                            });
                                                                                                                                                                        });
                                                                                                                                                                    });
                                                                                                                                                                });
                                                                                                                                                            });
                                                                                                                                                        });
                                                                                                                                                    });
                                                                                                                                                });
                                                                                                                                            });
                                                                                                                                        });
                                                                                                                                    });
                                                                                                                                });
                                                                                                                            });
                                                                                                                        });
                                                                                                                    });
                                                                                                                });
                                                                                                            });
                                                                                                        });
                                                                                                    });
                                                                                                });
                                                                                            });
                                                                                        });
                                                                                    });
                                                                                });
                                                                            });
                                                                        });
                                                                    });
                                                                });
                                                            });

                                        });
                                    });
                                });
                            });
                        });
                    });
                });
        },

        /* Получить точку */
        getPoint() {
            axios.get('https://geocode-maps.yandex.ru/1.x/?geocode=%D0%90%D1%81%D1%82%D1%80%D0%B0%D1%85%D0%B0%D0%BD%D1%8C+%D0%9C%D0%B5%D0%B9%D0%B5%D1%80%D0%B0+7')
                .then(response => {
                    let x2js = new X2JS();
                    this.jsonObject = x2js.xml_str2json(response.data);

                    // console.log(response.data);
                    this.point = this.jsonObject.ymaps.GeoObjectCollection.featureMember.GeoObject.Point.pos;
                    this.lowerCorner = this.jsonObject.ymaps.GeoObjectCollection.featureMember.GeoObject.boundedBy.Envelope.lowerCorner;
                    this.upperCorner = this.jsonObject.ymaps.GeoObjectCollection.featureMember.GeoObject.boundedBy.Envelope.upperCorner;

                    console.log(point);
                    console.log(lowerCorner);
                    console.log(upperCorner);
                })
                .catch(function (error) {
                    console.log(error);
                });
        }
    }


});