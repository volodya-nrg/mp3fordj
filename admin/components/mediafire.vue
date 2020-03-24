<template>
    <div class="page-admin-mediafire">
        <app-panel-mediafire></app-panel-mediafire>

        <table class="page-admin-tbl">
            <thead>
                <th>№</th>
                <th>Название</th>
                <th>Key</th>
            </thead>
            <tbody v-if="items.length">
                <tr is="app-mp3-mediafire" v-for="(item, key) in items" :key="item.id" :item="item" :pos="key + 1"></tr>
            </tbody>
            <tbody v-else>
                <tr is="app-tr-empty" :colspan-amount="3"></tr>
            </tbody>
        </table>
    </div>
</template>

<script>
    import AppPanelMediafire from './panel-mediafire.vue';
    import AppMp3Mediafire from './mp3-mediafire.vue';

    export default {
        name: "MediafireComponent",
        provide: function () {
            return {
                getItems: this.getItems,
                setItems: this.setItems
            }
        },
        data: function () {
            return {
                items: []
            }
        },
        components: {
            AppPanelMediafire,
            AppMp3Mediafire
        },
        computed: {},
        methods: {
            getItems: function () {
                return this.items;
            },
            setItems: function (data) {
                this.items = data;
            },
        }
    }
</script>