<template>
    <div class="page-admin-panel-add-tracks">
        <div>
            Файлов для загрузки: {{ total_files_on_local }}
        </div>
        <div>
            <button class="btn btn_green" :disabled="btn_load_disabled" @click="uploadFilesViaServer"><span>Загрузить</span></button>
        </div>
        <div>
            <button class="btn btn_green" :disabled="btn_fill_disabled" @click="getMetatagsFromUploads"><span>Заполнить</span></button>
        </div>
        <div>
            Жанр:
            <select name="genre" v-model="select_genre" :disabled="select_genre_disabled">
                <option v-for="genre in genres" :value="genre.value">{{ genre.name }}</option>
            </select>
        </div>
        <div>
            Папка: <input type="text" name="folder" v-model.trim="input_folder" :disabled="input_folder_disabled"/>
        </div>
        <div>
            <button class="btn btn_green" :disabled="btn_send_disabled" @click="sendItem"><span>Отправить</span></button>
        </div>
        <div>
            <button class="btn btn_green" :disabled="btn_clear_disabled" @click="clearFiles"><span>Очистить</span></button>
        </div>
        <div>
            Обработанно файлов: {{ total_uploaded_files }}
        </div>
    </div>
</template>

<script>
    export default {
        name: "PanelAddTracksComponent",
        inject: ['items', 'setMainCheckbox', 'genres'],
        timeLoop: 1000,
        http: {
            root: '/admin',
            emulateJSON: true
        },
        data: function () {
            return {
                total_files_on_local: 0,
                total_uploaded_files: 0,
                btn_fill_disabled: true,
                select_genre_disabled: true,
                input_folder_disabled: true,
                btn_send_disabled: true,
                btn_clear_disabled: true,
                select_genre: window.saved_category_id,
                input_folder: window.saved_folder
            }
        },
        created: function () {
            this.getTotalFilesFromUploads();
            this.existsFilesInUploads();
        },
        computed: {
            btn_load_disabled: function () {
                return !this.total_files_on_local;
            }
        },
        methods: {
            uploadFilesViaServer: function (e) {
                var $btn = $(e.target.parentElement);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.get('ajax_get_one_file_from_seorce_music').then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                    if (response.body.result) {
                        var item = response.body.msg;

                        item.pos = this.items.length + 1;
                        this.items.push(item);
                        this.total_files_on_local--;

                        if (this.total_files_on_local) {
                            setTimeout(function () {
                                this.uploadFilesViaServer(e);
                            }.bind(this), this.timeLoop);

                        } else {
                            this.btn_fill_disabled = false;
                            this.select_genre_disabled = false;
                            this.input_folder_disabled = false;
                            this.btn_send_disabled = false;
                        }

                    } else {
                        console.log(response.body.msg);
                    }

                }, response => {});
            },
            getMetatagsFromUploads: function (e) {
                var $btn = $(e.target.parentElement);
                var ids = this.items.map(a => a.uniq_id);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.post('ajax_get_metatags_mp3_from_uploads', {ids: ids}).then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                    if (response.body.result) {
                        this.updateItemAfterPutMetaTag(response.body.msg);
                    }

                }, response => {});
            },
            sendItem: function (e) {
                var $btn = $(e.target.parentElement);
                var checkedItemIndex = null;
                var checkedItem = this.items.find(function(el, i){
                    if (el.checked) {
                        checkedItemIndex = i;
                        return true;
                    }
                });

                if (checkedItemIndex !== null) {
                    checkedItem.category_id = this.select_genre;
                    checkedItem.folder = this.input_folder;

                    $btn.attr('disabled', true).addClass('btn_loading');
                    this.$http.post('/admin/ajax_set_mp3', checkedItem).then(response => {
                        $btn.attr('disabled', false).removeClass('btn_loading');

                        if (response.body.result) {
                            this.items.splice(checkedItemIndex, 1);
                            this.total_uploaded_files = response.body.total;

                        } else {
                            checkedItem.checked = false;
                            console.log(response.body.msg);
                        }

                        this.sendItem(e);

                    }, response => {});

                } else {
                    this.setMainCheckbox(false);
                }
            },
            clearFiles: function (e) {
                var $btn = $(e.target.parentElement);

                $btn.attr('disabled', true).addClass('btn_loading');
                this.$http.get('/admin/ajax_clear_dir_upload').then(response => {
                    $btn.attr('disabled', false).removeClass('btn_loading');

                    if (response.body.result) {
                        this.btn_clear_disabled = true;
                    }

                }, response => {});
            },
            existsFilesInUploads: function () {
                this.$http.get('/admin/ajax_exists_files_in_uploads').then(response => {
                    this.btn_clear_disabled = !response.body.msg;

                }, response => {});
            },
            getTotalFilesFromUploads: function () {
                this.$http.get('/admin/ajax_get_total_files_from_uploads').then(response => {
                    this.total_files_on_local = response.body.msg;

                }, response => {});
            },
            updateItemAfterPutMetaTag: function (data) {
                var dataItem = data.shift();
                var item = this.items.find(a => a.uniq_id === dataItem.uniq_id);
                
                if (item) {
                    var img = new Image();
                    var imgPath = '/upload/' + dataItem.wave;

                    if (dataItem.author) {
                        item.author = dataItem.author.trim();
                    }
                    if (dataItem.title) {
                        item.title = dataItem.title.trim();
                    }
                    if (dataItem.dop_style) {
                        item.dop_style = dataItem.dop_style.trim();
                    }
                    if (dataItem.album && item.label !== dataItem.album) {
                        item.album = dataItem.album.trim();
                    }

                    item.duration = dataItem.duration;

                    img.src = imgPath;
                    img.onload = function(){
                        item.wave = imgPath;

                        if (data.length) {
                            this.updateItemAfterPutMetaTag(data);
                        }

                    }.bind(this);
                }
            }
        }
    }
</script>