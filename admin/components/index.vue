<template>
    <div class="page-admin-index">
        <app-panel-add-tracks></app-panel-add-tracks>

        <table class="page-admin-tbl">
            <thead>
                <th>№</th>
                <th><input type="checkbox" v-model="mainCheckbox" @change="toggleAllCheckbox"/></th>
                <th></th>
                <th>Автор</th>
                <th>Название</th>
                <th>Альбом</th>
                <th>Доп. стиль</th>
                <th>Лейбл</th>
                <th>Время</th>
                <th>Wave-form</th>
                <th></th>
            </thead>
            <tbody v-if="items.length">
                <tr is="app-mp3-add" v-for="item in items" :key="item.uniq_id" :item="item"></tr>
            </tbody>
            <tbody v-else>
                <tr is="app-tr-empty" :colspan-amount="11"></tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import AppPanelAddTracks from './panel-add-tracks.vue';
    import AppMp3Add from './mp3-add.vue';

    export default {
        name: "IndexComponent",
        provide: function () {
            return {
                items: this.items,
                setMainCheckbox: this.setMainCheckbox,
                onChangeCheckbox: this.onChangeCheckbox
            }
        },
        data: function () {
            return {
                items: [],
                mainCheckbox: false
            }
        },
        components: {
            AppPanelAddTracks,
            AppMp3Add
        },
        created: function () {},
        computed: {},
        methods: {
            toggleAllCheckbox: function () {
                this.items.forEach(item => item.checked = this.mainCheckbox);
            },
            onChangeCheckbox: function () {
                var totalItems = this.items.length;
                var totalChecked = 0;

                this.items.forEach(function(item){
                    if (item.checked) {
                        totalChecked++;
                    }
                });

                this.setMainCheckbox(totalItems === totalChecked)
            },
            setMainCheckbox: function (val) {
                this.mainCheckbox = val;
            }
        }
    }
</script>