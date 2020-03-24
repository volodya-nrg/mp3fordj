<template>
    <div class="page-admin-panel-mediafire">
        <div>
            <select name="genre" v-model="select_genre" @change="getFolders" :disabled="select_genre_disabled">
                <option v-for="genre in genres" :value="genre.value">{{ genre.name }}</option>
            </select>
        </div>
        <div>
            Папка:
            <select name="folder" v-model="select_folder" @change="getMp3s" :disabled="select_folder_disabled">
                <option value=""></option>
                <option v-for="folder in folders" :value="folder">{{ folder }}</option>
            </select>
        </div>
        <div>
            Mediafire folder key:
            <input type="text" name="key" v-model.trim="input_key" :disabled="input_key_disabled"/>
        </div>
        <div>
            <button class="btn btn_green" :disabled="btn_link_disabled" @click="link"><span>Связать</span></button>
        </div>
        <div>
            Обновлено: {{ total_files_in_folder }} из {{ total_modifed_items }}
        </div>
        <div>
            <button class="btn btn_green" :disabled="btn_write_disabled" @click="write"><span>Записать результаты</span></button>
        </div>
    </div>
</template>

<script>
    export default {
        name: "PanelMediafireComponent",
        inject: ['getItems', 'setItems', 'genres'],
        http: {
            root: '/admin',
            emulateJSON: true
        },
        data: function () {
            return {
                select_genre: 1,
                select_genre_disabled: false,
                select_folder: "",
                select_folder_disabled: true,
                folders: [],
                input_key: "",
                input_key_disabled: true,
                btn_link_disabled: true,
                btn_write_disabled: true,
                total_files_in_folder: 0,
                total_modifed_items: 0
            }
        },
        created: function () {
            this.getFolders();
        },
        computed: {},
        methods: {
            link: function (e) {
                var $btn = $(e.target.parentElement);

                if (!this.input_key) {
                    alert('Ошибка: укажите quickkey!');
                    return;
                }

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.post('ajax_get_mp3s_from_mediafire', {quickkey: this.input_key}).then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                    if (response.body.result) {
                        if (response.body.msg.length) {
                            console.log(response.body.msg);

                            this.getItems().forEach(function(item){
                                var tmpEl = response.body.msg.find(a => a.filename === item.name_title_case.toLowerCase() + '.mp3');

                                if (tmpEl && !item.quickkey) {
                                    item.quickkey = tmpEl.quickkey;
                                    this.total_modifed_items++;
                                }

                                this.btn_write_disabled = false;
                            }.bind(this));
                            
                        } else {
                            console.log('Список пуст');
                        }
                    }

                }, response => {});
            },
            write: function (e) {
                var $btn = $(e.target.parentElement);
                var items_src = this.getItems();
                var items = [];

                for (var key in items_src) {
                    var item = items_src[key];
                    items.push({
                        id: item.id,
                        quickkey: item.quickkey
                    });
                }

                console.log(items);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.post('ajax_set_mediafire_info', {mediafire_info: items}).then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                    if (response.body.result) {
                        console.log('finish');
                    }

                }, response => {});
            },
            getFolders: function () {
                this.select_genre_disabled = true;
                this.select_folder_disabled = true;
                this.input_key = "";
                this.btn_link_disabled = true;
                this.btn_write_disabled = true;
                this.total_files_in_folder = 0;
                this.total_modifed_items = 0;
                this.setItems([]);
                this.select_folder = "";

                this.$http.post('ajax_get_genres_for_mediafire', {cat_id: this.select_genre}).then(response => {
                    this.select_genre_disabled = false;
                    this.select_folder_disabled = false;

                    if (response.body.result) {
                        this.folders = response.body.msg;
                    }

                }, response => {});
            },
            getMp3s: function () {
                var request = {
                    cat_id: this.select_genre,
                    folder: this.select_folder
                };

                this.select_genre_disabled = true;
                this.select_folder_disabled = true;
                this.input_key = "";
                this.btn_link_disabled = true;
                this.btn_write_disabled = true;
                this.total_files_in_folder = 0;
                this.total_modifed_items = 0;

                this.$http.post('ajax_get_mp3s_for_mediafire', request).then(response => {
                    this.select_genre_disabled = false;
                    this.select_folder_disabled = false;

                    if (response.body.result) {
                        this.setItems(response.body.msg);

                        if (response.body.msg.length) {
                            this.input_key_disabled = false;
                            this.btn_link_disabled = false;
                            this.total_files_in_folder = response.body.msg.length;
                        }
                    }

                }, response => {});
            },
        }
    }
</script>